<?php

use App\Http\Middleware\CheckActiveUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

uses(RefreshDatabase::class);

function makeRequest(): Request
{
    Session::start();
    $request = Request::create('/admin');
    $request->setLaravelSession(Session::driver());
    $request->setUserResolver(fn ($guard = null) => app('auth')->guard($guard)->user());

    return $request;
}

it('keeps active users authenticated', function () {
    $user = User::factory()->create(['is_active' => true, 'employee_id' => null]);
    Auth::login($user);

    $middleware = new CheckActiveUser;
    $response = $middleware->handle(makeRequest(), fn ($req) => response('ok'));

    expect(Auth::check())->toBeTrue();
    expect($response->getContent())->toBe('ok');
});

it('logs out and redirects inactive users', function () {
    $user = User::factory()->create(['is_active' => false, 'employee_id' => null]);
    Auth::login($user);

    $middleware = new CheckActiveUser;
    $response = $middleware->handle(makeRequest(), fn ($req) => response('ok'));

    expect(Auth::check())->toBeFalse();
    expect($response->getStatusCode())->toBe(302);
});
