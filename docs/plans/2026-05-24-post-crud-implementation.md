# Post CRUD Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add writer draft management page, admin post moderation panel (hide/delete any post), and `hidden_at` visibility system.

**Architecture:** Add `hidden_at` timestamp to posts; update `scopePublished` to filter it; add `WriterPostController::index` for draft list; add `Admin\PostController` and `Admin\PostVisibilityController` for admin moderation. Admin posts as themselves via existing writer routes.

**Tech Stack:** Laravel 13, PHP 8.4, Blade, Tailwind CSS v4, PHPUnit 12

---

### Task 1: Migration — add `hidden_at` to posts

**Files:**
- Create: `database/migrations/2026_05_24_XXXXXX_add_hidden_at_to_posts_table.php`

**Step 1: Generate migration**

```bash
php artisan make:migration add_hidden_at_to_posts_table --no-interaction
```

**Step 2: Fill the migration**

Open the generated file. Replace the `up()` and `down()` bodies:

```php
public function up(): void
{
    Schema::table('posts', function (Blueprint $table): void {
        $table->timestamp('hidden_at')->nullable()->after('published_at');
    });
}

public function down(): void
{
    Schema::table('posts', function (Blueprint $table): void {
        $table->dropColumn('hidden_at');
    });
}
```

**Step 3: Run migration**

```bash
php artisan migrate --no-interaction
```

Expected: migrated successfully, no errors.

**Step 4: Commit**

```bash
git add database/migrations/
git commit -m "feat: add hidden_at column to posts table"
```

---

### Task 2: Update Post model

**Files:**
- Modify: `app/Models/Post.php`

**Step 1: Write failing test**

Add to `tests/Feature/WriterPostTest.php`:

```php
public function test_hidden_post_does_not_appear_in_published_scope(): void
{
    $post = Post::factory()->published()->create(['hidden_at' => now()]);

    $results = Post::query()->published()->get();

    $this->assertNotContains($post->id, $results->pluck('id')->all());
}
```

**Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=test_hidden_post_does_not_appear_in_published_scope
```

Expected: FAIL — hidden post still appears.

**Step 3: Update Post model**

In `app/Models/Post.php`, add `hidden_at` to `casts()`:

```php
protected function casts(): array
{
    return [
        'published_at' => 'datetime',
        'hidden_at' => 'datetime',
        'is_featured' => 'boolean',
    ];
}
```

Update `scopePublished` to also filter hidden:

```php
public function scopePublished(Builder $query): Builder
{
    return $query
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->whereNull('hidden_at');
}
```

Add `scopeHidden`:

```php
public function scopeHidden(Builder $query): Builder
{
    return $query->whereNotNull('hidden_at');
}
```

Also add `hidden_at` to `$fillable`:

```php
protected $fillable = [
    'user_id',
    'title',
    'slug',
    'excerpt',
    'body',
    'published_at',
    'hidden_at',
    'is_featured',
];
```

**Step 4: Run test to verify it passes**

```bash
php artisan test --compact --filter=test_hidden_post_does_not_appear_in_published_scope
```

Expected: PASS.

**Step 5: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 6: Commit**

```bash
git add app/Models/Post.php tests/Feature/WriterPostTest.php
git commit -m "feat: add hidden_at cast, scopeHidden, update scopePublished"
```

---

### Task 3: Update PostPolicy

**Files:**
- Modify: `app/Policies/PostPolicy.php`

**Step 1: Write failing test**

Add to `tests/Feature/Admin/PostCurationTest.php`:

```php
public function test_admin_can_delete_any_post(): void
{
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($admin)
        ->delete(route('writer.posts.destroy', $post))
        ->assertRedirect();

    $this->assertModelMissing($post);
}

public function test_regular_user_cannot_delete_others_post(): void
{
    $user = User::factory()->create();
    $post = Post::factory()->for(User::factory()->create())->published()->create();

    $this->actingAs($user)
        ->delete(route('writer.posts.destroy', $post))
        ->assertForbidden();
}
```

**Step 2: Run tests to verify they fail**

```bash
php artisan test --compact --filter=test_admin_can_delete_any_post
```

Expected: FAIL — admin gets 403.

**Step 3: Update PostPolicy**

In `app/Policies/PostPolicy.php`, update `delete()` and `update()`, add `hide()`:

```php
public function update(User $user, Post $post): bool
{
    return $user->is_admin || $user->is($post->author);
}

public function delete(User $user, Post $post): bool
{
    return $user->is_admin || $user->is($post->author);
}

public function hide(User $user, Post $post): bool
{
    return $user->is_admin;
}
```

**Step 4: Run tests to verify they pass**

```bash
php artisan test --compact --filter=test_admin_can_delete_any_post
php artisan test --compact --filter=test_regular_user_cannot_delete_others_post
```

Expected: both PASS.

**Step 5: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 6: Commit**

```bash
git add app/Policies/PostPolicy.php tests/Feature/Admin/PostCurationTest.php
git commit -m "feat: allow admin to delete/update any post, add hide policy"
```

---

### Task 4: Add `hidden` factory state to PostFactory

**Files:**
- Modify: `database/factories/PostFactory.php`

**Step 1: Add hidden state**

```php
public function hidden(): static
{
    return $this->state(fn (array $attributes) => [
        'published_at' => now()->subDay(),
        'hidden_at' => now(),
    ]);
}
```

**Step 2: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 3: Commit**

```bash
git add database/factories/PostFactory.php
git commit -m "feat: add hidden factory state to PostFactory"
```

---

### Task 5: Writer draft management — index route and controller method

**Files:**
- Modify: `app/Http/Controllers/WriterPostController.php`
- Modify: `routes/web.php`

**Step 1: Write failing test**

Create `tests/Feature/WriterDraftListTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriterDraftListTest extends TestCase
{
    use RefreshDatabase;

    public function test_writer_can_view_their_own_posts(): void
    {
        $user = User::factory()->create();
        $draft = Post::factory()->for($user)->create(['published_at' => null]);
        $published = Post::factory()->for($user)->published()->create();
        $other = Post::factory()->published()->create();

        $response = $this->actingAs($user)->get(route('writer.posts.index'));

        $response->assertOk();
        $response->assertSee($draft->title);
        $response->assertSee($published->title);
        $response->assertDontSee($other->title);
    }

    public function test_guest_cannot_view_writer_post_list(): void
    {
        $this->get(route('writer.posts.index'))
            ->assertRedirect(route('login'));
    }
}
```

**Step 2: Run test to verify it fails**

```bash
php artisan test --compact tests/Feature/WriterDraftListTest.php
```

Expected: FAIL — route not found.

**Step 3: Add route**

In `routes/web.php`, inside the `writer` prefix group, add before the existing `posts/create` route:

```php
Route::get('posts', [WriterPostController::class, 'index'])->name('posts.index');
```

**Step 4: Add controller method**

In `app/Http/Controllers/WriterPostController.php`, add:

```php
public function index(): View
{
    $posts = auth()->user()->posts()->latest()->get();

    return view('writer.posts.index', compact('posts'));
}
```

Add `use Illuminate\View\View;` if not already imported.

**Step 5: Run test to verify it passes (view will 404 until Task 6)**

Skip running until view is created in Task 6.

**Step 6: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 7: Commit**

```bash
git add app/Http/Controllers/WriterPostController.php routes/web.php
git commit -m "feat: add writer posts index route and controller method"
```

---

### Task 6: Writer draft management — view

**Files:**
- Create: `resources/views/writer/posts/index.blade.php`

**Step 1: Create view**

```blade
<x-app-layout>
    <div class="mx-auto max-w-3xl">
        <div class="mb-8 flex items-center justify-between">
            <h1 class="font-serif text-3xl font-semibold">My stories</h1>
            <a href="{{ route('writer.posts.create') }}" class="rounded-md bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-700">New story</a>
        </div>

        @if ($posts->isEmpty())
            <p class="text-stone-500">No stories yet. <a href="{{ route('writer.posts.create') }}" class="underline">Write one.</a></p>
        @else
            <div class="divide-y divide-stone-200">
                @foreach ($posts as $post)
                    <div class="flex items-center justify-between py-4">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium text-stone-900">{{ $post->title }}</p>
                            <div class="mt-1 flex items-center gap-3 text-sm text-stone-500">
                                @if ($post->hidden_at)
                                    <span class="rounded bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Hidden</span>
                                @elseif ($post->published_at)
                                    <span class="rounded bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Published</span>
                                    <span>{{ $post->published_at->format('d M Y') }}</span>
                                @else
                                    <span class="rounded bg-stone-100 px-2 py-0.5 text-xs font-medium text-stone-600">Draft</span>
                                @endif
                            </div>
                        </div>
                        <div class="ml-4 flex shrink-0 gap-3">
                            <a href="{{ route('writer.posts.edit', $post) }}" class="text-sm text-stone-600 hover:text-stone-900">Edit</a>
                            <form method="POST" action="{{ route('writer.posts.destroy', $post) }}" onsubmit="return confirm('Delete this story?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-stone-400 hover:text-red-600">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
```

**Step 2: Run WriterDraftListTest**

```bash
php artisan test --compact tests/Feature/WriterDraftListTest.php
```

Expected: all PASS.

**Step 3: Commit**

```bash
git add resources/views/writer/posts/index.blade.php tests/Feature/WriterDraftListTest.php
git commit -m "feat: add writer draft management page"
```

---

### Task 7: Admin post list — controller

**Files:**
- Create: `app/Http/Controllers/Admin/PostController.php`

**Step 1: Write failing test**

Create `tests/Feature/Admin/PostModerationTest.php`:

```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_posts(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($admin)
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertSee($post->title);
    }

    public function test_regular_user_cannot_view_admin_post_list(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.posts.index'))
            ->assertForbidden();
    }

    public function test_admin_can_delete_any_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($admin)
            ->delete(route('admin.posts.destroy', $post))
            ->assertRedirect();

        $this->assertModelMissing($post);
    }
}
```

**Step 2: Run test to verify it fails**

```bash
php artisan test --compact tests/Feature/Admin/PostModerationTest.php
```

Expected: FAIL — routes not found.

**Step 3: Generate controller**

```bash
php artisan make:controller Admin/PostController --no-interaction
```

**Step 4: Fill controller**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->with('author')
            ->latest()
            ->paginate(25);

        return view('admin.posts.index', compact('posts'));
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.posts.index');
    }
}
```

**Step 5: Add routes**

In `routes/web.php`, inside the `admin` middleware group (where `PostCurationController::feature` lives), add:

```php
Route::get('posts', [PostController::class, 'index'])->name('posts.index');
Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
```

Add import at top:

```php
use App\Http\Controllers\Admin\PostController as AdminPostController;
```

**Step 6: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 7: Commit**

```bash
git add app/Http/Controllers/Admin/PostController.php routes/web.php
git commit -m "feat: add admin post list and delete controller + routes"
```

---

### Task 8: Admin post visibility — controller

**Files:**
- Create: `app/Http/Controllers/Admin/PostVisibilityController.php`

**Step 1: Write failing test**

Add to `tests/Feature/Admin/PostModerationTest.php`:

```php
public function test_admin_can_hide_a_post(): void
{
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($admin)
        ->patch(route('admin.posts.visibility', $post))
        ->assertRedirect();

    $this->assertNotNull($post->fresh()->hidden_at);
}

public function test_admin_can_unhide_a_post(): void
{
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->hidden()->create();

    $this->actingAs($admin)
        ->patch(route('admin.posts.visibility', $post))
        ->assertRedirect();

    $this->assertNull($post->fresh()->hidden_at);
}

public function test_regular_user_cannot_hide_post(): void
{
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($user)
        ->patch(route('admin.posts.visibility', $post))
        ->assertForbidden();
}
```

**Step 2: Run tests to verify they fail**

```bash
php artisan test --compact --filter=test_admin_can_hide_a_post
```

Expected: FAIL — route not found.

**Step 3: Generate controller**

```bash
php artisan make:controller Admin/PostVisibilityController --no-interaction
```

**Step 4: Fill controller**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class PostVisibilityController extends Controller
{
    public function toggle(Post $post): RedirectResponse
    {
        Gate::authorize('hide', $post);

        $post->update([
            'hidden_at' => $post->hidden_at ? null : now(),
        ]);

        return back();
    }
}
```

**Step 5: Add route**

In `routes/web.php`, admin middleware group, add:

```php
Route::patch('posts/{post}/visibility', [PostVisibilityController::class, 'toggle'])->name('posts.visibility');
```

Add import:

```php
use App\Http\Controllers\Admin\PostVisibilityController;
```

**Step 6: Run tests**

```bash
php artisan test --compact tests/Feature/Admin/PostModerationTest.php
```

Expected: all PASS.

**Step 7: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 8: Commit**

```bash
git add app/Http/Controllers/Admin/PostVisibilityController.php routes/web.php tests/Feature/Admin/PostModerationTest.php
git commit -m "feat: add admin post visibility toggle"
```

---

### Task 9: Admin post list — view

**Files:**
- Create: `resources/views/admin/posts/index.blade.php`

**Step 1: Create view**

```blade
@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-5xl">
        <h1 class="mb-8 text-2xl font-bold text-white">All posts</h1>

        <div class="overflow-hidden rounded-lg border border-stone-700">
            <table class="w-full text-sm text-left text-stone-300">
                <thead class="bg-stone-800 text-xs uppercase text-stone-400">
                    <tr>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Author</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-700 bg-stone-900">
                    @forelse ($posts as $post)
                        <tr>
                            <td class="px-4 py-3 font-medium text-white">{{ $post->title }}</td>
                            <td class="px-4 py-3">{{ $post->author->name }}</td>
                            <td class="px-4 py-3">
                                @if ($post->hidden_at)
                                    <span class="rounded bg-red-900 px-2 py-0.5 text-xs font-medium text-red-300">Hidden</span>
                                @elseif ($post->published_at)
                                    <span class="rounded bg-green-900 px-2 py-0.5 text-xs font-medium text-green-300">Published</span>
                                @else
                                    <span class="rounded bg-stone-700 px-2 py-0.5 text-xs font-medium text-stone-300">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <form method="POST" action="{{ route('admin.posts.visibility', $post) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs text-stone-400 hover:text-white">
                                            {{ $post->hidden_at ? 'Unhide' : 'Hide' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Delete this post permanently?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-400">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-stone-500">No posts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $posts->links() }}
        </div>
    </div>
@endsection
```

**Step 2: Run all admin tests**

```bash
php artisan test --compact tests/Feature/Admin/
```

Expected: all PASS.

**Step 3: Commit**

```bash
git add resources/views/admin/posts/
git commit -m "feat: add admin posts moderation view"
```

---

### Task 10: Run full test suite

**Step 1: Run all tests**

```bash
php artisan test --compact
```

Expected: all PASS, no regressions.

**Step 2: If any failures, fix before proceeding.**

---

### Task 11: Final pint pass

```bash
vendor/bin/pint --dirty --format agent
```

Commit any formatting fixes:

```bash
git add -p
git commit -m "style: pint formatting"
```
