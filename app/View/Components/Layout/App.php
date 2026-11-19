<?php

declare(strict_types=1);

namespace App\View\Components\Layout;

use App\Support\Modules\Module;
use App\Support\Modules\ModuleManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

/**
 * Builds the permission-filtered sidebar menu from every enabled module's
 * module.json before handing it to the layout template.
 */
final class App extends Component
{
    /** @param array<int, array<string, string>> $breadcrumbs */
    public function __construct(
        public ?string $title = null,
        public array $breadcrumbs = [],
    ) {}

    public function render(): View
    {
        return view('components.layout.app', ['menu' => $this->menu()]);
    }

    /** @return array<string, array<int, array<string, mixed>>> */
    private function menu(): array
    {
        $user = auth()->user();

        return app(ModuleManager::class)
            ->enabledModules()
            ->flatMap(fn (Module $module): array => $this->moduleMenu($module, $user))
            ->filter()
            ->groupBy('section')
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function moduleMenu(Module $module, mixed $user): array
    {
        return collect($module->menu)
            ->map(fn (array $item): ?array => $this->visibleItem($item, $user))
            ->filter()
            ->values()
            ->all();
    }

    /** @param array<string, mixed> $item */
    private function visibleItem(array $item, mixed $user): ?array
    {
        if (isset($item['permission']) && ! $user?->can((string) $item['permission'])) {
            return null;
        }

        if (isset($item['children'])) {
            $item['children'] = collect($item['children'])
                ->map(fn (array $child): ?array => $this->visibleItem($child, $user))
                ->filter()
                ->values()
                ->all();

            if ($item['children'] === []) {
                return null;
            }
        } elseif (isset($item['route']) && ! Route::has((string) $item['route'])) {
            return null;
        }

        return $item;
    }
}
