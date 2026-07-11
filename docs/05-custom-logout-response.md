# 05 - Custom LogoutResponse

> Prerequisites
>
> - Custom LoginResponse implemented
> - Login redirection working correctly

---

# Objective

After logging out from any panel, users should always return to the central authentication panel.

Instead of allowing Filament to determine where logout should redirect, we replace its default LogoutResponse.

---

# Why Is This Necessary?

Our architecture contains three panels.

```
Authentication Panel

Admin Panel

App Panel
```

Only the Authentication Panel exposes authentication pages.

Neither the Admin Panel nor the App Panel has its own login page.

Therefore:

```
/admin/login
```

and

```
/app/login
```

do not exist.

After logout, users should always return to:

```
/auth/login
```

regardless of which panel they logged out from.

---

# Default Logout Flow

Normally Filament performs something similar to:

```
Logout

↓

Current Panel Login Page
```

For a multi-panel application with only one authentication panel, this behavior is incorrect.

---

# Our Logout Flow

```
Logout

↓

CustomLogoutResponse

↓

/auth/login
```

Simple.

Regardless of where logout originated:

- Admin Panel
- App Panel

the destination is always identical.

---

# Create the Custom LogoutResponse

Create:

```
app/Filament/Auth/CustomLogoutResponse.php
```

```php
<?php

namespace App\Filament\Auth;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class CustomLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        return redirect('/auth/login');
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
use App\Filament\Auth\CustomLogoutResponse;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
```

Inside:

```php
register()
```

register the binding:

```php
$this->app->singleton(
    LogoutResponseContract::class,
    CustomLogoutResponse::class,
);
```

---

# Why Another singleton?

Exactly like LoginResponse,

Filament asks Laravel:

```
Give me a LogoutResponseContract.
```

Laravel checks the Service Container.

Instead of returning Filament's default implementation,

it returns:

```
CustomLogoutResponse
```

Conceptually:

```
Filament

↓

LogoutResponseContract

↓

Laravel Service Container

↓

CustomLogoutResponse
```

---

# Testing

Login as:

```
super_admin
```

Navigate to:

```
/admin
```

Click:

```
Logout
```

Expected:

```
/auth/login
```

---

Repeat the same test using:

```
user
```

inside:

```
/app
```

Expected:

```
/auth/login
```

---

# Common Mistakes

## Forgetting the singleton registration

Symptoms:

Logout redirects to an unexpected location.

or

Filament continues using its default LogoutResponse.

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
Authenticated User

↓

Logout

↓

Filament

↓

LogoutResponseContract

↓

Laravel Service Container

↓

CustomLogoutResponse

↓

/auth/login
```

---

# Result

Logout is now centralized.

Every panel shares the same logout destination.

The application now has:

- One login page
- One registration page
- One logout destination

The only remaining usability issue is preventing authenticated users from accessing authentication pages again.

That is implemented in the next chapter.

---

# Next

> **06 - RedirectAuthenticatedFromAuth Middleware**