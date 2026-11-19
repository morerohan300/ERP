<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A line item on a deal. `product_id` and `tax_id` become real references
 * once Inventory and the tax engine are used by Sales.
 *
 * @property int $deal_id
 * @property string $description
 * @property string $quantity
 * @property string $unit_price
 * @property string $discount_percent
 * @property string $line_total
 */
#[Fillable(['deal_id', 'product_id', 'description', 'quantity', 'unit_price', 'discount_percent', 'tax_id', 'tax_amount', 'line_total', 'sort_order'])]
class DealProduct extends Model
{
    use BelongsToCompany;

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'discount_percent' => 'decimal:2',
            'tax_amount' => 'decimal:4',
            'line_total' => 'decimal:4',
            'sort_order' => 'integer',
        ];
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }
}
