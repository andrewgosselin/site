<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseApiController
{
    /**
     * Get a list of users.
     * 
     * @group User Management
     * @unauthenticated
     */
    public function index(): JsonResponse
    {
        $users = User::paginate();

        return $this->success(
            UserResource::collection($users),
            [
                'pagination' => [
                    'total' => $users->total(),
                    'count' => $users->count(),
                    'per_page' => $users->perPage(),
                    'current_page' => $users->currentPage(),
                    'total_pages' => $users->lastPage(),
                ],
            ]
        );
    }

    /**
     * Get the authenticated user.
     * 
     * @group User Management
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }
}
