<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Core\Http\Requests\StoreUserRequest;
use Modules\Core\Http\Requests\UpdateUserRequest;
use Modules\Core\Http\Resources\UserResource;
use Modules\Core\Repositories\UserRepository;
use Modules\Core\Services\UserService;

final class UserController extends Controller
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UserService $service,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        [$filters, $perPage] = $this->listParameters($request);

        return UserResource::collection($this->users->paginate($filters, $perPage));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->service->create($request->validated());

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user->loadMissing('roles'));
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        return new UserResource($this->service->update($user, $request->validated()));
    }

    public function destroy(User $user): JsonResponse
    {
        $this->service->removeFromCompany($user);

        return response()->json(null, 204);
    }
}
