<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Core\Services\CustomFieldService;
use Modules\CRM\Events\DealLost;
use Modules\CRM\Events\DealWon;
use Modules\CRM\Models\Deal;
use Modules\CRM\Models\DealProduct;
use Modules\CRM\Models\Pipeline;
use Modules\CRM\Models\PipelineStage;

/**
 * Deal lifecycle: creation, Kanban moves, won / lost / reopen, and line items.
 */
class DealService
{
    private const SCALE = 4;

    public function __construct(private readonly CustomFieldService $customFields) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $products
     * @param  array<string, mixed>  $customFieldValues
     */
    public function create(array $data, array $products = [], array $customFieldValues = []): Deal
    {
        return DB::transaction(function () use ($data, $products, $customFieldValues): Deal {
            $pipeline = isset($data['pipeline_id'])
                ? Pipeline::query()->findOrFail($data['pipeline_id'])
                : Pipeline::default() ?? throw ValidationException::withMessages(['pipeline_id' => __('Create a pipeline first.')]);

            $stage = isset($data['pipeline_stage_id'])
                ? PipelineStage::query()->where('pipeline_id', $pipeline->getKey())->findOrFail($data['pipeline_stage_id'])
                : $pipeline->stages()->open()->first() ?? throw ValidationException::withMessages(['pipeline_stage_id' => __('The pipeline has no open stage.')]);

            $data['pipeline_id'] = $pipeline->getKey();
            $data['pipeline_stage_id'] = $stage->getKey();
            $data['probability'] ??= $stage->probability;

            $deal = new Deal($data);
            $deal->kanban_order = $this->nextOrder($stage);
            $deal->save();

            if ($products !== []) {
                $this->syncProducts($deal, $products);
            }

            $this->customFields->save($deal, $customFieldValues);

            return $deal->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>|null  $products  null leaves the lines untouched
     * @param  array<string, mixed>  $customFieldValues
     */
    public function update(Deal $deal, array $data, ?array $products = null, array $customFieldValues = []): Deal
    {
        return DB::transaction(function () use ($deal, $data, $products, $customFieldValues): Deal {
            $stageId = $data['pipeline_stage_id'] ?? null;
            unset($data['pipeline_stage_id'], $data['pipeline_id']);

            $deal->fill($data)->save();

            if ($stageId !== null && (int) $stageId !== $deal->pipeline_stage_id) {
                $this->moveToStage($deal, PipelineStage::query()->findOrFail($stageId));
            }

            if ($products !== null) {
                $this->syncProducts($deal, $products);
            }

            $this->customFields->save($deal, $customFieldValues);

            return $deal->refresh();
        });
    }

    /**
     * Move an open deal to another open stage of its pipeline, at $position (0-based) in that column.
     *
     * @throws ValidationException
     */
    public function moveToStage(Deal $deal, PipelineStage $stage, ?int $position = null): Deal
    {
        if (! $deal->isOpen()) {
            throw ValidationException::withMessages(['deal' => __('A closed deal cannot be moved. Reopen it first.')]);
        }

        if ($stage->pipeline_id !== $deal->pipeline_id) {
            throw ValidationException::withMessages(['pipeline_stage_id' => __('That stage belongs to a different pipeline.')]);
        }

        if ($stage->isClosed()) {
            throw ValidationException::withMessages(['pipeline_stage_id' => __('Use the Won or Lost action to close a deal.')]);
        }

        return DB::transaction(function () use ($deal, $stage, $position): Deal {
            $stageChanged = $deal->pipeline_stage_id !== $stage->getKey();

            $column = Deal::query()->open()
                ->where('pipeline_stage_id', $stage->getKey())
                ->whereKeyNot($deal->getKey())
                ->orderBy('kanban_order')->orderBy('id')
                ->pluck('id')->all();

            $position = $position === null ? count($column) : max(0, min($position, count($column)));
            array_splice($column, $position, 0, [$deal->getKey()]);

            foreach ($column as $index => $id) {
                if ($id !== $deal->getKey()) {
                    Deal::query()->whereKey($id)->update(['kanban_order' => $index + 1]);
                }
            }

            // Saved through the model so a stage change reaches the audit log.
            $deal->forceFill(['kanban_order' => array_search($deal->getKey(), $column, true) + 1]);
            if ($stageChanged) {
                $deal->forceFill(['pipeline_stage_id' => $stage->getKey(), 'probability' => $stage->probability]);
            }
            $deal->save();

            return $deal->refresh();
        });
    }

    public function win(Deal $deal): Deal
    {
        if (! $deal->isOpen()) {
            throw ValidationException::withMessages(['deal' => __('This deal is already closed.')]);
        }

        return DB::transaction(function () use ($deal): Deal {
            $stage = PipelineStage::query()->where('pipeline_id', $deal->pipeline_id)->where('is_won', true)->orderBy('sort_order')->first();

            $deal->forceFill([
                'pipeline_stage_id' => $stage?->getKey() ?? $deal->pipeline_stage_id,
                'probability' => 100,
                'won_at' => now(),
                'lost_at' => null,
                'lost_reason' => null,
                'kanban_order' => 0,
            ])->save();

            DealWon::dispatch($deal);

            return $deal;
        });
    }

    public function lose(Deal $deal, string $reason): Deal
    {
        if (! $deal->isOpen()) {
            throw ValidationException::withMessages(['deal' => __('This deal is already closed.')]);
        }

        return DB::transaction(function () use ($deal, $reason): Deal {
            $stage = PipelineStage::query()->where('pipeline_id', $deal->pipeline_id)->where('is_lost', true)->orderBy('sort_order')->first();

            $deal->forceFill([
                'pipeline_stage_id' => $stage?->getKey() ?? $deal->pipeline_stage_id,
                'probability' => 0,
                'lost_at' => now(),
                'won_at' => null,
                'lost_reason' => $reason,
                'kanban_order' => 0,
            ])->save();

            DealLost::dispatch($deal);

            return $deal;
        });
    }

    /** Put a closed deal back into the first open stage of its pipeline. */
    public function reopen(Deal $deal): Deal
    {
        if ($deal->isOpen()) {
            return $deal;
        }

        return DB::transaction(function () use ($deal): Deal {
            $stage = PipelineStage::query()->where('pipeline_id', $deal->pipeline_id)->open()->orderBy('sort_order')->firstOrFail();

            $deal->forceFill([
                'pipeline_stage_id' => $stage->getKey(),
                'probability' => $stage->probability,
                'won_at' => null,
                'lost_at' => null,
                'lost_reason' => null,
                'kanban_order' => $this->nextOrder($stage),
            ])->save();

            return $deal;
        });
    }

    /**
     * Replace the deal's line items and set the deal value to their total.
     *
     * @param  array<int, array<string, mixed>>  $lines  description, quantity, unit_price, discount_percent
     */
    public function syncProducts(Deal $deal, array $lines): Deal
    {
        $deal->products()->delete();

        $total = '0';
        foreach (array_values($lines) as $index => $line) {
            if (blank($line['description'] ?? null)) {
                continue;
            }

            $quantity = (string) ($line['quantity'] ?? '1');
            $unitPrice = (string) ($line['unit_price'] ?? '0');
            $discount = (string) ($line['discount_percent'] ?? '0');

            $gross = bcmul($quantity, $unitPrice, self::SCALE + 4);
            $net = bcmul($gross, bcsub('1', bcdiv($discount, '100', self::SCALE + 4), self::SCALE + 4), self::SCALE);

            DealProduct::query()->create([
                'deal_id' => $deal->getKey(),
                'product_id' => $line['product_id'] ?? null,
                'description' => $line['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_percent' => $discount,
                'tax_id' => $line['tax_id'] ?? null,
                'tax_amount' => '0',
                'line_total' => $net,
                'sort_order' => $index,
            ]);

            $total = bcadd($total, $net, self::SCALE);
        }

        if ($lines !== [] && bccomp($total, '0', self::SCALE) > 0) {
            $deal->forceFill(['value' => $total])->save();
        }

        return $deal->unsetRelation('products');
    }

    /** Weighted pipeline value: sum of value × probability for the given open deals' query. */
    public function weightedValue(?int $pipelineId = null): string
    {
        $query = Deal::query()->open();
        if ($pipelineId !== null) {
            $query->where('pipeline_id', $pipelineId);
        }

        return (string) ($query->selectRaw('COALESCE(SUM(value * probability / 100), 0) AS weighted')->value('weighted') ?? '0');
    }

    private function nextOrder(PipelineStage $stage): int
    {
        return (int) Deal::query()->where('pipeline_stage_id', $stage->getKey())->max('kanban_order') + 1;
    }
}
