# Nukilan Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Transform the existing Laravel blog into Nukilan — a Malaysian/SEA writing platform with user-authored posts, writer profiles, follow system, threaded comments, reading circles, meaningful reactions, admin curation, and writer tips.

**Architecture:** Laravel 13 MVC with Blade views. Each feature is a self-contained migration + model + controller + views slice. UI is light/minimal/editorial — white base, serif body font, single muted accent. TDD throughout.

**Tech Stack:** PHP 8.4, Laravel 13, Tailwind CSS v4, Alpine.js (already available via Breeze), Stripe (tips — added last), PHPUnit 12.

---

## Phase 1: Foundation

### Task 1: Add user_id to posts + wire writer relationship

**Files:**
- Create: `database/migrations/2026_05_24_000001_add_user_id_to_posts_table.php`
- Modify: `app/Models/Post.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/PostAuthorshipTest.php`

**Step 1: Create migration**

```bash
php artisan make:migration add_user_id_to_posts_table --no-interaction
```

Edit the generated file:

```php
public function up(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('id');
    });
}

public function down(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->dropForeignIdFor(\App\Models\User::class);
    });
}
```

**Step 2: Run migration**

```bash
php artisan migrate --no-interaction
```

**Step 3: Write failing test**

```php
// tests/Feature/PostAuthorshipTest.php
<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostAuthorshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this->assertInstanceOf(User::class, $post->author);
        $this->assertEquals($user->id, $post->author->id);
    }

    public function test_user_has_many_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->for($user)->create();

        $this->assertCount(3, $user->posts);
    }
}
```

**Step 4: Run test — expect FAIL**

```bash
php artisan test --compact --filter=PostAuthorshipTest
```

**Step 5: Update Post model**

```php
// app/Models/Post.php — add import + relationship
use Illuminate\Database\Eloquent\Relations\BelongsTo;

public function author(): BelongsTo
{
    return $this->belongsTo(User::class, 'user_id');
}
```

**Step 6: Update User model**

```php
// app/Models/User.php — add import + relationship
use Illuminate\Database\Eloquent\Relations\HasMany;

public function posts(): HasMany
{
    return $this->hasMany(Post::class);
}
```

**Step 7: Update PostFactory to include user_id**

```php
// database/factories/PostFactory.php
// Add to definition():
'user_id' => User::factory(),
```

**Step 8: Run test — expect PASS**

```bash
php artisan test --compact --filter=PostAuthorshipTest
```

**Step 9: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: add user_id to posts, wire author relationship"
```

---

### Task 2: Redesign UI — light minimal editorial layout

**Files:**
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/views/layouts/guest.blade.php`
- Modify: `resources/views/layouts/navigation.blade.php`
- Modify: `resources/views/posts/index.blade.php`
- Modify: `resources/views/posts/show.blade.php`
- Modify: `resources/css/app.css` (or equivalent Tailwind entry)

**Goal:** White/off-white base, serif body font (use Google Fonts — Lora or Merriweather), minimal nav, clean post cards, no dark stone.

**Step 1: Add serif font**

In `resources/views/layouts/app.blade.php` `<head>`:

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400&family=Inter:wght@400;500&display=swap" rel="stylesheet">
```

**Step 2: Tailwind config — add font families**

In `tailwind.config.js`:

```js
theme: {
    extend: {
        fontFamily: {
            serif: ['Lora', 'Georgia', 'serif'],
            sans: ['Inter', 'ui-sans-serif', 'system-ui'],
        },
        colors: {
            accent: {
                DEFAULT: '#8B7355', // warm sand
                light: '#C4A882',
            },
        },
    },
},
```

**Step 3: Rebuild app layout — minimal nav**

Replace `resources/views/layouts/app.blade.php` body structure:

```html
<body class="bg-white text-stone-900 font-sans antialiased">
    <header class="border-b border-stone-100 py-4 px-6">
        <nav class="max-w-3xl mx-auto flex items-center justify-between">
            <a href="{{ route('posts.index') }}" class="text-xl font-serif font-semibold tracking-tight">Nukilan</a>
            <div class="flex items-center gap-6 text-sm">
                @auth
                    <a href="{{ route('posts.create') }}" class="text-accent hover:text-accent-light font-medium">Write</a>
                    <a href="{{ route('profile.edit') }}" class="text-stone-600 hover:text-stone-900">{{ Auth::user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-stone-400 hover:text-stone-600">Sign out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-stone-600 hover:text-stone-900">Sign in</a>
                    <a href="{{ route('register') }}" class="bg-stone-900 text-white px-4 py-1.5 rounded-full text-sm hover:bg-stone-700">Get started</a>
                @endauth
            </div>
        </nav>
    </header>
    <main class="max-w-3xl mx-auto px-6 py-10">
        {{ $slot }}
    </main>
</body>
```

**Step 4: Post index — clean card list**

```html
{{-- resources/views/posts/index.blade.php --}}
<x-app-layout>
    <div class="space-y-10">
        @foreach ($posts as $post)
        <article class="border-b border-stone-100 pb-10">
            @if ($post->author)
            <div class="flex items-center gap-2 mb-3">
                <span class="text-sm font-medium text-stone-700">{{ $post->author->name }}</span>
                <span class="text-stone-300">·</span>
                <span class="text-sm text-stone-400">{{ $post->published_at->format('d M Y') }}</span>
            </div>
            @endif
            <h2 class="text-2xl font-serif font-semibold leading-snug mb-2">
                <a href="{{ route('posts.show', $post) }}" class="hover:text-accent">{{ $post->title }}</a>
            </h2>
            <p class="text-stone-600 leading-relaxed mb-4">{{ $post->excerpt }}</p>
            <a href="{{ route('posts.show', $post) }}" class="text-sm text-accent hover:underline">Read more →</a>
        </article>
        @endforeach
    </div>
</x-app-layout>
```

**Step 5: Post show — reader mode**

```html
{{-- resources/views/posts/show.blade.php --}}
<x-app-layout>
    <article class="max-w-2xl mx-auto">
        <header class="mb-8">
            <h1 class="text-4xl font-serif font-semibold leading-tight mb-4">{{ $post->title }}</h1>
            @if ($post->author)
            <div class="flex items-center gap-3 text-sm text-stone-500">
                <span class="font-medium text-stone-700">{{ $post->author->name }}</span>
                <span>·</span>
                <span>{{ $post->published_at->format('d M Y') }}</span>
                <span>·</span>
                <span>{{ $post->reading_time }} min read</span>
            </div>
            @endif
        </header>
        <div class="prose prose-stone prose-lg font-serif max-w-none">
            {!! $post->body !!}
        </div>
    </article>
</x-app-layout>
```

**Step 6: Add reading_time accessor to Post model**

```php
// app/Models/Post.php
public function getReadingTimeAttribute(): int
{
    return max(1, (int) ceil(str_word_count(strip_tags($this->body)) / 200));
}
```

**Step 7: Build assets**

```bash
npm run build
```

**Step 8: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: redesign UI — light minimal editorial layout with Nukilan branding"
```

---

## Phase 2: Writer Features

### Task 3: User-authored posts — create/edit/delete

**Files:**
- Create: `app/Http/Controllers/WriterPostController.php`
- Create: `resources/views/writer/posts/create.blade.php`
- Create: `resources/views/writer/posts/edit.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/WriterPostTest.php`

**Step 1: Write failing tests**

```php
// tests/Feature/WriterPostTest.php
<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WriterPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('writer.posts.store'), [
            'title' => 'My First Post',
            'excerpt' => 'A short excerpt.',
            'body' => 'The full body content here.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', ['title' => 'My First Post', 'user_id' => $user->id]);
    }

    public function test_guest_cannot_create_post(): void
    {
        $this->post(route('writer.posts.store'), ['title' => 'Hack'])->assertRedirect(route('login'));
    }

    public function test_user_can_publish_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create(['published_at' => null]);

        $this->actingAs($user)->patch(route('writer.posts.publish', $post));

        $this->assertNotNull($post->fresh()->published_at);
    }

    public function test_user_cannot_edit_others_post(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $post = Post::factory()->for($owner)->create();

        $this->actingAs($other)
            ->patch(route('writer.posts.update', $post), ['title' => 'Hijacked'])
            ->assertForbidden();
    }

    public function test_user_can_delete_own_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->for($user)->create();

        $this->actingAs($user)->delete(route('writer.posts.destroy', $post))->assertRedirect();
        $this->assertModelMissing($post);
    }
}
```

**Step 2: Run — expect FAIL**

```bash
php artisan test --compact --filter=WriterPostTest
```

**Step 3: Create controller**

```bash
php artisan make:controller WriterPostController --no-interaction
```

```php
// app/Http/Controllers/WriterPostController.php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WriterPostController extends Controller
{
    public function create(): View
    {
        return view('writer.posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:500'],
            'body'    => ['required', 'string'],
        ]);

        $post = $request->user()->posts()->create([
            ...$validated,
            'slug' => Str::slug($validated['title']) . '-' . Str::random(6),
        ]);

        return redirect()->route('posts.show', $post);
    }

    public function edit(Post $post): View
    {
        $this->authorize('update', $post);

        return view('writer.posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title'   => ['required', 'string', 'max:255'],
            'excerpt' => ['required', 'string', 'max:500'],
            'body'    => ['required', 'string'],
        ]);

        $post->update($validated);

        return redirect()->route('posts.show', $post);
    }

    public function publish(Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $post->update(['published_at' => now()]);

        return redirect()->route('posts.show', $post);
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index');
    }
}
```

**Step 4: Create PostPolicy**

```bash
php artisan make:policy PostPolicy --model=Post --no-interaction
```

```php
// app/Policies/PostPolicy.php
public function update(User $user, Post $post): bool
{
    return $user->id === $post->user_id;
}

public function delete(User $user, Post $post): bool
{
    return $user->id === $post->user_id;
}
```

**Step 5: Register routes**

```php
// routes/web.php — inside auth middleware group
Route::prefix('writer')->name('writer.')->group(function () {
    Route::get('posts/create', [WriterPostController::class, 'create'])->name('posts.create');
    Route::post('posts', [WriterPostController::class, 'store'])->name('posts.store');
    Route::get('posts/{post}/edit', [WriterPostController::class, 'edit'])->name('posts.edit');
    Route::patch('posts/{post}', [WriterPostController::class, 'update'])->name('posts.update');
    Route::patch('posts/{post}/publish', [WriterPostController::class, 'publish'])->name('posts.publish');
    Route::delete('posts/{post}', [WriterPostController::class, 'destroy'])->name('posts.destroy');
});
```

**Step 6: Create views**

```html
{{-- resources/views/writer/posts/create.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-serif font-semibold mb-8">New story</h1>
        <form method="POST" action="{{ route('writer.posts.store') }}" class="space-y-6">
            @csrf
            <div>
                <input name="title" type="text" placeholder="Title"
                    class="w-full text-2xl font-serif border-0 border-b border-stone-200 focus:ring-0 focus:border-accent pb-2 outline-none"
                    value="{{ old('title') }}">
                @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <input name="excerpt" type="text" placeholder="Write a short excerpt..."
                    class="w-full text-stone-500 border-0 border-b border-stone-200 focus:ring-0 focus:border-accent pb-2 outline-none"
                    value="{{ old('excerpt') }}">
                @error('excerpt') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <textarea name="body" rows="20" placeholder="Tell your story..."
                    class="w-full font-serif text-lg border-0 focus:ring-0 outline-none resize-none leading-relaxed">{{ old('body') }}</textarea>
                @error('body') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-4 pt-4 border-t border-stone-100">
                <button type="submit" class="bg-stone-900 text-white px-6 py-2 rounded-full text-sm hover:bg-stone-700">Save draft</button>
                <a href="{{ route('posts.index') }}" class="text-stone-400 text-sm self-center hover:text-stone-600">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
```

```html
{{-- resources/views/writer/posts/edit.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-serif font-semibold mb-8">Edit story</h1>
        <form method="POST" action="{{ route('writer.posts.update', $post) }}" class="space-y-6">
            @csrf @method('PATCH')
            <input name="title" type="text" value="{{ old('title', $post->title) }}"
                class="w-full text-2xl font-serif border-0 border-b border-stone-200 focus:ring-0 focus:border-accent pb-2 outline-none">
            <input name="excerpt" type="text" value="{{ old('excerpt', $post->excerpt) }}"
                class="w-full text-stone-500 border-0 border-b border-stone-200 focus:ring-0 pb-2 outline-none">
            <textarea name="body" rows="20"
                class="w-full font-serif text-lg border-0 focus:ring-0 outline-none resize-none leading-relaxed">{{ old('body', $post->body) }}</textarea>
            <div class="flex gap-4 pt-4 border-t border-stone-100">
                <button type="submit" class="bg-stone-900 text-white px-6 py-2 rounded-full text-sm hover:bg-stone-700">Save</button>
                @if (!$post->published_at)
                <form method="POST" action="{{ route('writer.posts.publish', $post) }}">
                    @csrf @method('PATCH')
                    <button class="bg-accent text-white px-6 py-2 rounded-full text-sm hover:opacity-90">Publish</button>
                </form>
                @endif
            </div>
        </form>
    </div>
</x-app-layout>
```

**Step 7: Run tests — expect PASS**

```bash
php artisan test --compact --filter=WriterPostTest
```

**Step 8: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: writer can create, edit, publish, and delete posts"
```

---

### Task 4: Writer profiles

**Files:**
- Create: `app/Http/Controllers/WriterProfileController.php`
- Create: `resources/views/writers/show.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/WriterProfileTest.php`

**Step 1: Write failing test**

```php
// tests/Feature/WriterProfileTest.php
<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WriterProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_writer_profile_shows_published_posts(): void
    {
        $writer = User::factory()->create();
        $published = Post::factory()->for($writer)->published()->create();
        $draft = Post::factory()->for($writer)->create(['published_at' => null]);

        $response = $this->get(route('writers.show', $writer));

        $response->assertOk();
        $response->assertSee($published->title);
        $response->assertDontSee($draft->title);
    }
}
```

**Step 2: Add published factory state to PostFactory**

```php
// database/factories/PostFactory.php
public function published(): static
{
    return $this->state(['published_at' => now()->subDay()]);
}
```

**Step 3: Run — expect FAIL**

```bash
php artisan test --compact --filter=WriterProfileTest
```

**Step 4: Create controller**

```bash
php artisan make:controller WriterProfileController --no-interaction
```

```php
// app/Http/Controllers/WriterProfileController.php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class WriterProfileController extends Controller
{
    public function show(User $writer): View
    {
        $posts = $writer->posts()->published()->latest('published_at')->get();

        return view('writers.show', compact('writer', 'posts'));
    }
}
```

**Step 5: Add route**

```php
// routes/web.php — public routes
Route::get('/writers/{writer}', [WriterProfileController::class, 'show'])->name('writers.show');
```

**Step 6: Create view**

```html
{{-- resources/views/writers/show.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <div class="mb-10 pb-10 border-b border-stone-100">
            <h1 class="text-3xl font-serif font-semibold mb-1">{{ $writer->name }}</h1>
            <p class="text-stone-500 text-sm">{{ $posts->count() }} {{ Str::plural('story', $posts->count()) }}</p>
        </div>
        <div class="space-y-8">
            @forelse ($posts as $post)
            <article class="border-b border-stone-100 pb-8">
                <h2 class="text-xl font-serif font-semibold mb-1">
                    <a href="{{ route('posts.show', $post) }}" class="hover:text-accent">{{ $post->title }}</a>
                </h2>
                <p class="text-stone-500 text-sm mb-2">{{ $post->excerpt }}</p>
                <span class="text-xs text-stone-400">{{ $post->published_at->format('d M Y') }}</span>
            </article>
            @empty
            <p class="text-stone-400">No stories yet.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
```

**Step 7: Run — expect PASS**

```bash
php artisan test --compact --filter=WriterProfileTest
```

**Step 8: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: writer profile page with published posts"
```

---

## Phase 3: Community

### Task 5: Follow system

**Files:**
- Create: `database/migrations/2026_05_24_000002_create_follows_table.php`
- Create: `app/Models/Follow.php`
- Create: `app/Http/Controllers/FollowController.php`
- Modify: `app/Models/User.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/FollowTest.php`

**Step 1: Create migration**

```bash
php artisan make:migration create_follows_table --no-interaction
```

```php
public function up(): void
{
    Schema::create('follows', function (Blueprint $table) {
        $table->id();
        $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('following_id')->constrained('users')->cascadeOnDelete();
        $table->timestamp('created_at');
        $table->unique(['follower_id', 'following_id']);
    });
}
```

**Step 2: Write failing test**

```php
// tests/Feature/FollowTest.php
<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_follow_another_user(): void
    {
        $follower = User::factory()->create();
        $writer = User::factory()->create();

        $this->actingAs($follower)->post(route('writers.follow', $writer));

        $this->assertTrue($follower->fresh()->isFollowing($writer));
    }

    public function test_user_can_unfollow(): void
    {
        $follower = User::factory()->create();
        $writer = User::factory()->create();
        $follower->following()->attach($writer->id);

        $this->actingAs($follower)->delete(route('writers.unfollow', $writer));

        $this->assertFalse($follower->fresh()->isFollowing($writer));
    }

    public function test_cannot_follow_self(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('writers.follow', $user))->assertForbidden();
    }
}
```

**Step 3: Run — expect FAIL**

```bash
php artisan test --compact --filter=FollowTest
```

**Step 4: Add relationships to User model**

```php
// app/Models/User.php
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

public function following(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
                ->withTimestamps();
}

public function followers(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
                ->withTimestamps();
}

public function isFollowing(User $user): bool
{
    return $this->following()->where('following_id', $user->id)->exists();
}
```

**Step 5: Create FollowController**

```bash
php artisan make:controller FollowController --no-interaction
```

```php
// app/Http/Controllers/FollowController.php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(Request $request, User $writer): RedirectResponse
    {
        abort_if($request->user()->id === $writer->id, 403);

        $request->user()->following()->syncWithoutDetaching([$writer->id]);

        return back();
    }

    public function unfollow(Request $request, User $writer): RedirectResponse
    {
        $request->user()->following()->detach($writer->id);

        return back();
    }
}
```

**Step 6: Add routes**

```php
// routes/web.php — inside auth middleware group
Route::post('/writers/{writer}/follow', [FollowController::class, 'follow'])->name('writers.follow');
Route::delete('/writers/{writer}/unfollow', [FollowController::class, 'unfollow'])->name('writers.unfollow');
```

**Step 7: Run — expect PASS**

```bash
php artisan test --compact --filter=FollowTest
```

**Step 8: Add follow button to writer profile view**

```html
{{-- In resources/views/writers/show.blade.php, after writer name --}}
@auth
    @if (auth()->id() !== $writer->id)
        @if (auth()->user()->isFollowing($writer))
            <form method="POST" action="{{ route('writers.unfollow', $writer) }}">
                @csrf @method('DELETE')
                <button class="text-sm text-stone-400 border border-stone-200 px-4 py-1 rounded-full hover:border-stone-400">Following</button>
            </form>
        @else
            <form method="POST" action="{{ route('writers.follow', $writer) }}">
                @csrf
                <button class="text-sm bg-stone-900 text-white px-4 py-1 rounded-full hover:bg-stone-700">Follow</button>
            </form>
        @endif
    @endif
@endauth
```

**Step 9: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: follow/unfollow writers"
```

---

### Task 6: Threaded comments

**Files:**
- Create: `database/migrations/2026_05_24_000003_create_comments_table.php`
- Create: `app/Models/Comment.php`
- Create: `app/Http/Controllers/CommentController.php`
- Modify: `resources/views/posts/show.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/CommentTest.php`

**Step 1: Create migration**

```bash
php artisan make:migration create_comments_table --no-interaction
```

```php
public function up(): void
{
    Schema::create('comments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
        $table->text('body');
        $table->timestamps();
    });
}
```

**Step 2: Write failing test**

```php
// tests/Feature/CommentTest.php
<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_comment_on_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($user)->post(route('posts.comments.store', $post), [
            'body' => 'Great read!',
        ]);

        $this->assertDatabaseHas('comments', ['body' => 'Great read!', 'post_id' => $post->id]);
    }

    public function test_user_can_reply_to_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();
        $parent = Comment::factory()->for($post)->for($user)->create();

        $this->actingAs($user)->post(route('posts.comments.store', $post), [
            'body' => 'I agree!',
            'parent_id' => $parent->id,
        ]);

        $this->assertDatabaseHas('comments', ['body' => 'I agree!', 'parent_id' => $parent->id]);
    }

    public function test_guest_cannot_comment(): void
    {
        $post = Post::factory()->published()->create();

        $this->post(route('posts.comments.store', $post), ['body' => 'Hi'])->assertRedirect(route('login'));
    }
}
```

**Step 3: Run — expect FAIL**

```bash
php artisan test --compact --filter=CommentTest
```

**Step 4: Create Comment model + factory**

```bash
php artisan make:model Comment --factory --no-interaction
```

```php
// app/Models/Comment.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'user_id', 'parent_id', 'body'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
}
```

```php
// database/factories/CommentFactory.php
public function definition(): array
{
    return [
        'post_id'   => Post::factory(),
        'user_id'   => User::factory(),
        'parent_id' => null,
        'body'      => fake()->paragraph(),
    ];
}
```

**Step 5: Create CommentController**

```bash
php artisan make:controller CommentController --no-interaction
```

```php
// app/Http/Controllers/CommentController.php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'body'      => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $post->comments()->create([
            ...$validated,
            'user_id' => $request->user()->id,
        ]);

        return back();
    }
}
```

**Step 6: Add comments relationship to Post model**

```php
// app/Models/Post.php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function comments(): HasMany
{
    return $this->hasMany(Comment::class)->whereNull('parent_id')->with('replies.author', 'author')->latest();
}
```

**Step 7: Add routes**

```php
// routes/web.php — inside auth middleware group
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
```

**Step 8: Add comments section to post show view**

```html
{{-- Append to resources/views/posts/show.blade.php --}}
<section class="mt-16 pt-8 border-t border-stone-100 max-w-2xl mx-auto">
    <h3 class="font-serif text-xl font-semibold mb-6">Responses</h3>

    @auth
    <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mb-8">
        @csrf
        <textarea name="body" rows="3" placeholder="What do you think?"
            class="w-full border border-stone-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-accent resize-none">{{ old('body') }}</textarea>
        <button class="mt-2 bg-stone-900 text-white px-5 py-2 rounded-full text-sm hover:bg-stone-700">Respond</button>
    </form>
    @endauth

    <div class="space-y-6">
        @foreach ($post->comments as $comment)
        <div class="border-b border-stone-100 pb-6">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-sm font-medium text-stone-700">{{ $comment->author->name }}</span>
                <span class="text-xs text-stone-400">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-stone-600 text-sm leading-relaxed">{{ $comment->body }}</p>

            {{-- Replies --}}
            @foreach ($comment->replies as $reply)
            <div class="ml-6 mt-4 pt-4 border-t border-stone-50">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-sm font-medium text-stone-700">{{ $reply->author->name }}</span>
                    <span class="text-xs text-stone-400">{{ $reply->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-stone-600 text-sm leading-relaxed">{{ $reply->body }}</p>
            </div>
            @endforeach

            @auth
            <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mt-3">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                <input name="body" type="text" placeholder="Reply..."
                    class="border-b border-stone-200 focus:border-accent outline-none text-sm w-full pb-1">
            </form>
            @endauth
        </div>
        @endforeach
    </div>
</section>
```

**Step 9: Run — expect PASS**

```bash
php artisan test --compact --filter=CommentTest
```

**Step 10: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: threaded comments on posts"
```

---

### Task 7: Reactions

**Files:**
- Create: `database/migrations/2026_05_24_000004_create_reactions_table.php`
- Create: `app/Models/Reaction.php`
- Create: `app/Enums/ReactionType.php`
- Create: `app/Http/Controllers/ReactionController.php`
- Modify: `resources/views/posts/show.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/ReactionTest.php`

**Step 1: Create enum**

```bash
php artisan make:class Enums/ReactionType --invokable --no-interaction
```

Replace content:

```php
// app/Enums/ReactionType.php
<?php

namespace App\Enums;

enum ReactionType: string
{
    case ThoughtProvoking = 'thought_provoking';
    case BeautifullyWritten = 'beautifully_written';
    case ChangedMyMind = 'changed_my_mind';

    public function label(): string
    {
        return match($this) {
            self::ThoughtProvoking  => 'Thought-provoking',
            self::BeautifullyWritten => 'Beautifully written',
            self::ChangedMyMind     => 'Changed my mind',
        };
    }
}
```

**Step 2: Create migration**

```bash
php artisan make:migration create_reactions_table --no-interaction
```

```php
public function up(): void
{
    Schema::create('reactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('type');
        $table->timestamp('created_at');
        $table->unique(['post_id', 'user_id', 'type']);
    });
}
```

**Step 3: Write failing test**

```php
// tests/Feature/ReactionTest.php
<?php

namespace Tests\Feature;

use App\Enums\ReactionType;
use App\Models\Post;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_react_to_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($user)->post(route('posts.reactions.store', $post), [
            'type' => ReactionType::ThoughtProvoking->value,
        ]);

        $this->assertDatabaseHas('reactions', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'type'    => ReactionType::ThoughtProvoking->value,
        ]);
    }

    public function test_user_can_toggle_reaction_off(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();

        // React
        $this->actingAs($user)->post(route('posts.reactions.store', $post), [
            'type' => ReactionType::BeautifullyWritten->value,
        ]);

        // React again — should toggle off
        $this->actingAs($user)->post(route('posts.reactions.store', $post), [
            'type' => ReactionType::BeautifullyWritten->value,
        ]);

        $this->assertDatabaseMissing('reactions', ['post_id' => $post->id, 'user_id' => $user->id]);
    }
}
```

**Step 4: Run — expect FAIL**

```bash
php artisan test --compact --filter=ReactionTest
```

**Step 5: Create Reaction model**

```bash
php artisan make:model Reaction --no-interaction
```

```php
// app/Models/Reaction.php
<?php

namespace App\Models;

use App\Enums\ReactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    public $timestamps = false;
    const UPDATED_AT = null;

    protected $fillable = ['post_id', 'user_id', 'type'];

    protected function casts(): array
    {
        return ['type' => ReactionType::class, 'created_at' => 'datetime'];
    }

    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
```

**Step 6: Create ReactionController**

```bash
php artisan make:controller ReactionController --no-interaction
```

```php
// app/Http/Controllers/ReactionController.php
<?php

namespace App\Http\Controllers;

use App\Enums\ReactionType;
use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', array_column(ReactionType::cases(), 'value'))],
        ]);

        $existing = Reaction::where([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'type'    => $validated['type'],
        ])->first();

        if ($existing) {
            $existing->delete();
        } else {
            $post->reactions()->create([
                'user_id'    => $request->user()->id,
                'type'       => $validated['type'],
                'created_at' => now(),
            ]);
        }

        return back();
    }
}
```

**Step 7: Add reactions relationship to Post**

```php
// app/Models/Post.php
use Illuminate\Database\Eloquent\Relations\HasMany;

public function reactions(): HasMany
{
    return $this->hasMany(Reaction::class);
}
```

**Step 8: Add route**

```php
// routes/web.php — inside auth middleware group
Route::post('/posts/{post}/reactions', [ReactionController::class, 'store'])->name('posts.reactions.store');
```

**Step 9: Add reaction buttons to post show view**

```html
{{-- In resources/views/posts/show.blade.php, after article body --}}
@auth
<div class="mt-8 flex gap-3 flex-wrap">
    @foreach (\App\Enums\ReactionType::cases() as $type)
    @php
        $count = $post->reactions->where('type', $type)->count();
        $reacted = $post->reactions->where('user_id', auth()->id())->where('type', $type)->isNotEmpty();
    @endphp
    <form method="POST" action="{{ route('posts.reactions.store', $post) }}">
        @csrf
        <input type="hidden" name="type" value="{{ $type->value }}">
        <button class="text-sm px-4 py-1.5 rounded-full border transition-colors
            {{ $reacted ? 'bg-accent text-white border-accent' : 'border-stone-200 text-stone-600 hover:border-accent' }}">
            {{ $type->label() }} @if($count > 0) · {{ $count }} @endif
        </button>
    </form>
    @endforeach
</div>
@endauth
```

**Step 10: Run — expect PASS**

```bash
php artisan test --compact --filter=ReactionTest
```

**Step 11: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: meaningful reactions on posts"
```

---

### Task 8: Reading Circles

**Files:**
- Create: `database/migrations/2026_05_24_000005_create_reading_circles_tables.php`
- Create: `app/Models/ReadingCircle.php`
- Create: `app/Models/CircleMessage.php`
- Create: `app/Http/Controllers/ReadingCircleController.php`
- Create: `app/Http/Controllers/CircleMessageController.php`
- Create: `resources/views/circles/show.blade.php`
- Modify: `resources/views/posts/show.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/ReadingCircleTest.php`

**Step 1: Create migration**

```bash
php artisan make:migration create_reading_circles_tables --no-interaction
```

```php
public function up(): void
{
    Schema::create('reading_circles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_id')->constrained()->cascadeOnDelete();
        $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('circle_members', function (Blueprint $table) {
        $table->foreignId('circle_id')->constrained('reading_circles')->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->timestamp('joined_at');
        $table->primary(['circle_id', 'user_id']);
    });

    Schema::create('circle_messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('circle_id')->constrained('reading_circles')->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->text('body');
        $table->timestamps();
    });
}
```

**Step 2: Write failing test**

```php
// tests/Feature/ReadingCircleTest.php
<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\ReadingCircle;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReadingCircleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_reading_circle_for_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($user)->post(route('posts.circles.store', $post), [
            'name' => 'Our Book Club',
        ]);

        $this->assertDatabaseHas('reading_circles', ['post_id' => $post->id, 'name' => 'Our Book Club']);
    }

    public function test_creator_is_auto_joined_to_circle(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();

        $response = $this->actingAs($user)->post(route('posts.circles.store', $post), [
            'name' => 'My Circle',
        ]);

        $circle = ReadingCircle::first();
        $this->assertTrue($circle->members->contains($user));
    }

    public function test_member_can_post_message_to_circle(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();
        $circle = ReadingCircle::factory()->for($post)->for($user, 'creator')->create();
        $circle->members()->attach($user->id, ['joined_at' => now()]);

        $this->actingAs($user)->post(route('circles.messages.store', $circle), [
            'body' => 'What did everyone think of the ending?',
        ]);

        $this->assertDatabaseHas('circle_messages', ['circle_id' => $circle->id, 'body' => 'What did everyone think of the ending?']);
    }

    public function test_non_member_cannot_post_to_circle(): void
    {
        $creator = User::factory()->create();
        $outsider = User::factory()->create();
        $post = Post::factory()->published()->create();
        $circle = ReadingCircle::factory()->for($post)->for($creator, 'creator')->create();

        $this->actingAs($outsider)->post(route('circles.messages.store', $circle), [
            'body' => 'Sneaking in!',
        ])->assertForbidden();
    }
}
```

**Step 3: Run — expect FAIL**

```bash
php artisan test --compact --filter=ReadingCircleTest
```

**Step 4: Create models + factories**

```bash
php artisan make:model ReadingCircle --factory --no-interaction
php artisan make:model CircleMessage --factory --no-interaction
```

```php
// app/Models/ReadingCircle.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReadingCircle extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'creator_id', 'name'];

    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'creator_id'); }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'circle_members', 'circle_id', 'user_id')
                    ->withPivot('joined_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CircleMessage::class, 'circle_id')->with('author')->latest();
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }
}
```

```php
// database/factories/ReadingCircleFactory.php
public function definition(): array
{
    return [
        'post_id'    => Post::factory(),
        'creator_id' => User::factory(),
        'name'       => fake()->words(3, true),
    ];
}
```

```php
// app/Models/CircleMessage.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CircleMessage extends Model
{
    protected $fillable = ['circle_id', 'user_id', 'body'];

    public function circle(): BelongsTo { return $this->belongsTo(ReadingCircle::class, 'circle_id'); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}
```

**Step 5: Create controllers**

```bash
php artisan make:controller ReadingCircleController --no-interaction
php artisan make:controller CircleMessageController --no-interaction
```

```php
// app/Http/Controllers/ReadingCircleController.php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\ReadingCircle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingCircleController extends Controller
{
    public function store(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:100']]);

        $circle = $post->circles()->create([
            'creator_id' => $request->user()->id,
            'name'       => $validated['name'],
        ]);

        $circle->members()->attach($request->user()->id, ['joined_at' => now()]);

        return redirect()->route('circles.show', $circle);
    }

    public function show(ReadingCircle $circle): View
    {
        abort_unless($circle->hasMember(auth()->user()), 403);

        $circle->load('post', 'members', 'messages');

        return view('circles.show', compact('circle'));
    }

    public function join(Request $request, ReadingCircle $circle): RedirectResponse
    {
        if (! $circle->hasMember($request->user())) {
            $circle->members()->attach($request->user()->id, ['joined_at' => now()]);
        }

        return redirect()->route('circles.show', $circle);
    }
}
```

```php
// app/Http/Controllers/CircleMessageController.php
<?php

namespace App\Http\Controllers;

use App\Models\ReadingCircle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CircleMessageController extends Controller
{
    public function store(Request $request, ReadingCircle $circle): RedirectResponse
    {
        abort_unless($circle->hasMember($request->user()), 403);

        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $circle->messages()->create([
            'user_id' => $request->user()->id,
            'body'    => $validated['body'],
        ]);

        return back();
    }
}
```

**Step 6: Add circles relationship to Post**

```php
// app/Models/Post.php
public function circles(): HasMany
{
    return $this->hasMany(ReadingCircle::class);
}
```

**Step 7: Add routes**

```php
// routes/web.php — inside auth middleware group
Route::post('/posts/{post}/circles', [ReadingCircleController::class, 'store'])->name('posts.circles.store');
Route::get('/circles/{circle}', [ReadingCircleController::class, 'show'])->name('circles.show');
Route::post('/circles/{circle}/join', [ReadingCircleController::class, 'join'])->name('circles.join');
Route::post('/circles/{circle}/messages', [CircleMessageController::class, 'store'])->name('circles.messages.store');
```

**Step 8: Create circle show view**

```html
{{-- resources/views/circles/show.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <p class="text-sm text-stone-400 mb-1">Reading circle for</p>
            <a href="{{ route('posts.show', $circle->post) }}" class="font-serif text-xl font-semibold hover:text-accent">{{ $circle->post->title }}</a>
            <h2 class="text-stone-600 mt-1">{{ $circle->name }}</h2>
        </div>

        <div class="mb-8 text-sm text-stone-400">
            {{ $circle->members->count() }} {{ Str::plural('member', $circle->members->count()) }}
        </div>

        <div class="space-y-4 mb-8 max-h-96 overflow-y-auto">
            @foreach ($circle->messages->reverse() as $message)
            <div class="bg-stone-50 rounded-lg px-4 py-3">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-sm font-medium text-stone-700">{{ $message->author->name }}</span>
                    <span class="text-xs text-stone-400">{{ $message->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-stone-600 text-sm leading-relaxed">{{ $message->body }}</p>
            </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('circles.messages.store', $circle) }}" class="border-t border-stone-100 pt-4">
            @csrf
            <div class="flex gap-3">
                <input name="body" type="text" placeholder="Share your thoughts..."
                    class="flex-1 border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-accent">
                <button class="bg-stone-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-stone-700">Send</button>
            </div>
        </form>
    </div>
</x-app-layout>
```

**Step 9: Add "Start a reading circle" to post show view**

```html
{{-- In resources/views/posts/show.blade.php, after reactions section --}}
@auth
<div class="mt-8 pt-8 border-t border-stone-100">
    <details class="group">
        <summary class="cursor-pointer text-sm text-stone-500 hover:text-stone-900 list-none flex items-center gap-2">
            <span>Start a reading circle</span>
            <span class="group-open:rotate-180 transition-transform">↓</span>
        </summary>
        <form method="POST" action="{{ route('posts.circles.store', $post) }}" class="mt-3 flex gap-3">
            @csrf
            <input name="name" type="text" placeholder="Name your circle..."
                class="flex-1 border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-accent">
            <button class="bg-stone-900 text-white px-4 py-2 rounded-lg text-sm hover:bg-stone-700">Create</button>
        </form>
    </details>
</div>
@endauth
```

**Step 10: Run — expect PASS**

```bash
php artisan test --compact --filter=ReadingCircleTest
```

**Step 11: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: reading circles — private discussion groups around posts"
```

---

## Phase 4: Discovery & Admin

### Task 9: Admin featured posts curation

**Files:**
- Create: `database/migrations/2026_05_24_000006_add_featured_to_posts_table.php`
- Create: `app/Http/Controllers/Admin/PostCurationController.php`
- Modify: `app/Models/Post.php`
- Modify: `resources/views/posts/index.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/PostCurationTest.php`

**Step 1: Create migration**

```bash
php artisan make:migration add_featured_to_posts_table --no-interaction
```

```php
public function up(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->boolean('is_featured')->default(false)->after('published_at');
    });
}
```

**Step 2: Write failing test**

```php
// tests/Feature/Admin/PostCurationTest.php
<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostCurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_feature_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($admin)->patch(route('admin.posts.feature', $post));

        $this->assertTrue($post->fresh()->is_featured);
    }

    public function test_regular_user_cannot_feature_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->published()->create();

        $this->actingAs($user)->patch(route('admin.posts.feature', $post))->assertForbidden();
    }

    public function test_featured_posts_appear_first_on_homepage(): void
    {
        $regular = Post::factory()->published()->create();
        $featured = Post::factory()->published()->create(['is_featured' => true]);

        $response = $this->get(route('posts.index'));

        $content = $response->getContent();
        $this->assertLessThan(
            strpos($content, $regular->title),
            strpos($content, $featured->title)
        );
    }
}
```

**Step 3: Add admin factory state to UserFactory**

```php
// database/factories/UserFactory.php
public function admin(): static
{
    return $this->state(['is_admin' => true]);
}
```

**Step 4: Run — expect FAIL**

```bash
php artisan test --compact --filter=PostCurationTest
```

**Step 5: Update Post model**

```php
// app/Models/Post.php — add to fillable and add scope
'is_featured',

public function scopeFeatured(Builder $query): Builder
{
    return $query->where('is_featured', true);
}
```

**Step 6: Create admin curation controller**

```bash
php artisan make:controller Admin/PostCurationController --no-interaction
```

```php
// app/Http/Controllers/Admin/PostCurationController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostCurationController extends Controller
{
    public function feature(Request $request, Post $post): RedirectResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        $post->update(['is_featured' => ! $post->is_featured]);

        return back();
    }
}
```

**Step 7: Add route**

```php
// routes/web.php — inside admin middleware group
Route::patch('posts/{post}/feature', [\App\Http\Controllers\Admin\PostCurationController::class, 'feature'])->name('admin.posts.feature');
```

**Step 8: Update PostController index — featured first**

```php
// app/Http/Controllers/PostController.php
$posts = Post::published()
    ->orderByDesc('is_featured')
    ->orderByDesc('published_at')
    ->get();
```

**Step 9: Update index view to show Featured badge**

```html
{{-- In article card in resources/views/posts/index.blade.php --}}
@if ($post->is_featured)
<span class="inline-block text-xs font-medium text-accent border border-accent px-2 py-0.5 rounded-full mb-2">Featured</span>
@endif
```

**Step 10: Run — expect PASS**

```bash
php artisan test --compact --filter=PostCurationTest
```

**Step 11: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: admin can feature posts, featured appear first on homepage"
```

---

## Phase 5: Monetization

### Task 10: Writer tips via Stripe

**Files:**
- Create: `database/migrations/2026_05_24_000007_create_tips_table.php`
- Create: `app/Models/Tip.php`
- Create: `app/Http/Controllers/TipController.php`
- Create: `resources/views/tips/checkout.blade.php`
- Modify: `routes/web.php`
- Modify: `composer.json` (add Stripe SDK)
- Test: `tests/Feature/TipTest.php`

**Step 1: Install Stripe**

```bash
composer require stripe/stripe-php --no-interaction
```

**Step 2: Add Stripe keys to .env**

```
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

And to `config/services.php`:

```php
'stripe' => [
    'key'    => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
],
```

**Step 3: Create migration**

```bash
php artisan make:migration create_tips_table --no-interaction
```

```php
public function up(): void
{
    Schema::create('tips', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
        $table->unsignedInteger('amount_cents');
        $table->string('stripe_payment_intent_id')->unique();
        $table->string('status')->default('pending');
        $table->timestamps();
    });
}
```

**Step 4: Write failing test**

```php
// tests/Feature/TipTest.php
<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TipTest extends TestCase
{
    use RefreshDatabase;

    public function test_tip_page_requires_auth(): void
    {
        $writer = User::factory()->create();

        $this->get(route('writers.tip', $writer))->assertRedirect(route('login'));
    }

    public function test_user_cannot_tip_themselves(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('writers.tip', $user))->assertForbidden();
    }

    public function test_tip_page_shows_for_authenticated_user(): void
    {
        $tipper = User::factory()->create();
        $writer = User::factory()->create();

        $this->actingAs($tipper)->get(route('writers.tip', $writer))->assertOk();
    }
}
```

**Step 5: Run — expect FAIL**

```bash
php artisan test --compact --filter=TipTest
```

**Step 6: Create Tip model**

```bash
php artisan make:model Tip --no-interaction
```

```php
// app/Models/Tip.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tip extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'post_id', 'amount_cents', 'stripe_payment_intent_id', 'status'];

    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_id'); }
    public function receiver(): BelongsTo { return $this->belongsTo(User::class, 'receiver_id'); }
    public function post(): BelongsTo { return $this->belongsTo(Post::class); }
}
```

**Step 7: Create TipController**

```bash
php artisan make:controller TipController --no-interaction
```

```php
// app/Http/Controllers/TipController.php
<?php

namespace App\Http\Controllers;

use App\Models\Tip;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\StripeClient;

class TipController extends Controller
{
    public function create(Request $request, User $writer): View
    {
        abort_if($request->user()->id === $writer->id, 403);

        return view('tips.checkout', [
            'writer'     => $writer,
            'stripeKey'  => config('services.stripe.key'),
        ]);
    }

    public function store(Request $request, User $writer): RedirectResponse
    {
        abort_if($request->user()->id === $writer->id, 403);

        $validated = $request->validate([
            'amount_cents' => ['required', 'integer', 'min:100', 'max:100000'],
        ]);

        $stripe = new StripeClient(config('services.stripe.secret'));

        $intent = $stripe->paymentIntents->create([
            'amount'   => $validated['amount_cents'],
            'currency' => 'myr',
            'metadata' => [
                'sender_id'   => $request->user()->id,
                'receiver_id' => $writer->id,
            ],
        ]);

        Tip::create([
            'sender_id'                => $request->user()->id,
            'receiver_id'              => $writer->id,
            'amount_cents'             => $validated['amount_cents'],
            'stripe_payment_intent_id' => $intent->id,
            'status'                   => 'pending',
        ]);

        return redirect()->away($intent->next_action?->redirect_to_url?->url ?? route('writers.show', $writer));
    }
}
```

**Step 8: Add routes**

```php
// routes/web.php — inside auth middleware group
Route::get('/writers/{writer}/tip', [TipController::class, 'create'])->name('writers.tip');
Route::post('/writers/{writer}/tip', [TipController::class, 'store'])->name('writers.tip.store');
```

**Step 9: Create tip checkout view**

```html
{{-- resources/views/tips/checkout.blade.php --}}
<x-app-layout>
    <div class="max-w-md mx-auto text-center">
        <h1 class="text-3xl font-serif font-semibold mb-2">Support {{ $writer->name }}</h1>
        <p class="text-stone-500 mb-8">Your tip goes directly to the writer.</p>

        <form method="POST" action="{{ route('writers.tip.store', $writer) }}" class="space-y-4">
            @csrf
            <div class="flex justify-center gap-3">
                @foreach ([500, 1000, 2000] as $preset)
                <button type="button"
                    onclick="document.getElementById('amount').value = {{ $preset }}"
                    class="px-5 py-2 border border-stone-200 rounded-full text-sm hover:border-accent">
                    RM {{ number_format($preset / 100, 2) }}
                </button>
                @endforeach
            </div>
            <input id="amount" name="amount_cents" type="number" min="100" placeholder="Custom amount (cents)"
                class="w-full border border-stone-200 rounded-lg px-4 py-2 text-sm text-center focus:outline-none focus:border-accent">
            <button class="w-full bg-stone-900 text-white py-3 rounded-full text-sm hover:bg-stone-700">
                Send tip via Stripe
            </button>
        </form>
    </div>
</x-app-layout>
```

**Step 10: Add tip button to writer profile**

```html
{{-- In resources/views/writers/show.blade.php --}}
@auth
    @if (auth()->id() !== $writer->id)
    <a href="{{ route('writers.tip', $writer) }}" class="text-sm text-stone-500 border border-stone-200 px-4 py-1 rounded-full hover:border-accent">Tip writer</a>
    @endif
@endauth
```

**Step 11: Run — expect PASS**

```bash
php artisan test --compact --filter=TipTest
```

**Step 12: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add -A
git commit -m "feat: writer tips via Stripe checkout"
```

---

## Final

**Run full test suite:**

```bash
php artisan test --compact
```

All green → done.
