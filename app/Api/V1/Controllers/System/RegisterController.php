<?php

/*
 * RegisterController.php
 * Copyright (c) 2024 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see https://www.gnu.org/licenses/.
 */

declare(strict_types=1);

namespace FireflyIII\Api\V1\Controllers\System;

use FireflyIII\Api\V1\Requests\System\ApiUserRegistrationRequest;
use FireflyIII\Events\RegisteredUser;
use FireflyIII\Exceptions\FireflyException;
use FireflyIII\Notifications\Notifiables\OwnerNotifiable;
use FireflyIII\Repositories\User\UserRepositoryInterface;
use FireflyIII\Transformers\UserTransformer;
use FireflyIII\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\JsonApiSerializer;

/**
 * Class RegisterController.
 * 
 * Handles user registration via API without requiring authentication.
 */
class RegisterController extends BaseController
{
    private UserRepositoryInterface $repository;

    /**
     * RegisterController constructor.
     */
    public function __construct()
    {
        $this->middleware(
            function ($request, $next) {
                $this->repository = app(UserRepositoryInterface::class);

                return $next($request);
            }
        );
    }

    /**
     * Register a new user via API.
     * 
     * This endpoint allows users to register without requiring authentication,
     * making it suitable for API-only usage.
     */
    public function register(ApiUserRegistrationRequest $request): JsonResponse
    {
        // Check if registration is allowed
        $allowRegistration = $this->allowedToRegister();
        if (false === $allowRegistration) {
            return response()->json([
                'message' => 'Registration is currently not available.',
                'errors' => ['registration' => ['Registration is disabled.']]
            ], 403);
        }

        $data = $request->getAll();
        
        // Create the user
        $user = $this->repository->store($data);
        
        // Fire the RegisteredUser event to trigger all necessary setup steps
        // This ensures API-created users get the same setup as UI-created users
        $owner = new OwnerNotifiable();
        event(new RegisteredUser($owner, $user));
        
        Log::info(sprintf('Registered new user via API: %s', $user->email));
        
        $manager = $this->getManager();

        // make resource
        /** @var UserTransformer $transformer */
        $transformer = app(UserTransformer::class);
        $transformer->setParameters(new \Symfony\Component\HttpFoundation\ParameterBag($this->getParameters()));

        $resource = new Item($user, $transformer, 'users');

        return response()->json($manager->createData($resource)->toArray())->header('Content-Type', 'application/vnd.api+json');
    }

    /**
     * Check if registration is allowed.
     * 
     * @throws FireflyException
     */
    protected function allowedToRegister(): bool
    {
        // Check if using external identity provider
        if ('web' !== config('firefly.authentication_guard')) {
            return false;
        }

        // Check if registration is enabled
        $allowRegistration = (bool) app('fireflyconfig')->get('allow_registration', true)->data;
        
        return $allowRegistration;
    }

    /**
     * Get manager for JSON API responses.
     */
    private function getManager(): Manager
    {
        $manager = new Manager();
        $baseUrl = request()->getSchemeAndHttpHost().'/api/v1';
        $manager->setSerializer(new JsonApiSerializer($baseUrl));

        return $manager;
    }

    /**
     * Get parameters for the request.
     */
    private function getParameters(): array
    {
        return [
            'page' => (int) request()->get('page', 1),
            'limit' => (int) request()->get('limit', 50),
        ];
    }
}
