# 06 - RedirectAuthenticatedFromAuth Middleware

> Prerequisites
>
> - Authentication Panel configured
> - Custom LoginResponse implemented
> - Custom LogoutResponse implemented

---

# Objective

Prevent authenticated users from revisiting authentication pages.

Authenticated users should never see:

```
/auth

/auth/login

/auth/register
```

Instead, they should immediately return to their assigned application panel.

---

# The Problem

Imagine the following sequence.

```
Guest

↓

/auth/login

↓

Login

↓

Admin Panel
```

Everything works correctly.

However...

The user manually types:

```
/auth/login
```

or

```
/auth
```

The Authentication Panel is no longer useful because the user is already authenticated.

Instead, the application should automatically redirect them back to:

```
/admin
```

or

```
/app
```

depending on their role.

---

# The Solution

Create a middleware that intercepts requests to the Authentication Panel.

If the user is already authenticated, redirect them immediately.

Otherwise continue the request normally.

---

# Generate the Middleware

```bash
php artisan make:middleware RedirectAuthenticatedFromAuth
```

---

# Middleware Implementation

Replace the generated file with:

```php
<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectAuthenticatedFromAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            if (
                $request->routeIs('filament.auth.home') ||
                $request->routeIs('filament.auth.auth.login') ||
                $request->routeIs('filament.auth.auth.register')
            ) {
                return $user->hasRole('super_admin')
                    ? redirect('/admin')
                    : redirect('/app');
            }
        }

        return $next($request);
    }
}
```

---

# Register the Middleware

Open:

```
AuthenticationPanelProvider.php
```

Inside:

```php
->middleware([
```

register the middleware **after**:

```php
StartSession::class
```

Correct order:

```php
EncryptCookies::class,
AddQueuedCookiesToResponse::class,
StartSession::class,

RedirectAuthenticatedFromAuth::class,

AuthenticateSession::class,
ShareErrorsFromSession::class,
PreventRequestForgery::class,
SubstituteBindings::class,
DisableBladeIconComponents::class,
DispatchServingFilamentEvent::class,
```

---

# Why Must It Be After StartSession?

This is one of the most important discoveries made while building this architecture.

The middleware checks:

```php
Auth::check()
```

Laravel determines the authenticated user from the session.

Without an active session:

```php
Auth::check()
```

always returns:

```php
false
```

Therefore the middleware must execute only after:

```php
StartSession::class
```

has restored the user's session.

---

# Why We Don't Use authMiddleware()

Filament provides:

```php
->authMiddleware()
```

for protecting authenticated application pages.

However,

```
/auth/login
```

and

```
/auth/register
```

are **guest pages**.

They are not authenticated routes.

Using:

```php
authMiddleware()
```

would mix responsibilities.

Instead,

the redirect middleware belongs to the normal middleware stack of the Authentication Panel.

---

# Request Flow

## Guest

```
Request

↓

Middleware

↓

Auth::check()

↓

false

↓

Continue

↓

Login Page
```

---

## Authenticated User

```
Request

↓

Middleware

↓

Auth::check()

↓

true

↓

Role?

↓

super_admin

↓

/admin

-------------------------

user

↓

/app
```

---

# Testing

## Test 1

Login as:

```
super_admin
```

Visit:

```
/auth/login
```

Expected:

```
/admin
```

---

## Test 2

Visit:

```
/auth
```

Expected:

```
/admin
```

---

## Test 3

Login as:

```
user
```

Visit:

```
/auth/login
```

Expected:

```
/app
```

---

# Common Mistakes

## Middleware registered before StartSession

Symptoms:

```php
Auth::check()
```

always returns:

```php
false
```

---

## Forgetting to include the Home route

Remember that the Authentication Panel also exposes:

```
/auth
```

through:

```
filament.auth.home
```

That route should also be redirected.

---

## Redirect Loop

If authenticated users still receive:

```
ERR_TOO_MANY_REDIRECTS
```

verify:

- The middleware is registered after `StartSession`.
- The middleware only redirects the Authentication Panel's guest routes.
- The redirect target (`/admin` or `/app`) is **not** the Authentication Panel itself.

---

# Final Authentication Flow

```
Guest

↓

/auth/login

↓

Authentication

↓

CustomLoginResponse

↓

Admin Panel
or
App Panel

────────────────────────────

Authenticated User

↓

/auth
/auth/login
/auth/register

↓

RedirectAuthenticatedFromAuth

↓

Admin Panel
or
App Panel

────────────────────────────

Logout

↓

CustomLogoutResponse

↓

/auth/login
```

---

# Result

The authentication experience is now complete.

The application provides:

- One login page
- One registration page
- One logout destination
- Automatic panel redirection after login
- Automatic protection against revisiting authentication pages

The Authentication Panel now behaves as a dedicated authentication gateway rather than an application panel.