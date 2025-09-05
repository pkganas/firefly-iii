<?php

/*
 * OAuthController.php
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

use FireflyIII\Api\V1\Controllers\Controller;
use FireflyIII\Api\V1\Requests\System\OAuthTokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Class OAuthController.
 * 
 * Handles OAuth token requests via API.
 */
class OAuthController extends Controller
{
    /**
     * Get an OAuth access token.
     * 
     * This endpoint allows users to authenticate and get an access token
     * for API usage without requiring the web interface.
     */
    public function token(OAuthTokenRequest $request): JsonResponse
    {
        try {
            // Validate the grant type
            $grantType = $request->input('grant_type');
            
            if ($grantType !== 'password') {
                return response()->json([
                    'error' => 'unsupported_grant_type',
                    'error_description' => 'The authorization grant type is not supported by the authorization server.',
                    'hint' => 'Only "password" grant type is supported',
                    'message' => 'The authorization grant type is not supported by the authorization server.'
                ], 400)->header('Content-Type', self::JSON_CONTENT_TYPE);
            }
            
            // Get client credentials
            $clientId = $request->input('client_id');
            $clientSecret = $request->input('client_secret');
            
            // Find the OAuth client
            $client = \Laravel\Passport\Client::where('id', $clientId)
                ->where('secret', $clientSecret)
                ->where('revoked', false)
                ->first();
                
            if (!$client) {
                return response()->json([
                    'error' => 'invalid_client',
                    'error_description' => 'Client authentication failed.',
                    'message' => 'Invalid client credentials.'
                ], 401)->header('Content-Type', self::JSON_CONTENT_TYPE);
            }
            
            // Get user credentials
            $username = $request->input('username');
            $password = $request->input('password');
            
            // Authenticate the user
            $user = \FireflyIII\User::where('email', $username)->first();
            
            if (!$user || !Hash::check($password, $user->password)) {
                return response()->json([
                    'error' => 'invalid_grant',
                    'error_description' => 'The provided authorization grant is invalid.',
                    'message' => 'Invalid user credentials.'
                ], 401)->header('Content-Type', self::JSON_CONTENT_TYPE);
            }
            
            // Create a personal access token
            $token = $user->createToken('API Token', ['*']);
            
            return response()->json([
                'token_type' => 'Bearer',
                'expires_in' => 3600, // 1 hour
                'access_token' => $token->accessToken,
                'refresh_token' => null, // Personal access tokens don't have refresh tokens
            ])->header('Content-Type', self::JSON_CONTENT_TYPE);
            
        } catch (\Exception $e) {
            Log::error('OAuth token request failed: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'server_error',
                'error_description' => 'An error occurred while processing the token request.',
                'message' => 'Token request failed.'
            ], 500)->header('Content-Type', self::JSON_CONTENT_TYPE);
        }
    }
}
