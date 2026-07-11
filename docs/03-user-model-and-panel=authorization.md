# 03 - User Model & Panel Authorization

> Prerequisites
>
> - Authentication Panel configured
> - Admin Panel configured
> - App Panel configured
> - Spatie Permission installed

---

# Objective

This document explains how Filament determines whether a user is allowed to enter a panel.

By the end of this document:

- Users can authenticate through the Authentication Panel.
- Every panel is protected.
- Authorization logic exists in **one place only**.

---

# Authentication vs Authorization

These two concepts are different.

## Authentication

Authentication answers:

> "Who is this user?"

Example:

```
Email

+

Password

↓

Authenticated
```

Authentication is handled by:

- Authentication Panel
- Login Page

---

## Authorization

Authorization answers:

> "What is this authenticated user allowed to access?"

Example:

```
Authenticated

↓

Role?

↓

Admin Panel

or

App Panel
```

Authorization is **not** handled by middleware.

Instead, Filament asks your User model directly.

---

# Implement FilamentUser

Open:

```
app/Models/User.php
```

Implement:

```php
use Filament\Models\Contracts\FilamentUser;
```

Then:

```php
class User extends Authenticatable implements FilamentUser
```

---

# canAccessPanel()

Filament calls:

```php
$user->canAccessPanel($panel)
```

before allowing a user to enter any panel.

Implement:

```php
public function canAccessPanel(Panel $panel): bool
{
    return match ($panel->getId()) {
        'auth' => true,

        'admin' => $this->hasRole('super_admin'),

        'app' => $this->hasAnyRole([
            'super_admin',
            'user',
        ]),

        default => false,
    };
}
```

---

# How It Works

Imagine the user visits:

```
/admin
```

Filament internally performs something similar to:

```php
if (! $user->canAccessPanel($panel)) {
    abort();
}
```

If:

```
panel id = admin
```

then

```php
$this->hasRole('super_admin')
```

is evaluated.

If the user has the role:

```
super_admin
```

access is granted.

Otherwise access is denied.

---

# Why The Authentication Panel Always Returns True

Notice:

```php
'auth' => true,
```

This is intentional.

Every authenticated user should always be allowed to access:

```
/auth/login
```

or

```
/auth/register
```

The Authentication Panel should never reject a valid authenticated user.

Later, a middleware will redirect authenticated users away from these pages.

Authorization and redirection are two different responsibilities.

---

# Role Matrix

| Role | Auth | Admin | App |
|------|------|-------|-----|
| Guest | Login only | ❌ | ❌ |
| super_admin | ✅ | ✅ | ✅ |
| user | ✅ | ❌ | ✅ |

---

# Why We Don't Use Middleware

A common approach is:

```
Admin Middleware

↓

Check Role
```

This duplicates authorization logic.

Instead, Filament already provides:

```php
canAccessPanel()
```

Using this method keeps all panel authorization inside the User model.

One method.

One responsibility.

---

# Common Mistake

Using the wrong panel id.

Example:

```php
'authentication' => true,
```

when the panel id is actually:

```php
'auth'
```

Symptoms:

```
These credentials do not match our records.
```

even though:

- Email is correct.
- Password is correct.

This happens because authentication succeeds but panel authorization fails.

Filament intentionally displays a generic authentication error for security reasons.

---

# Execution Flow

```
Guest

↓

Login

↓

Credentials Valid?

↓

YES

↓

Retrieve User

↓

canAccessPanel()

↓

YES

↓

Continue Authentication

↓

CustomLoginResponse
```

Notice:

```
CustomLoginResponse
```

is **never reached** if

```php
canAccessPanel()
```

returns:

```php
false
```

---

# Benefits

This design provides:

- One authorization method
- No duplicated role checks
- No authorization middleware
- Easy maintenance
- Native Filament integration

---

# Next

Continue with:

> **04 - Custom LoginResponse**