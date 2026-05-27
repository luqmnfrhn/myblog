# Writer Profile Redesign Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Redesign `/writers/{writer}` into a two-column, tabbed profile page with Gravatar avatar, bio, following sidebar, shareable link, and Home/Activity/Lists/About tabs.

**Architecture:** Single route, query-string tab switching (`?tab=`). Controller loads data per active tab. Two-column Tailwind layout with profile card sidebar. Gravatar for avatars (no upload).

**Tech Stack:** Laravel 13, PHP 8.4, Blade, Tailwind CSS v4, PHPUnit 12.

---

## Task 1: Add `bio` column to users table

**Files:**
- Create: `database/migrations/2026_05_28_000001_add_bio_to_users_table.php`
- Modify: `app/Models/User.php`
- Modify: `database/factories/UserFactory.php`
- Test: `tests/Feature/WriterProfileTest.php`

**Step 1: Write the failing test**

Add to `tests/Feature/WriterProfileTest.php`:

```php
public function test_writer_profile_shows_bio(): void
{
    $writer = User::factory()->create(['bio' => 'Sharing what inspires me.']);

    $response = $this->get(route('writers.show', $writer));

    $response->assertOk();
    $response->assertSee('Sharing what inspires me.');
}
```

**Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=test_writer_profile_shows_bio
```

Expected: FAIL — unknown column `bio`.

**Step 3: Create migration**

```bash
php artisan make:migration add_bio_to_users_table --no-interaction
```

Edit the generated file:

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('bio')->nullable()->after('name');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('bio');
    });
}
```

**Step 4: Run migration**

```bash
php artisan migrate --no-interaction
```

**Step 5: Update User model**

In `app/Models/User.php`, change the `#[Fillable]` attribute:

```php
#[Fillable(['name', 'bio', 'email', 'password', 'is_admin'])]
```

Add the `avatarUrl()` method before `socialAccounts()`:

```php
public function avatarUrl(int $size = 80): string
{
    $hash = md5(strtolower(trim($this->email)));

    return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=identicon";
}
```

**Step 6: Update UserFactory to support bio**

In `database/factories/UserFactory.php`, add `bio` to the `definition()` array:

```php
'bio' => null,
```

**Step 7: Run test to verify it passes**

```bash
php artisan test --compact --filter=test_writer_profile_shows_bio
```

Expected: PASS.

**Step 8: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 9: Commit**

```bash
git add database/migrations/2026_05_28_000001_add_bio_to_users_table.php app/Models/User.php database/factories/UserFactory.php tests/Feature/WriterProfileTest.php
git commit -m "feat: add bio column to users and avatarUrl method"
```

---

## Task 2: Update `WriterProfileController` for tabs

**Files:**
- Modify: `app/Http/Controllers/WriterProfileController.php`
- Test: `tests/Feature/WriterProfileTest.php`

**Step 1: Write failing tests for each tab**

Add to `tests/Feature/WriterProfileTest.php`:

```php
public function test_activity_tab_shows_writer_reactions(): void
{
    $writer = User::factory()->create();
    $post = Post::factory()->published()->create();
    \App\Models\Reaction::factory()->create([
        'user_id' => $writer->id,
        'post_id' => $post->id,
        'type' => \App\Enums\ReactionType::ThoughtProvoking->value,
    ]);

    $response = $this->get(route('writers.show', [$writer, 'tab' => 'activity']));

    $response->assertOk();
    $response->assertSee($post->title);
}

public function test_activity_tab_shows_writer_comments(): void
{
    $writer = User::factory()->create();
    $post = Post::factory()->published()->create();
    \App\Models\Comment::factory()->create([
        'user_id' => $writer->id,
        'post_id' => $post->id,
        'body' => 'A thoughtful comment.',
    ]);

    $response = $this->get(route('writers.show', [$writer, 'tab' => 'activity']));

    $response->assertOk();
    $response->assertSee('A thoughtful comment.');
}

public function test_lists_tab_shows_reading_circles(): void
{
    $writer = User::factory()->create();
    $circle = \App\Models\ReadingCircle::factory()->create(['creator_id' => $writer->id]);

    $response = $this->get(route('writers.show', [$writer, 'tab' => 'lists']));

    $response->assertOk();
    $response->assertSee($circle->name);
}

public function test_about_tab_shows_bio_and_join_date(): void
{
    $writer = User::factory()->create(['bio' => 'My bio text.']);

    $response = $this->get(route('writers.show', [$writer, 'tab' => 'about']));

    $response->assertOk();
    $response->assertSee('My bio text.');
    $response->assertSee($writer->created_at->format('F Y'));
}
```

**Step 2: Run tests to verify they fail**

```bash
php artisan test --compact --filter=test_activity_tab_shows_writer_reactions
```

Expected: FAIL.

**Step 3: Check if Reaction has a factory**

```bash
php artisan tinker --execute 'echo class_exists(\Database\Factories\ReactionFactory::class) ? "yes" : "no";'
```

If "no", create one:

```bash
php artisan make:factory ReactionFactory --model=Reaction --no-interaction
```

Edit `database/factories/ReactionFactory.php`:

```php
public function definition(): array
{
    return [
        'post_id' => \App\Models\Post::factory(),
        'user_id' => \App\Models\User::factory(),
        'type' => \App\Enums\ReactionType::ThoughtProvoking->value,
        'created_at' => now(),
    ];
}
```

Add `HasFactory` to `app/Models/Reaction.php`:

```php
use Database\Factories\ReactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Inside class:
/** @use HasFactory<ReactionFactory> */
use HasFactory;
```

**Step 4: Update the controller**

Replace the contents of `app/Http/Controllers/WriterProfileController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WriterProfileController extends Controller
{
    public function show(Request $request, User $writer): View
    {
        $tab = $request->query('tab', 'home');

        $isFollowing = Auth::check()
            && ! Auth::user()->is($writer)
            && Auth::user()->isFollowing($writer);

        $following = $writer->following()->latest('follows.created_at')->take(5)->get();
        $followingCount = $writer->following()->count();

        $items = match ($tab) {
            'activity' => $this->activityItems($writer),
            'lists'    => $writer->readingCircles()->with('post')->latest()->get(),
            'about'    => new Collection,
            default    => $writer->posts()->published()->latest('published_at')->get(),
        };

        return view('writers.show', compact('writer', 'tab', 'isFollowing', 'following', 'followingCount', 'items'));
    }

    /**
     * @return Collection<int, array{type: string, post_title: string, post_slug: string, excerpt: string, date: \Carbon\Carbon}>
     */
    private function activityItems(User $writer): Collection
    {
        $reactions = $writer->reactions()
            ->with('post')
            ->latest()
            ->get()
            ->map(fn ($reaction) => [
                'type'       => 'reaction',
                'post_title' => $reaction->post->title,
                'post_slug'  => $reaction->post->slug,
                'excerpt'    => $reaction->type->label(),
                'date'       => $reaction->created_at,
            ]);

        $comments = $writer->comments()
            ->with('post')
            ->latest()
            ->get()
            ->map(fn ($comment) => [
                'type'       => 'comment',
                'post_title' => $comment->post->title,
                'post_slug'  => $comment->post->slug,
                'excerpt'    => \Illuminate\Support\Str::limit($comment->body, 80),
                'date'       => $comment->created_at,
            ]);

        return $reactions->concat($comments)->sortByDesc('date')->values();
    }
}
```

**Step 5: Add missing relations to User model**

In `app/Models/User.php`, add:

```php
use App\Models\Comment;
use App\Models\Reaction;
use App\Models\ReadingCircle;

// Relations:
public function reactions(): HasMany
{
    return $this->hasMany(Reaction::class);
}

public function comments(): HasMany
{
    return $this->hasMany(Comment::class);
}

public function readingCircles(): HasMany
{
    return $this->hasMany(ReadingCircle::class, 'creator_id');
}
```

**Step 6: Run all new tests**

```bash
php artisan test --compact --filter=WriterProfileTest
```

Expected: All PASS.

**Step 7: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 8: Commit**

```bash
git add app/Http/Controllers/WriterProfileController.php app/Models/User.php app/Models/Reaction.php database/factories/ReactionFactory.php tests/Feature/WriterProfileTest.php
git commit -m "feat: update WriterProfileController with tab support and activity feed"
```

---

## Task 3: Rebuild `writers/show.blade.php` view

**Files:**
- Modify: `resources/views/writers/show.blade.php`
- Test: `tests/Feature/WriterProfileTest.php`

**Step 1: Add view tests**

Add to `tests/Feature/WriterProfileTest.php`:

```php
public function test_profile_shows_gravatar_avatar(): void
{
    $writer = User::factory()->create();
    $hash = md5(strtolower(trim($writer->email)));

    $response = $this->get(route('writers.show', $writer));

    $response->assertOk();
    $response->assertSee($hash);
}

public function test_profile_shows_following_list_in_sidebar(): void
{
    $writer = User::factory()->create();
    $followed = User::factory()->create(['name' => 'Jane Followed']);
    $writer->following()->attach($followed);

    $response = $this->get(route('writers.show', $writer));

    $response->assertOk();
    $response->assertSee('Jane Followed');
}

public function test_profile_shows_edit_link_to_own_profile(): void
{
    $writer = User::factory()->create();

    $response = $this->actingAs($writer)->get(route('writers.show', $writer));

    $response->assertOk();
    $response->assertSee('Edit profile');
}

public function test_profile_hides_edit_link_from_other_users(): void
{
    $writer = User::factory()->create();
    $other = User::factory()->create();

    $response = $this->actingAs($other)->get(route('writers.show', $writer));

    $response->assertOk();
    $response->assertDontSee('Edit profile');
}

public function test_tab_bar_renders_all_four_tabs(): void
{
    $writer = User::factory()->create();

    $response = $this->get(route('writers.show', $writer));

    $response->assertOk();
    $response->assertSee('Home');
    $response->assertSee('Activity');
    $response->assertSee('Lists');
    $response->assertSee('About');
}
```

**Step 2: Run tests to verify they fail**

```bash
php artisan test --compact --filter=test_profile_shows_gravatar_avatar
```

Expected: FAIL.

**Step 3: Replace the view**

Replace `resources/views/writers/show.blade.php` entirely:

```blade
<x-authenticated-layout>
    <div class="mx-auto max-w-5xl px-4">
        <div class="flex gap-12">
            {{-- Left column --}}
            <div class="min-w-0 flex-1">
                {{-- Header: name + three-dots --}}
                <div class="mb-6 flex items-center gap-3">
                    <h1 class="truncate font-serif text-4xl font-semibold">
                        {{ $writer->name }}
                    </h1>
                    <div class="relative" x-data="{ open: false }">
                        <button
                            @click="open = !open"
                            class="rounded p-1 text-stone-400 hover:text-stone-700"
                            aria-label="More options"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/>
                            </svg>
                        </button>
                        <div
                            x-show="open"
                            @click.outside="open = false"
                            x-cloak
                            class="absolute left-0 top-8 z-10 w-44 rounded-md border border-stone-200 bg-white shadow-md"
                        >
                            <button
                                type="button"
                                onclick="navigator.clipboard.writeText('{{ route('writers.show', $writer) }}').then(() => { this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy link', 2000); })"
                                class="w-full px-4 py-2 text-left text-sm text-stone-700 hover:bg-stone-50"
                            >
                                Copy link
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tab bar --}}
                @php
                    $tabs = ['home' => 'Home', 'activity' => 'Activity', 'lists' => 'Lists', 'about' => 'About'];
                @endphp
                <div class="mb-8 flex gap-6 border-b border-stone-200">
                    @foreach ($tabs as $key => $label)
                        <a
                            href="{{ route('writers.show', [$writer, 'tab' => $key]) }}"
                            class="pb-3 text-sm font-medium transition-colors
                                {{ $tab === $key
                                    ? 'border-b-2 border-stone-900 text-stone-900'
                                    : 'text-stone-500 hover:text-stone-800' }}"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                {{-- Tab content --}}
                @if ($tab === 'home')
                    <div class="space-y-8">
                        @forelse ($items as $post)
                            <article class="flex gap-6 border-b border-stone-200 pb-8">
                                <div class="min-w-0 flex-1">
                                    <div class="mb-1 flex items-center gap-2 text-xs text-stone-500">
                                        <img src="{{ $writer->avatarUrl(24) }}" class="h-6 w-6 rounded-full" alt="">
                                        <span>{{ $writer->name }}</span>
                                        <span>·</span>
                                        <span>{{ $post->published_at?->format('M j, Y') }}</span>
                                    </div>
                                    <h2 class="mb-1 font-serif text-xl font-semibold leading-snug">
                                        <a href="{{ route('posts.show', $post) }}" class="hover:text-accent">
                                            {{ $post->title }}
                                        </a>
                                    </h2>
                                    <p class="text-sm leading-6 text-stone-600">{{ $post->excerpt }}</p>
                                </div>
                            </article>
                        @empty
                            <p class="text-stone-500">No stories yet.</p>
                        @endforelse
                    </div>

                @elseif ($tab === 'activity')
                    <div class="space-y-6">
                        @forelse ($items as $item)
                            <div class="border-b border-stone-200 pb-6">
                                <p class="mb-1 text-xs text-stone-400">
                                    {{ $item['type'] === 'reaction' ? 'Reacted' : 'Commented' }}
                                    · {{ $item['date']->format('M j, Y') }}
                                </p>
                                <a href="{{ route('posts.show', ['post' => $item['post_slug']]) }}" class="font-serif text-base font-semibold hover:text-accent">
                                    {{ $item['post_title'] }}
                                </a>
                                <p class="mt-1 text-sm text-stone-600">{{ $item['excerpt'] }}</p>
                            </div>
                        @empty
                            <p class="text-stone-500">No activity yet.</p>
                        @endforelse
                    </div>

                @elseif ($tab === 'lists')
                    <div class="space-y-6">
                        @forelse ($items as $circle)
                            <div class="rounded-md border border-stone-200 p-4">
                                <p class="font-semibold text-stone-900">{{ $circle->name }}</p>
                                @if ($circle->post)
                                    <p class="mt-1 text-sm text-stone-500">
                                        Based on:
                                        <a href="{{ route('posts.show', $circle->post) }}" class="hover:text-accent">
                                            {{ $circle->post->title }}
                                        </a>
                                    </p>
                                @endif
                            </div>
                        @empty
                            <p class="text-stone-500">No reading circles yet.</p>
                        @endforelse
                    </div>

                @elseif ($tab === 'about')
                    <div class="prose prose-stone max-w-none">
                        @if ($writer->bio)
                            <p>{{ $writer->bio }}</p>
                        @else
                            <p class="text-stone-400">No bio yet.</p>
                        @endif
                        <p class="text-sm text-stone-500">Member since {{ $writer->created_at->format('F Y') }}</p>
                    </div>
                @endif
            </div>

            {{-- Right sidebar --}}
            <aside class="w-64 shrink-0">
                {{-- Profile card --}}
                <div class="mb-6">
                    <img
                        src="{{ $writer->avatarUrl(80) }}"
                        alt="{{ $writer->name }}"
                        class="mb-3 h-20 w-20 rounded-full"
                    >
                    <p class="font-semibold text-stone-900">{{ $writer->name }}</p>
                    @if ($writer->bio)
                        <p class="mt-1 text-sm text-stone-500">{{ $writer->bio }}</p>
                    @endif

                    @auth
                        @if (auth()->id() === $writer->id)
                            <a href="#" class="mt-2 inline-block text-sm text-green-600 hover:underline">Edit profile</a>
                        @else
                            <div class="mt-3 flex gap-2">
                                @if ($isFollowing)
                                    <form method="POST" action="{{ route('writers.unfollow', $writer) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-md border border-stone-200 px-3 py-1.5 text-sm text-stone-500 hover:border-stone-400">
                                            Following
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('writers.follow', $writer) }}">
                                        @csrf
                                        <button class="rounded-md bg-stone-900 px-3 py-1.5 text-sm text-white hover:bg-stone-700">
                                            Follow
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('writers.tip', $writer) }}"
                                   class="rounded-md border border-stone-200 px-3 py-1.5 text-sm text-stone-500 hover:border-accent hover:text-accent">
                                    Tip writer
                                </a>
                            </div>
                        @endif
                    @endauth
                </div>

                {{-- Following list --}}
                <div>
                    <p class="mb-3 font-semibold text-stone-900">Following</p>
                    @forelse ($following as $followedUser)
                        <a
                            href="{{ route('writers.show', $followedUser) }}"
                            class="mb-3 flex items-center gap-2 hover:opacity-80"
                        >
                            <img
                                src="{{ $followedUser->avatarUrl(32) }}"
                                alt="{{ $followedUser->name }}"
                                class="h-8 w-8 rounded-full"
                            >
                            <span class="text-sm text-stone-700">{{ $followedUser->name }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-stone-400">Not following anyone yet.</p>
                    @endforelse

                    @if ($followingCount > 5)
                        <a href="#" class="mt-2 text-sm text-stone-500 hover:text-stone-800">
                            See all ({{ $followingCount }})
                        </a>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-authenticated-layout>
```

> **Note:** The three-dots dropdown uses Alpine.js (`x-data`, `x-show`). Check if Alpine.js is already loaded in the layout. If not, add it to the layout's `<head>` or use a plain JS toggle instead.

**Step 4: Check Alpine.js availability**

```bash
grep -r "alpine" resources/views/components/ --include="*.blade.php" -l
```

If no results, Alpine.js is not in the layout. Replace the `x-data`/`x-show` dropdown with a plain JS version:

```html
<div class="relative">
    <button
        onclick="this.nextElementSibling.classList.toggle('hidden')"
        class="rounded p-1 text-stone-400 hover:text-stone-700"
        aria-label="More options"
    >
        <!-- svg same as above -->
    </button>
    <div class="absolute left-0 top-8 z-10 hidden w-44 rounded-md border border-stone-200 bg-white shadow-md">
        <button
            type="button"
            onclick="navigator.clipboard.writeText('{{ route('writers.show', $writer) }}').then(() => { this.textContent = 'Copied!'; setTimeout(() => this.textContent = 'Copy link', 2000); })"
            class="w-full px-4 py-2 text-left text-sm text-stone-700 hover:bg-stone-50"
        >
            Copy link
        </button>
    </div>
</div>
```

**Step 5: Run all WriterProfile tests**

```bash
php artisan test --compact --filter=WriterProfileTest
```

Expected: All PASS.

**Step 6: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

**Step 7: Commit**

```bash
git add resources/views/writers/show.blade.php tests/Feature/WriterProfileTest.php
git commit -m "feat: redesign writer profile page with tabs, sidebar, and share link"
```

---

## Task 4: Verify in browser

**Step 1: Get the correct local URL**

Use the `get-absolute-url` MCP tool to resolve the URL for `/writers/3`.

**Step 2: Run the dev server if not running**

```bash
composer run dev
```

**Step 3: Visit the profile**

Open `http://127.0.0.1:8000/writers/3` in the browser. Verify:
- [ ] Two-column layout renders
- [ ] Gravatar avatar appears in sidebar
- [ ] Four tabs visible; Home is active by default
- [ ] Three-dots button opens dropdown with "Copy link"
- [ ] Clicking a tab switches content
- [ ] Following list shows followed users with links

**Step 4: Run the full test suite**

```bash
php artisan test --compact
```

Expected: All PASS.
