<?php

/*
 * OAuthTokenRequest.php
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

namespace FireflyIII\Api\V1\Requests\System;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class OAuthTokenRequest
 * 
 * Request validation for OAuth token requests.
 */
class OAuthTokenRequest extends FormRequest
{
    /**
     * Allow token requests for everyone (no authentication required).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The rules that the incoming request must be matched against.
     */
    public function rules(): array
    {
        return [
            'grant_type'    => 'required|string|in:password,client_credentials,refresh_token',
            'client_id'     => 'required|string',
            'client_secret' => 'required|string',
            'username'      => 'required_if:grant_type,password|email',
            'password'      => 'required_if:grant_type,password|string',
            'scope'         => 'nullable|string',
            'refresh_token' => 'required_if:grant_type,refresh_token|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'grant_type.required'     => 'Grant type is required.',
            'grant_type.in'           => 'Grant type must be one of: password, client_credentials, refresh_token.',
            'client_id.required'      => 'Client ID is required.',
            'client_secret.required'  => 'Client secret is required.',
            'username.required_if'    => 'Username is required for password grant type.',
            'username.email'          => 'Username must be a valid email address.',
            'password.required_if'    => 'Password is required for password grant type.',
            'refresh_token.required_if' => 'Refresh token is required for refresh_token grant type.',
        ];
    }
}
