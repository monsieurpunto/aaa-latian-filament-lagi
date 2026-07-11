# 02 - Authentication Panel Setup

> Prerequisites
>
> - Laravel 13+
> - Filament v5 installed
> - Admin Panel already created
> - App Panel already created

---

# Objective

By the end of this document you will have:

- An Authentication Panel
- An Admin Panel
- An App Panel

Only the Authentication Panel exposes:

- Login
- Register

The Admin and App panels have **no login pages**.

---

# Step 1 - Create the Authentication Panel

Generate a new Filament panel.

```bash
php artisan make:filament-panel auth
```

---

# Step 2 - Configure the Authentication Panel

Open:

```
app/Providers/Filament/AuthenticationPanelProvider.php
```

Replace the contents with:

```php
<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RedirectAuthenticatedFromAuth;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AuthenticationPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('auth')
            ->path('auth')
            ->login()
            ->registration()
            ->colors([
                'primary' => Color::Teal,
            ])
            ->discoverPages(
                in: app_path('Filament/Auth/Pages'),
                for: 'App\\Filament\\Auth\\Pages',
            )
            ->middleware([
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

---

# Step 3 - Configure the Admin Panel

The Admin Panel should **NOT** expose its own login page.

Example:

```php
->id('admin')
->path('admin')
```

Do **not** include:

```php
->login()
```

---

# Step 4 - Configure the App Panel

Likewise:

```php
->id('app')
->path('app')
```

Do **not** include:

```php
->login()
```

---

# Step 5 - Verify the Routes

Run:

```bash
php artisan route:list
```

Expected routes:

```
auth
auth/login
auth/register
auth/logout

admin
admin/logout

app
app/logout
```

Notice:

There should **NOT** be:

```
admin/login

app/login
```

---

# Step 6 - Test

Visit:

```
/auth/login
```

Expected:

Filament Login page.

---

Visit:

```
/auth/register
```

Expected:

Filament Register page.

---

Visit:

```
/admin
```

Expected:

Authentication required.

---

Visit:

```
/app
```

Expected:

Authentication required.

---

# Common Mistakes

## Forgetting to remove `->login()`

Both Admin and App panels must **not** contain:

```php
->login()
```

Otherwise multiple login pages will exist.

---

## Using the wrong panel id

The Authentication Panel id is:

```php
auth
```

Not:

```php
authentication
```

This id is used later inside:

```php
User::canAccessPanel()
```

and throughout the authentication flow.

---

## Middleware order

The custom middleware:

```php
RedirectAuthenticatedFromAuth
```

must come **after**

```php
StartSession::class
```

Otherwise:

```php
Auth::check()
```

will always return `false`.

---

# Result

You now have a clean three-panel architecture:

```
Authentication Panel
    ├── Login
    └── Register

Admin Panel
    └── Dashboard

App Panel
    └── Dashboard
```

Authentication has been centralized, but users are **not yet redirected to their destination panel after login**.

That is implemented in the next document.

---

**Next:**

> **03 - User Model and Panel Authorization**