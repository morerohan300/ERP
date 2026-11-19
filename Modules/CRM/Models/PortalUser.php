<?php

declare(strict_types=1);

namespace Modules\CRM\Models;

use App\Support\Concerns\HasUuid;
use App\Support\Tenancy\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * A customer's login to the support portal (the separate `portal` guard).
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property ?int $contact_id
 * @property bool $is_active
 */
#[Fillable(['name', 'email', 'password', 'contact_id', 'customer_id', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class PortalUser extends Authenticatable
{
    use BelongsToCompany, HasUuid;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
