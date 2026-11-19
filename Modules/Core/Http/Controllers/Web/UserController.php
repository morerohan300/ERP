<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use App\Models\User;
use App\Support\Tenancy\CurrentCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Core\Exports\UsersExport;
use Modules\Core\Http\Requests\StoreUserRequest;
use Modules\Core\Http\Requests\UpdateUserRequest;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Department;
use Modules\Core\Repositories\UserRepository;
use Modules\Core\Services\UserService;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class UserController extends Controller
{
    public function __construct(
        private readonly UserService $users,
        private readonly UserRepository $repository,
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(): View
    {
        return view('core::users.index', [
            'roles' => Role::query()->orderBy('name')->pluck('name', 'name'),
        ]);
    }

    public function create(): View
    {
        return view('core::users.create', $this->formOptions());
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->users->create($data, $request->file('avatar'));

        return $this->status('users.index', __('User added to the company.'));
    }

    public function show(User $user): RedirectResponse
    {
        return redirect()->route('users.edit', $user);
    }

    public function edit(User $user): View
    {
        $pivot = $user->companies()
            ->whereKey(app(CurrentCompany::class)->id())
            ->first()?->pivot;

        return view('core::users.edit', ['user' => $user, 'pivot' => $pivot] + $this->formOptions());
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->users->update($user, $request->validated(), $request->file('avatar'));

        return $this->status('users.index', __('User updated.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->users->removeFromCompany($user);

        return $this->status('users.index', __('User removed from the company.'));
    }

    public function export(): BinaryFileResponse
    {
        Gate::authorize('export', User::class);

        return Excel::download(new UsersExport(User::query()->inCurrentCompany()), 'users-'.now()->toDateString().'.xlsx');
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'roles' => Role::query()->orderBy('name')->pluck('name', 'name'),
            'branches' => Branch::query()->orderBy('name')->pluck('name', 'id'),
            'departments' => Department::query()->orderBy('name')->pluck('name', 'id'),
        ];
    }
}
