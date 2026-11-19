<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Http\Requests\StoreRoleRequest;
use Modules\Core\Http\Requests\UpdateRoleRequest;
use Modules\Core\Services\PermissionService;
use Spatie\Permission\Models\Role;

final class RoleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    public function index(): View
    {
        return view('core::roles.index', [
            'roles' => Role::query()->where('company_id', app(CurrentCompany::class)->id())->with('permissions')->orderBy('name')->get(),
        ]);
    }

    public function create(PermissionService $permissions): View
    {
        return view('core::roles.create', ['matrix' => $permissions->matrix()]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = Role::query()->create([
            'name' => $data['name'],
            'guard_name' => 'web',
            'company_id' => app(CurrentCompany::class)->id(),
        ]);
        $role->syncPermissions($data['permissions'] ?? []);

        return $this->status('roles.index', __('Role created.'));
    }

    public function show(Role $role): RedirectResponse
    {
        return redirect()->route('roles.edit', $role);
    }

    public function edit(Role $role, PermissionService $permissions): View
    {
        return view('core::roles.edit', [
            'role' => $role,
            'matrix' => $permissions->matrix(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return $this->status('roles.index', __('Role updated.'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        return $this->status('roles.index', __('Role deleted.'));
    }
}
