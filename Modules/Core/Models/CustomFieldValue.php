<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Stored value of a custom field for one record. Multi-value fields are
 * stored as JSON; scalars as plain strings.
 */
#[Fillable(['custom_field_id', 'model_type', 'model_id', 'value'])]
class CustomFieldValue extends Model
{
    public function field(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id');
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
