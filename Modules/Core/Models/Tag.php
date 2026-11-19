<?php

declare(strict_types=1);

namespace Modules\Core\Models;

use App\Support\Concerns\HasAuditColumns;
use App\Support\Concerns\HasUuid;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'color'])]
class Tag extends Model
{
    use BelongsToCompany, HasAuditColumns, HasUuid;
}
