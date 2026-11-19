<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Web;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Http\Requests\StoreBranchRequest;
use Modules\Core\Http\Requests\UpdateBranchRequest;
use Modules\Core\Models\Branch;
use Modules\Core\Repositories\BranchRepository;

final class BranchController extends Controller
{
    public function __construct(private readonly BranchRepository $branches)
    {
        $this->authorizeResource(Branch::class, 'branch');
    }

    public function index(): View
    {
        return view('core::branches.index');
    }

    public function create(): View
    {
        return view('core::branches.create');
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $this->branches->create($request->validated());

        return $this->status('branches.index', __('Branch created.'));
    }

    public function show(Branch $branch): RedirectResponse
    {
        return redirect()->route('branches.edit', $branch);
    }

    public function edit(Branch $branch): View
    {
        return view('core::branches.edit', ['branch' => $branch]);
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $this->branches->update($branch, $request->validated());

        return $this->status('branches.index', __('Branch updated.'));
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        $this->branches->delete($branch);

        return $this->status('branches.index', __('Branch deleted.'));
    }
}
