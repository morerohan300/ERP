<?php

declare(strict_types=1);

namespace App\Support\Numbering;

use Illuminate\Database\Eloquent\Model;

/**
 * Assigns `number` on create using the model's document type.
 * Models declare: protected static string $documentType = 'invoice';
 * and optionally a `date` attribute used for yearly/monthly resets.
 *
 * @mixin Model
 */
trait HasDocumentNumber
{
    public static function bootHasDocumentNumber(): void
    {
        static::creating(function ($model): void {
            if (empty($model->number)) {
                $date = $model->date ?? null;
                $model->number = app(DocumentNumberGenerator::class)->next(static::documentType(), $date);
            }
        });
    }

    public static function documentType(): string
    {
        return property_exists(static::class, 'documentType')
            ? static::$documentType
            : str(class_basename(static::class))->snake()->toString();
    }
}
