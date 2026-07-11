# 07 - Service Container & Dependency Injection

> Prerequisites
>
> - Custom LoginResponse implemented
> - Custom LogoutResponse implemented

---

# Objective

This chapter explains **why** our authentication architecture works.

Instead of modifying Filament's source code, we replace parts of its behavior using Laravel's Service Container.

Understanding this concept is valuable far beyond Filament.

---

# What Is The Service Container?

Laravel contains a central object called the **Service Container**.

Its responsibility is simple:

> Given a type, create (or retrieve) the correct object.

Think of it as a factory.

```
Application

↓

Service Container

↓

Requested Object
```

Laravel uses the container almost everywhere.

Controllers.

Jobs.

Events.

Listeners.

Commands.

Middleware.

Filament.

Everything.

---

# Dependency Injection

Imagine a class needs another class.

Instead of creating it manually:

```php
$mailer = new Mailer();
```

Laravel does it automatically.

Example:

```php
public function __construct(Mailer $mailer)
{
    $this->mailer = $mailer;
}
```

Laravel sees:

```
Mailer
```

asks the Service Container,

then injects the object automatically.

This is called:

> Dependency Injection (DI)

---

# Interfaces vs Implementations

Instead of depending on concrete classes,

Laravel usually depends on interfaces.

Example:

```
LoginResponseContract
```

instead of:

```
LoginResponse
```

Why?

Because interfaces can have multiple implementations.

Example:

```
LoginResponseContract

↓

Filament LoginResponse

or

CustomLoginResponse
```

The framework does not care which implementation it receives.

It only cares that the object satisfies the contract.

---

# What Is A Contract?

A contract is simply an interface.

Example:

```php
interface LoginResponse
{
    public function toResponse($request);
}
```

Any class implementing this interface becomes a valid LoginResponse.

---

# Filament's Default Behavior

Internally Filament does something conceptually similar to:

```php
return app(LoginResponseContract::class);
```

Notice:

Filament never creates:

```php
new LoginResponse()
```

Instead,

it asks Laravel:

```
Please give me a LoginResponseContract.
```

---

# Replacing Filament's LoginResponse

Inside:

```php
AppServiceProvider
```

we registered:

```php
$this->app->singleton(
    LoginResponseContract::class,
    CustomLoginResponse::class,
);
```

Meaning:

Whenever someone asks for:

```
LoginResponseContract
```

return:

```
CustomLoginResponse
```

instead.

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

---

# Why singleton()?

Laravel offers multiple ways to register services.

The two most common are:

```
bind()

singleton()
```

---

## bind()

Creates a **new object every time**.

```
Request 1

↓

new Object()

--------------------

Request 2

↓

new Object()
```

---

## singleton()

Creates the object once.

Then reuses it.

```
Request

↓

Service Container

↓

Existing Object
```

For response classes,

singleton is perfectly acceptable because they hold no request-specific state.

---

# Why We Didn't Modify Vendor Files

Never edit:

```
vendor/
```

Reasons:

- Updates overwrite changes.
- Difficult to maintain.
- Not portable.
- Not supported.

Instead,

replace implementations through the Service Container.

This is Laravel's intended extension mechanism.

---

# PHPDoc Annotations

Consider:

```php
$user = Auth::user();
```

Laravel knows:

```
User
```

Your IDE only knows:

```
Authenticatable
```

Therefore,

methods like:

```php
$user->hasRole()
```

appear undefined.

---

# The Solution

Use:

```php
/** @var User $user */
$user = Auth::user();
```

This annotation is called a:

> PHPDoc annotation

It is ignored by:

- PHP
- Laravel
- Filament

Its only purpose is improving static analysis.

Benefits:

- Autocomplete
- Type checking
- IDE support
- Cleaner development experience

---

# Runtime vs Development

PHPDoc exists only during development.

At runtime:

```
PHP ignores it completely.
```

It does **not**:

- cast objects
- instantiate objects
- change object types

It simply informs tools.

---

# Why We Didn't Use method_exists()

Some IDEs recommend:

```php
method_exists($user, 'hasRole')
```

This is unnecessary.

The authenticated user is already your:

```
App\Models\User
```

The proper solution is to inform the IDE using PHPDoc.

---

# Why This Architecture Is Better

Instead of:

```
Override Filament Login Page

↓

Override Controllers

↓

Modify Vendor Files
```

we used Laravel's built-in extension mechanism.

Everything remains:

- Upgrade-safe
- Testable
- Maintainable
- Framework-friendly

---

# Key Takeaways

Remember these principles.

## 1.

Always prefer:

```
Contracts
```

over concrete implementations.

---

## 2.

Replace behavior using:

```
Service Container
```

instead of editing vendor files.

---

## 3.

Use:

```php
/** @var User $user */
```

to help static analysis.

---

## 4.

Authentication,

Authorization,

and

Redirection

are three different responsibilities.

Keep them separate.

---

# Summary

At this point you should understand not only **how** the authentication architecture works,

but also **why** Laravel and Filament allow it to be implemented without modifying framework code.

This same pattern appears throughout the Laravel ecosystem and is one of the most important concepts to master as an advanced Laravel developer.

---

# Next

> **08 - Common Pitfalls & Debugging**