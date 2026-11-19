<?php

declare(strict_types=1);

namespace Modules\CRM\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\CRM\Models\Pipeline;
use Modules\CRM\Models\PipelineStage;

/**
 * Saves a pipeline together with its ordered stages.
 */
class PipelineService
{
    /**
     * Create or update a pipeline. Stages are matched by id; new ones are created, and stages
     * missing from the list are removed unless deals still sit in them.
     *
     * @param  array<string, mixed>  $data  name, is_default
     * @param  array<int, array<string, mixed>>  $stages  in display order
     *
     * @throws ValidationException when a removed stage still holds deals
     */
    public function save(?Pipeline $pipeline, array $data, array $stages): Pipeline
    {
        return DB::transaction(function () use ($pipeline, $data, $stages): Pipeline {
            $pipeline = $pipeline ?? new Pipeline;
            $pipeline->fill(['name' => $data['name'], 'is_default' => (bool) ($data['is_default'] ?? false)])->save();

            if ($pipeline->is_default) {
                Pipeline::query()->whereKeyNot($pipeline->getKey())->where('is_default', true)->update(['is_default' => false]);
            } elseif (! Pipeline::query()->whereKeyNot($pipeline->getKey())->where('is_default', true)->exists()) {
                // A company always has one default pipeline.
                $pipeline->forceFill(['is_default' => true])->save();
            }

            $keep = [];
            foreach (array_values($stages) as $index => $stage) {
                $attributes = [
                    'name' => $stage['name'],
                    'probability' => (int) $stage['probability'],
                    'color' => $stage['color'] ?? 'slate',
                    'is_won' => (bool) ($stage['is_won'] ?? false),
                    'is_lost' => (bool) ($stage['is_lost'] ?? false),
                    'sort_order' => $index + 1,
                ];

                $existing = ! empty($stage['id']) ? $pipeline->stages()->find($stage['id']) : null;

                if ($existing !== null) {
                    $existing->fill($attributes)->save();
                    $keep[] = $existing->getKey();
                } else {
                    $keep[] = PipelineStage::query()->create($attributes + ['pipeline_id' => $pipeline->getKey()])->getKey();
                }
            }

            $removed = $pipeline->stages()->whereNotIn('id', $keep)->withCount('deals')->get();

            foreach ($removed as $stage) {
                if ($stage->deals_count > 0) {
                    throw ValidationException::withMessages(['stages' => __('The stage ":name" still has deals. Move them before removing it.', ['name' => $stage->name])]);
                }

                $stage->delete();
            }

            return $pipeline->refresh();
        });
    }
}
