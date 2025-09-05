<?php

/*
 * api-noauth.php
 * Copyright (c) 2021 james@firefly-iii.org
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
// Cron job API routes:
use FireflyIII\Http\Middleware\AcceptHeaders;

// Simple test route
Route::get('v1/simple-test', function () {
    return response()->json(['message' => 'Simple test works']);
});

// User registration API routes (no authentication required):
Route::group(
    [
        'namespace' => 'FireflyIII\Api\V1\Controllers\System',
        'prefix'    => 'v1/register',
        'as'        => 'api.v1.register.',
    ],
    static function (): void {
        Route::post('', ['uses' => 'RegisterController@register', 'as' => 'store']);
    }
);

Route::group(
    [
        'namespace'  => 'FireflyIII\Api\V1\Controllers\System',
        'prefix'     => 'v1/cron',
        'as'         => 'api.v1.cron.',
        'middleware' => [AcceptHeaders::class],
    ],
    static function (): void {
        Route::get('{cliToken}', ['uses' => 'CronController@cron', 'as' => 'index']);
    }
);

// Test route to debug authentication issue
Route::get('v1/test', function () {
    return response()->json(['message' => 'Test route works without authentication']);
});

// User registration API routes (no authentication required):
Route::group(
    [
        'namespace' => 'FireflyIII\Api\V1\Controllers\System',
        'prefix'    => 'v1/register',
        'as'        => 'api.v1.register.',
        'middleware' => [AcceptHeaders::class],
    ],
    static function (): void {
        Route::post('', ['uses' => 'RegisterController@register', 'as' => 'store']);
    }
);

// OAuth token API routes (no authentication required):
Route::group(
    [
        'namespace' => 'FireflyIII\Api\V1\Controllers\System',
        'prefix'    => 'v1/oauth',
        'as'        => 'api.v1.oauth.',
        'middleware' => [AcceptHeaders::class],
    ],
    static function (): void {
        Route::post('token', ['uses' => 'OAuthController@token', 'as' => 'token']);
    }
);
