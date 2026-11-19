<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Http\Requests\StoreDepartmentRequest;
use Modules\Core\Http\Requests\UpdateDepartmentRequest;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Department;
use Modules\Core\Repositories\DepartmentRepository;

final class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentRepository $departments)
    {
        $this->authorizeResource(Department::class, 'department');
    }

    public function index(): View
    {
        return view('core::departments.index');
    }

    public function create(): View
    {
        return view('core::departments.create', $this->formOptions());
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->departments->create($request->validated());

        return $this->status('departments.index', __('Department created.'));
    }

    public function show(Department $department): RedirectResponse
    {
        return redirect()->route('departments.edit', $department);
    }

    public function edit(Department $department): View
    {
        return view('core::departments.edit', ['department' => $department] + $this->formOptions());
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->departments->update($department, $request->validated());

        return $this->status('departments.index', __('Department updated.'));
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->departments->delete($department);

        return $this->status('departments.index', __('Department deleted.'));
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'branches' => Branch::query()->orderBy('name')->pluck('name', 'id'),
            'parents' => Department::query()->orderBy('name')->pluck('name', 'id'),
            'managers' => User::query()->inCurrentCompany()->orderBy('name')->pluck('name', 'id'),
        ];
    }
}
