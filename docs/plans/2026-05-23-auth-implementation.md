# Auth System Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add reader auth (email/password + Google/GitHub OAuth) and admin auth (separate login, `is_admin` flag) to the blog.

**Architecture:** Single `User` model with `is_admin` boolean. Breeze scaffolds email/password flows. Socialite handles OAuth via a `SocialAccount` pivot model. Admin routes are protected by `EnsureIsAdmin` middleware; admin is seeded, never self-registered.

**Tech Stack:** Laravel Breeze (Blade), laravel/socialite, Laravel's built-in `web` guard, PHPUnit feature tests.

---

### Task 1: Install Dependencies

**Files:**
- Modify: `composer.json` (via composer)
- Modify: `package.json` (via npm, Breeze publishes assets)

**Step 1: Install Laravel Breeze**

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade --no-interaction
```

Expected output: Breeze publishes auth views to `resources/views/auth/`, controllers to `app/Http/Controllers/Auth/`, and routes to `routes/auth.php`.

**Step 2: Install Socialite**

```bash
composer require laravel/socialite
```

**Step 3: Build frontend assets**

```bash
npm install && npm run build
```

**Step 4: Commit**

```bash
git add composer.json composer.lock package.json package-lock.json resources/ app/Http/Controllers/Auth/ routes/auth.php
git commit -m "Install Breeze and Socialite"
```

---

### Task 2: Database Migrations

**Files:**
- Create: `database/migrations/YYYY_MM_DD_add_is_admin_to_users_table.php`
- Create: `database/migrations/YYYY_MM_DD_create_social_accounts_table.php`

**Step 1: Generate migrations**

```bash
php artisan make:migration add_is_admin_to_users_table --no-interaction
php artisan make:migration create_social_accounts_table --no-interaction
```

**Step 2: Write `add_is_admin_to_users_table` migration**

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('is_admin')->default(false)->after('remember_token');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('is_admin');
    });
}
```

**Step 3: Write `create_social_accounts_table` migration**

```php
public function up(): void
{
    Schema::create('social_accounts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('provider');
        $table->string('provider_id');
        $table->text('token')->nullable();
        $table->timestamp('created_at')->nullable();

        $table->unique(['provider', 'provider_id']);
        $table->index('user_id');
    });
}

public function down(): void
{
    Schema::dropIfExists('social_accounts');
}
```

**Step 4: Run migrations**

```bash
php artisan migrate
```

**Step 5: Commit**

```bash
git add database/migrations/
git commit -m "Add is_admin column and social_accounts table"
```

---

### Task 3: SocialAccount Model + User Updates

**Files:**
- Create: `app/Models/SocialAccount.php`
- Modify: `app/Models/User.php`
- Create: `database/factories/SocialAccountFactory.php` (for tests)

**Step 1: Create the model**

```bash
php artisan make:model SocialAccount --no-interaction
```

**Step 2: Write `SocialAccount` model**

Replace generated content:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    public $timestamps = false;

    protected $fillable = ['provider', 'provider_id', 'token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

**Step 3: Update `User` model**

Add `is_admin` to fillable and add `socialAccounts` relationship. Add `is_admin` cast:

```php
// In casts():
'is_admin' => 'boolean',

// Add relationship:
use Illuminate\Database\Eloquent\Relations\HasMany;

public function socialAccounts(): HasMany
{
    return $this->hasMany(SocialAccount::class);
}
```

Also update the `#[Fillable]` attribute to include `is_admin`:

```php
#[Fillable(['name', 'email', 'password', 'is_admin'])]
```

**Step 4: Add `admin` factory state to `UserFactory`**

```php
public function admin(): static
{
    return $this->state(fn (array $attributes) => [
        'is_admin' => true,
    ]);
}
```

**Step 5: Write failing test**

In `tests/Feature/AuthTest.php` (create new file):

```bash
php artisan make:test AuthTest --phpunit --no-interaction
```

```php
public function test_admin_factory_state_sets_is_admin_true(): void
{
    $user = User::factory()->admin()->create();
    $this->assertTrue($user->is_admin);
}
```

**Step 6: Run the test to verify it fails**

```bash
php artisan test --compact --filter=test_admin_factory_state_sets_is_admin_true
```

Expected: FAIL — `is_admin` column or state not yet set.

**Step 7: Implement (already written in steps 2–4), run test**

```bash
php artisan test --compact --filter=test_admin_factory_state_sets_is_admin_true
```

Expected: PASS.

**Step 8: Commit**

```bash
git add app/Models/SocialAccount.php app/Models/User.php database/factories/UserFactory.php tests/Feature/AuthTest.php
git commit -m "Add SocialAccount model, is_admin flag, admin factory state"
```

---

### Task 4: EnsureIsAdmin Middleware

**Files:**
- Create: `app/Http/Middleware/EnsureIsAdmin.php`

**Step 1: Generate middleware**

```bash
php artisan make:middleware EnsureIsAdmin --no-interaction
```

**Step 2: Write middleware**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_admin) {
            abort(403);
        }

        return $next($request);
    }
}
```

**Step 3: Write failing test** (add to `AuthTest.php`)

```php
public function test_non_admin_cannot_access_admin_routes(): void
{
    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/admin/login');
    // /admin/login is public — test a protected route instead
    // We'll test the middleware directly once admin routes exist (Task 6)
    $this->assertTrue(true); // placeholder — remove after Task 6
}
```

**Step 4: Register middleware alias in `bootstrap/app.php`**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureIsAdmin::class,
    ]);
})
```

**Step 5: Commit**

```bash
git add app/Http/Middleware/EnsureIsAdmin.php bootstrap/app.php
git commit -m "Add EnsureIsAdmin middleware"
```

---

### Task 5: Admin Auth Controller + Views

**Files:**
- Create: `app/Http/Controllers/Admin/Auth/LoginController.php`
- Create: `resources/views/admin/auth/login.blade.php`

**Step 1: Create directory and controller**

```bash
php artisan make:controller Admin/Auth/LoginController --no-interaction
```

**Step 2: Write `LoginController`**

```php
<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }

        if (! Auth::user()->is_admin) {
            Auth::logout();
            return back()->withErrors([
                'email' => 'You do not have admin access.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
```

**Step 3: Create admin login view**

`resources/views/admin/auth/login.blade.php`:

```blade
@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-sm py-16">
    <h1 class="mb-8 text-2xl font-bold text-white">Admin Login</h1>

    <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm text-stone-300">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
            @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm text-stone-300">Password</label>
            <input type="password" id="password" name="password" required
                class="mt-1 w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
        </div>

        <button type="submit"
            class="w-full rounded-lg bg-amber-400 px-4 py-2 font-semibold text-stone-950 hover:bg-amber-300">
            Sign in
        </button>
    </form>
</div>
@endsection
```

**Step 4: Write failing test** (add to `AuthTest.php`)

```php
public function test_admin_can_login_via_admin_login_form(): void
{
    $admin = User::factory()->admin()->create();

    $response = $this->post(route('admin.login'), [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($admin);
}

public function test_non_admin_cannot_login_via_admin_login_form(): void
{
    $user = User::factory()->create();

    $response = $this->post(route('admin.login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
}
```

**Step 5: Run tests to verify they fail**

```bash
php artisan test --compact --filter=test_admin_can_login
```

Expected: FAIL — route `admin.login` does not exist yet.

**Step 6: Commit controller + view (routes come in Task 6)**

```bash
git add app/Http/Controllers/Admin/ resources/views/admin/
git commit -m "Add admin login controller and view"
```

---

### Task 6: Register All Routes

**Files:**
- Modify: `routes/web.php`
- Verify: `routes/auth.php` (published by Breeze — check it exists)

**Step 1: Check Breeze auth routes**

```bash
cat routes/auth.php
```

Breeze auto-registers `/login`, `/register`, `/logout`, and password reset routes.

**Step 2: Add social + admin routes to `routes/web.php`**

```php
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Auth\SocialController;

// Social auth
Route::get('/auth/{provider}', [SocialController::class, 'redirect'])
    ->name('auth.social.redirect')
    ->where('provider', 'google|github');

Route::get('/auth/{provider}/callback', [SocialController::class, 'callback'])
    ->name('auth.social.callback')
    ->where('provider', 'google|github');

// Admin auth
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('login', [AdminLoginController::class, 'store']);
    });

    Route::post('logout', [AdminLoginController::class, 'destroy'])->name('logout');

    // Protected admin routes go here in future tasks
    Route::middleware('admin')->group(function () {
        Route::get('dashboard', fn () => view('admin.dashboard'))->name('dashboard');
    });
});
```

**Step 3: Create a stub admin dashboard view** (`resources/views/admin/dashboard.blade.php`):

```blade
@extends('layouts.app')
@section('content')
<h1 class="text-2xl font-bold text-white">Admin Dashboard</h1>
@endsection
```

**Step 4: Run admin login tests**

```bash
php artisan test --compact --filter=test_admin_can_login
```

Expected: PASS both admin login tests.

**Step 5: Test non-admin blocked from admin dashboard**

Add to `AuthTest.php`:

```php
public function test_non_admin_cannot_access_admin_dashboard(): void
{
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
}

public function test_admin_can_access_admin_dashboard(): void
{
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
}
```

**Step 6: Run new tests**

```bash
php artisan test --compact --filter=AuthTest
```

Expected: All PASS.

**Step 7: Commit**

```bash
git add routes/web.php resources/views/admin/dashboard.blade.php tests/Feature/AuthTest.php
git commit -m "Register social and admin routes, add admin dashboard stub"
```

---

### Task 7: Socialite Controller

**Files:**
- Create: `app/Http/Controllers/Auth/SocialController.php`

**Step 1: Add Socialite config** to `config/services.php`:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URL', '/auth/google/callback'),
],

'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => env('GITHUB_REDIRECT_URL', '/auth/github/callback'),
],
```

**Step 2: Create controller**

```bash
php artisan make:controller Auth/SocialController --no-interaction
```

**Step 3: Write `SocialController`**

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        $socialUser = Socialite::driver($provider)->user();

        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            Auth::login($socialAccount->user, remember: true);
            return redirect()->intended('/');
        }

        $user = User::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            ['name' => $socialUser->getName(), 'password' => ''],
        );

        $user->socialAccounts()->create([
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'token' => $socialUser->token,
        ]);

        Auth::login($user, remember: true);

        return redirect()->intended('/');
    }
}
```

**Step 4: Write failing tests** (add to `AuthTest.php`)

```php
use Laravel\Socialite\Contracts\Factory as Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

public function test_new_user_can_authenticate_via_google(): void
{
    $socialiteUser = $this->createMock(SocialiteUser::class);
    $socialiteUser->method('getId')->willReturn('google-123');
    $socialiteUser->method('getEmail')->willReturn('user@example.com');
    $socialiteUser->method('getName')->willReturn('Test User');
    $socialiteUser->token = 'fake-token';

    $provider = $this->createMock(\Laravel\Socialite\Two\GoogleProvider::class);
    $provider->method('user')->willReturn($socialiteUser);

    $this->mock(Socialite::class)
        ->shouldReceive('driver')
        ->with('google')
        ->andReturn($provider);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect('/');
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'user@example.com']);
    $this->assertDatabaseHas('social_accounts', ['provider' => 'google', 'provider_id' => 'google-123']);
}

public function test_existing_social_account_logs_in_linked_user(): void
{
    $user = User::factory()->create(['email' => 'existing@example.com']);
    $user->socialAccounts()->create([
        'provider' => 'github',
        'provider_id' => 'gh-456',
        'token' => 'old-token',
    ]);

    $socialiteUser = $this->createMock(SocialiteUser::class);
    $socialiteUser->method('getId')->willReturn('gh-456');
    $socialiteUser->method('getEmail')->willReturn('existing@example.com');
    $socialiteUser->method('getName')->willReturn($user->name);
    $socialiteUser->token = 'new-token';

    $provider = $this->createMock(\Laravel\Socialite\Two\GithubProvider::class);
    $provider->method('user')->willReturn($socialiteUser);

    $this->mock(Socialite::class)
        ->shouldReceive('driver')
        ->with('github')
        ->andReturn($provider);

    $this->get('/auth/github/callback');

    $this->assertAuthenticatedAs($user);
}
```

**Step 5: Run tests to verify they fail**

```bash
php artisan test --compact --filter=test_new_user_can_authenticate_via_google
```

Expected: FAIL — `SocialController` not yet wired.

**Step 6: Run all AuthTests after implementation**

```bash
php artisan test --compact --filter=AuthTest
```

Expected: All PASS.

**Step 7: Commit**

```bash
git add app/Http/Controllers/Auth/SocialController.php config/services.php tests/Feature/AuthTest.php
git commit -m "Add Socialite controller for Google/GitHub OAuth"
```

---

### Task 8: Admin Seeder

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`

**Step 1: Update `DatabaseSeeder`**

```php
public function run(): void
{
    User::factory()->admin()->create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
    ]);
}
```

Import `Hash` at top: `use Illuminate\Support\Facades\Hash;`

**Step 2: Run seeder**

```bash
php artisan db:seed --no-interaction
```

**Step 3: Commit**

```bash
git add database/seeders/DatabaseSeeder.php
git commit -m "Seed admin user"
```

---

### Task 9: Add Social Login Buttons to Reader Login View

**Files:**
- Modify: `resources/views/auth/login.blade.php` (Breeze-published)

**Step 1: Find where the login form ends, add social buttons after the submit button**

```blade
<div class="mt-6">
    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-white/10"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="bg-stone-950 px-2 text-stone-400">Or continue with</span>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-2 gap-3">
        <a href="{{ route('auth.social.redirect', 'google') }}"
            class="flex items-center justify-center rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm text-white hover:bg-white/10">
            Google
        </a>
        <a href="{{ route('auth.social.redirect', 'github') }}"
            class="flex items-center justify-center rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm text-white hover:bg-white/10">
            GitHub
        </a>
    </div>
</div>
```

**Step 2: Run full test suite**

```bash
php artisan test --compact
```

Expected: All PASS. Fix any failures before proceeding.

**Step 3: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 4: Commit**

```bash
git add resources/views/auth/login.blade.php
git commit -m "Add Google and GitHub social login buttons to login view"
```

---

### Task 10: Final Verification

**Step 1: Run full test suite**

```bash
php artisan test --compact
```

Expected: All PASS.

**Step 2: Check routes registered correctly**

```bash
php artisan route:list --except-vendor
```

Verify: `/login`, `/register`, `/logout`, `/auth/{provider}`, `/auth/{provider}/callback`, `/admin/login`, `/admin/dashboard` all listed.

**Step 3: Run Pint on all modified files**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 4: Final commit if any Pint fixes**

```bash
git add -p
git commit -m "Apply Pint formatting"
```
