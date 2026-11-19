<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use App\Support\Modules\ModuleManager;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Services\ModuleService;

final class ModuleController extends Controller
{
    public function __construct(private readonly ModuleService $modules) {}

    public function index(ModuleManager $manager): View
    {
        return view('core::modules.index', [
            'modules' => $manager->all(),
            'companyId' => app(CurrentCompany::class)->id(),
            'manager' => $manager,
        ]);
    }

    public function toggle(Request $request, string $module): RedirectResponse
    {
        $this->modules->setEnabled($module, $request->boolean('enabled'));

        return $this->status('modules.index', __('Module access updated.'));
    }
}
