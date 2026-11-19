<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Core\Models\Company;

/**
 * Manages users within the current company: invite/create, roles, branch
 * restrictions, activation and removal.
 */
class UserService
{
    public function __construct(private readonly CurrentCompany $currentCompany) {}

    /**
     * Create a user (or attach an existing account by e-mail) to the current
     * company. New accounts receive a "set your password" e-mail when no
     * password is given.
     *
     * @param  array{name: string, email: string, password?: string|null, phone?: string|null, roles?: array<int, string>, branch_ids?: array<int, int>|null, department_id?: int|null, job_title?: string|null, is_active?: bool, locale?: string|null, timezone?: string|null}  $data
     */
    public function create(array $data, ?UploadedFile $avatar = null): User
    {
        $company = $this->currentCompany->getOrFail();

        return DB::transaction(function () use ($data, $avatar, $company): User {
            $user = User::withTrashed()->where('email', mb_strtolower($data['email']))->first();
            $isNew = $user === null;

            if ($user !== null && $user->belongsToCompany($company)) {
                throw ValidationException::withMessages(['email' => __('This user is already a member of the company.')]);
            }

            if ($user?->trashed()) {
                $user->restore();
            }

            if ($isNew) {
                $user = User::query()->create([
                    'name' => $data['name'],
                    'email' => mb_strtolower($data['email']),
                    'password' => $data['password'] ?? Str::password(32),
                    'phone' => $data['phone'] ?? null,
                    'locale' => $data['locale'] ?? null,
                    'timezone' => $data['timezone'] ?? $company->timezone,
                    'is_active' => $data['is_active'] ?? true,
                    'current_company_id' => $company->getKey(),
                ]);
            }

            if ($avatar !== null) {
                $user->forceFill(['avatar_path' => $avatar->store("avatars/{$user->uuid}", 'public')])->save();
            }

            $company->users()->attach($user->getKey(), $this->pivotData($data) + ['is_owner' => false, 'joined_at' => now()]);

            $this->syncRoles($user, $data['roles'] ?? []);

            if ($isNew) {
                event(new Registered($user));
                if (empty($data['password'])) {
                    Password::broker()->sendResetLink(['email' => $user->email]);
                }
            }

            return $user;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        $company = $this->currentCompany->getOrFail();

        return DB::transaction(function () use ($user, $data, $avatar, $company): User {
            $user->fill(array_filter([
                'name' => $data['name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'locale' => $data['locale'] ?? null,
                'timezone' => $data['timezone'] ?? null,
            ], fn ($v) => $v !== null));

            if (array_key_exists('is_active', $data)) {
                $this->guardOwner($user, $company, fn () => ! $data['is_active'], __('The company owner cannot be deactivated.'));
                $user->is_active = (bool) $data['is_active'];
            }

            if (! empty($data['password'])) {
                $user->password = $data['password'];
            }

            if ($avatar !== null) {
                if ($user->avatar_path) {
                    Storage::disk('public')->delete($user->avatar_path);
                }
                $user->avatar_path = $avatar->store("avatars/{$user->uuid}", 'public');
            }

            $user->save();

            $company->users()->updateExistingPivot($user->getKey(), $this->pivotData($data));

            if (array_key_exists('roles', $data)) {
                $this->syncRoles($user, $data['roles'] ?? []);
            }

            return $user->refresh();
        });
    }

    /** Remove a user from the current company (the account itself remains). */
    public function removeFromCompany(User $user): void
    {
        $company = $this->currentCompany->getOrFail();

        $this->guardOwner($user, $company, fn () => true, __('The company owner cannot be removed.'));

        DB::transaction(function () use ($user, $company): void {
            $user->syncRoles([]);
            $company->users()->detach($user->getKey());

            if ($user->current_company_id === $company->getKey()) {
                $user->forceFill(['current_company_id' => $user->companies()->value('companies.id')])->save();
            }

            $user->tokens()->delete();
        });
    }

    /** @param array<int, string> $roles */
    public function syncRoles(User $user, array $roles): void
    {
        setPermissionsTeamId($this->currentCompany->id());
        $user->unsetRelation('roles');
        $user->syncRoles($roles);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function pivotData(array $data): array
    {
        $pivot = [];
        if (array_key_exists('branch_ids', $data)) {
            $ids = array_values(array_filter(array_map('intval', (array) ($data['branch_ids'] ?? []))));
            $pivot['branch_ids'] = $ids === [] ? null : json_encode($ids);
        }
        foreach (['department_id', 'job_title'] as $key) {
            if (array_key_exists($key, $data)) {
                $pivot[$key] = $data[$key] ?: null;
            }
        }

        return $pivot;
    }

    private function guardOwner(User $user, Company $company, callable $condition, string $message): void
    {
        $isOwner = $company->users()->whereKey($user->getKey())->wherePivot('is_owner', true)->exists();

        if ($isOwner && $condition()) {
            throw ValidationException::withMessages(['user' => $message]);
        }
    }
}
