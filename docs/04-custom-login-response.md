# 04 - Custom LoginResponse

> Prerequisites
>
> - Authentication Panel configured
> - Admin Panel configured
> - App Panel configured
> - User model implements `FilamentUser`
> - `canAccessPanel()` implemented

---

# Objective

After a successful authentication, users should be redirected to the correct Filament panel based on their role.

Instead of allowing Filament to decide where to redirect users, we replace its default LoginResponse with our own implementation.

---

# Why Is This Necessary?

By default, Filament redirects users back to the current panel after login.

For normal applications this works perfectly.

However, in this architecture the Authentication Panel exists **only** to authenticate users.

It is **not** an application panel.

Therefore returning users to:

```
/auth
```

is incorrect.

Instead:

```
super_admin

↓

/admin
```

and

```
user

↓

/app
```

---

# The Default Flow

Filament normally performs something similar to:

```
Login

↓

Default LoginResponse

↓

Current Panel

↓

Dashboard
```

That works when every panel owns its own login page.

It does **not** work when authentication is centralized.

---

# Our Flow

```
Login

↓

Authentication Successful

↓

CustomLoginResponse

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

The Authentication Panel is never visited again after login.

---

# Create the Custom LoginResponse

Create:

```
app/Filament/Auth/CustomLoginResponse.php
```

```php
<?php

namespace App\Filament\Auth;

use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportRedirects\Redirector;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->hasRole('super_admin')
            ? redirect('/admin')
            : redirect('/app');
    }
}
```

---

# Register the Response

Open:

```
app/Providers/AppServiceProvider.php
```

Import:

```php
use App\Filament\Auth\CustomLoginResponse;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
```

Inside:

```php
public function register(): void
```

register the binding:

```php
$this->app->singleton(
    LoginResponseContract::class,
    CustomLoginResponse::class,
);
```

---

# What Does singleton() Do?

Laravel uses a Service Container.

Filament does **not** instantiate LoginResponse directly.

Instead it asks Laravel:

```
Give me a LoginResponseContract.
```

Laravel checks its container.

Normally it would return Filament's default implementation.

By registering a singleton, we replace that implementation globally.

Conceptually:

```
Filament

↓

LoginResponseContract

↓

Laravel Service Container

↓

CustomLoginResponse
```

No Filament source code is modified.

No vendor files are edited.

---

# Why singleton Instead of Editing Vendor Files?

Advantages:

- Safe during package updates.
- Easy to maintain.
- Uses Laravel's Dependency Injection.
- Official extension point.

Always prefer replacing implementations through the Service Container instead of editing package code.

---

# Understanding This Line

```php
/** @var User $user */
$user = Auth::user();
```

This is called a **PHPDoc annotation**.

It is **not** executed.

Laravel ignores it.

PHP ignores it.

It only exists for static analysis tools such as Intelephense or PHPStan.

Without it:

```php
Auth::user()
```

is treated as:

```
Authenticatable
```

The IDE cannot detect methods like:

```php
$user->hasRole()
```

Adding the annotation informs the IDE that the authenticated user is actually an instance of:

```
App\Models\User
```

This improves:

- Autocomplete
- Static analysis
- Error detection

without changing runtime behavior.

---

# Why We Use Hardcoded URLs

The redirect uses:

```php
redirect('/admin')
```

instead of route names.

Reasons:

- Simpler.
- Easier to read.
- No dependency on route names.
- Matches the fixed panel paths.

This can be changed later if panel paths become dynamic.

---

# Testing

Visit:

```
/auth/login
```

Login as:

```
super_admin
```

Expected:

```
/admin
```

---

Login as:

```
user
```

Expected:

```
/app
```

---

# Common Mistakes

## Forgetting to register the singleton

Symptoms:

```
ERR_TOO_MANY_REDIRECTS
```

or

Filament continues using its default LoginResponse.

---

## Registering the singleton in boot()

Incorrect:

```php
boot()
```

Correct:

```php
register()
```

Container bindings belong inside:

```php
register()
```

---

## Forgetting to clear caches

Run:

```bash
php artisan optimize:clear
```

after registering the singleton.

---

# Execution Flow

```
User submits credentials

↓

Authentication succeeds

↓

canAccessPanel()

↓

CustomLoginResponse

↓

Role Check

↓

Redirect

↓

Admin Panel

or

App Panel
```

---

# Result

At this point the application now supports:

- One login page
- Multiple application panels
- Automatic panel redirection after login

The only remaining issue is preventing authenticated users from revisiting the authentication pages.

That is covered in the next chapter.

---

# Next

> **05 - Custom LogoutResponse**