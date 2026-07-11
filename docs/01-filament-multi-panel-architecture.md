# Filament v5 Multi-Panel Authentication Architecture

> Version: 1.0
>
> Target:
> - Laravel 13+
> - Filament v5
> - Spatie Permission

---

# Overview

This project uses **three separate Filament panels** while exposing **only a single authentication entry point** to users.

Instead of every panel owning its own login page, authentication is centralized inside a dedicated **Authentication Panel**.

After a successful login, users are redirected to the appropriate panel according to their role.

This architecture keeps authentication isolated from the application itself and avoids duplicated login pages.

---

# High-Level Architecture

```
                    Guest
                      │
                      ▼
                /auth/login
                      │
                      ▼
           Authentication Panel
                      │
                      ▼
           Custom LoginResponse
                      │
          ┌───────────┴───────────┐
          ▼                       ▼
      Admin Panel             App Panel
```

---

# Panels

The application consists of three independent Filament panels.

## Authentication Panel

Purpose:

- Login
- Registration
- Password Reset
- Email Verification (future)
- Two Factor Authentication (future)

This panel **does not contain application pages**.

It only exists to authenticate users.

Configuration:

```php
->id('auth')
->path('auth')
->login()
->registration()
```

---

## Admin Panel

Purpose:

Administration.

Contains:

- Dashboard
- Resources
- Widgets
- Filament Shield

This panel does **not** expose its own login page.

Configuration:

```php
->id('admin')
->path('admin')
```

---

## App Panel

Purpose:

Normal user application.

Contains:

- Dashboard
- Resources
- Widgets

This panel also does **not** expose its own login page.

Configuration:

```php
->id('app')
->path('app')
```

---

# Authentication Flow

```
Guest

↓

/auth/login

↓

Authenticate

↓

CustomLoginResponse

↓

Role?

↓

super_admin ─────► /admin

user ────────────► /app
```

Authentication is centralized.

Panel selection happens **after** authentication succeeds.

---

# Authorization Flow

Authentication and authorization are intentionally separated.

Authentication answers:

> "Who is this user?"

Authorization answers:

> "Which panel may this user access?"

Authorization is implemented inside:

```php
User::canAccessPanel()
```

No authorization logic is duplicated inside middleware.

---

# Logout Flow

```
Authenticated User

↓

Logout

↓

CustomLogoutResponse

↓

/auth/login
```

Regardless of which panel initiated the logout, users always return to the central authentication panel.

---

# Responsibilities

Each component owns exactly one responsibility.

| Component | Responsibility |
|-----------|----------------|
| Authentication Panel | Authenticate users |
| User::canAccessPanel() | Authorize panel access |
| CustomLoginResponse | Redirect after successful login |
| CustomLogoutResponse | Redirect after logout |
| RedirectAuthenticatedFromAuth Middleware | Prevent authenticated users from revisiting authentication pages |

---

# Why This Architecture?

Advantages:

- Single login page
- Single registration page
- No duplicated authentication logic
- Clear separation of authentication and authorization
- Works naturally with Filament's extension points
- No vendor modifications
- Easy to maintain
- Easy to reuse across projects

---

# Design Principles

This architecture intentionally follows:

- Single Responsibility Principle (SRP)
- Open/Closed Principle (OCP)
- Dependency Inversion Principle (DIP)

Every customization is performed through Filament's official extension points rather than overriding framework internals.

---

# Folder Structure

```
app/

├── Filament/
│   └── Auth/
│       ├── CustomLoginResponse.php
│       └── CustomLogoutResponse.php
│
├── Http/
│   └── Middleware/
│       └── RedirectAuthenticatedFromAuth.php
│
├── Models/
│   └── User.php
│
└── Providers/
    ├── AppServiceProvider.php
    └── Filament/
        ├── AuthenticationPanelProvider.php
        ├── AdminPanelProvider.php
        └── AppPanelProvider.php
```

---

# Next Document

Continue with:

> **02-authentication-panel-setup.md**

This document covers the complete implementation of the Authentication Panel.