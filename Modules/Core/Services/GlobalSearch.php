<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use App\Models\User;
use App\Support\Modules\ModuleManager;
use Illuminate\Support\Collection;

/**
 * Header search across modules. Each module registers providers:
 *
 *   GlobalSearch::register('crm', 'Contacts', 'crm.contact.view',
 *       fn (string $term, int $limit) => Contact::search($term)->limit($limit)->get()
 *           ->map(fn ($c) => ['title' => $c->name, 'subtitle' => $c->email, 'url' => route(...)]));
 */
class GlobalSearch
{
    /** @var array<int, array{module: string, group: string, permission: string|null, resolver: callable}> */
    private static array $providers = [];

    public static function register(string $module, string $group, ?string $permission, callable $resolver): void
    {
        self::$providers[] = compact('module', 'group', 'permission', 'resolver');
    }

    public function __construct(private readonly ModuleManager $modules) {}

    /**
     * @return Collection<string, Collection<int, array{title: string, subtitle: string|null, url: string}>>
     */
    public function search(string $term, User $user, int $perGroup = 5): Collection
    {
        $term = trim($term);
        if (mb_strlen($term) < 2) {
            return collect();
        }

        return collect(self::$providers)
            ->filter(fn (array $p) => $this->modules->enabled($p['module']))
            ->filter(fn (array $p) => $p['permission'] === null || $user->can($p['permission']))
            ->mapWithKeys(fn (array $p) => [__($p['group']) => collect(($p['resolver'])($term, $perGroup))])
            ->filter(fn (Collection $results) => $results->isNotEmpty());
    }
}
