# Nukilan

Nukilan is a Laravel-based writing platform for Malaysian and Southeast Asian writers. The current build has moved beyond the original simple blog into a community writing app with authored posts, writer profiles, follows, comments, reactions, reading circles, admin curation, and early writer tipping support.

## Current Stack

- PHP 8.4
- Laravel 13
- Laravel Breeze authentication
- Blade views
- Tailwind CSS 3.4
- Alpine.js
- SQLite for local development
- PHPUnit 12
- Stripe PHP SDK for writer tips

## Features Implemented So Far

### Public Reading Experience

- Public homepage at `/` showing published stories.
- Published stories are ordered with featured posts first, then latest published date.
- Public story detail pages at `/posts/{post:slug}`.
- Story pages show title, excerpt, author, publish date, estimated reading time, reactions, reading circles, and responses.
- Light editorial visual design under the Nukilan brand.

### Authentication

- User registration and login through Breeze.
- Social authentication routes for Google and GitHub are present.
- Admin login route is present at `/admin/login`.
- Form field contrast has been improved so typed text, placeholders, borders, and focus states are visible on the light UI.

### Writer Authorship

- Posts now belong to users through `posts.user_id`.
- Writers can create drafts.
- Writers can edit, publish, and delete their own posts.
- Post update/delete authorization is protected by `PostPolicy`.
- Writer post routes live under `/writer/posts/...`.

### Writer Profiles

- Public writer profile pages are available at `/writers/{writer}`.
- Profiles show the writer name, bio, avatar, and published posts only.
- Draft posts are hidden from public profiles.
- Profile page has tabbed layout: Posts, Responses, Activity.
- Sidebar shows follower/following counts and follow action.
- Share link copies the profile URL to clipboard.
- Writer bio is stored in `users.bio`; `avatarUrl()` method on `User` model returns avatar URL.

### Follow System

- Authenticated users can follow or unfollow other writers.
- Users cannot follow themselves.
- Writer profiles show follow/unfollow actions when viewing another writer.

### Comments

- Authenticated users can comment on published posts.
- Users can reply to existing comments.
- Comment replies are scoped to the same post during validation.
- Guests are redirected to login when attempting protected comment actions.

### Reactions

- Authenticated users can react to posts with meaningful reaction types:
  - Thought-provoking
  - Beautifully written
  - Changed my mind
- Reactions toggle off when the same user submits the same reaction again.
- Reaction counts appear on the story page.

### Reading Circles

- Authenticated users can create a private reading circle around a post.
- The circle creator is automatically joined as a member.
- Members can post messages inside the circle.
- Non-members cannot post messages to a circle.
- Users can join visible circles from the post page.

### Admin Curation

- Admin users can feature or unfeature posts.
- Featured posts appear first on the homepage.
- Admin curation route: `PATCH /admin/posts/{post}/feature`.

### Admin Moderation

- Admin users can list all posts at `GET /admin/posts`.
- Admin can delete any post via `DELETE /admin/posts/{post}`.
- Admin can toggle post visibility (hide/unhide) via `PATCH /admin/posts/{post}/visibility`.
- Hidden posts are tracked with `posts.hidden_at` timestamp; `scopeHidden` and updated `scopePublished` scopes exist on `Post` model.
- `PostPolicy` extended to allow admins to hide, update, and delete any post.

### App Layout

- Authenticated pages use a new sidebar layout with bottom tab bar for mobile.
- Public pages (home, post detail, writer profile) also use this layout with guest-safe auth guards.
- Blade components added: `authenticated-layout`, `sidebar-nav-link`, `bottom-tab`.
- Placeholder routes and views added for Library (`/library`), Stats (`/stats`), and Search (`/search`).

### Writer Tips

- Stripe PHP SDK has been added.
- Authenticated users can open a tip page for another writer.
- Users cannot tip themselves.
- Tip records are stored with sender, receiver, amount, Stripe payment intent, and status.
- Stripe keys must be configured before real payment flow usage.

## Main Routes

| Area | Route |
| --- | --- |
| Home | `GET /` |
| Story detail | `GET /posts/{post:slug}` |
| Writer profile | `GET /writers/{writer}` |
| Create story | `GET /writer/posts/create` |
| Store story | `POST /writer/posts` |
| Edit story | `GET /writer/posts/{post}/edit` |
| Update story | `PATCH /writer/posts/{post}` |
| Publish story | `PATCH /writer/posts/{post}/publish` |
| Delete story | `DELETE /writer/posts/{post}` |
| Follow writer | `POST /writers/{writer}/follow` |
| Unfollow writer | `DELETE /writers/{writer}/unfollow` |
| Comment on post | `POST /posts/{post}/comments` |
| React to post | `POST /posts/{post}/reactions` |
| Create circle | `POST /posts/{post}/circles` |
| View circle | `GET /circles/{circle}` |
| Join circle | `POST /circles/{circle}/join` |
| Circle message | `POST /circles/{circle}/messages` |
| Tip writer | `GET /writers/{writer}/tip` |
| Submit tip | `POST /writers/{writer}/tip` |
| Feature post | `PATCH /admin/posts/{post}/feature` |
| Admin post list | `GET /admin/posts` |
| Admin delete post | `DELETE /admin/posts/{post}` |
| Admin toggle visibility | `PATCH /admin/posts/{post}/visibility` |
| Writer drafts | `GET /writer/posts` |
| Library | `GET /library` |
| Stats | `GET /stats` |
| Search | `GET /search` |

## Database Progress

The current schema includes:

- `users`
- `posts`
- `follows`
- `comments`
- `reactions`
- `reading_circles`
- `circle_members`
- `circle_messages`
- `tips`
- `social_accounts`
- Laravel cache, jobs, sessions, and password reset tables

Important post fields added during the Nukilan work:

- `posts.user_id`
- `posts.is_featured`
- `posts.hidden_at`
- `users.bio`

## Important App Files

### Models

- `app/Models/Post.php`
- `app/Models/User.php`
- `app/Models/Comment.php`
- `app/Models/Reaction.php`
- `app/Models/ReadingCircle.php`
- `app/Models/CircleMessage.php`
- `app/Models/Follow.php`
- `app/Models/Tip.php`

### Controllers

- `app/Http/Controllers/PostController.php`
- `app/Http/Controllers/WriterPostController.php`
- `app/Http/Controllers/WriterProfileController.php`
- `app/Http/Controllers/FollowController.php`
- `app/Http/Controllers/CommentController.php`
- `app/Http/Controllers/ReactionController.php`
- `app/Http/Controllers/ReadingCircleController.php`
- `app/Http/Controllers/CircleMessageController.php`
- `app/Http/Controllers/TipController.php`
- `app/Http/Controllers/Admin/PostCurationController.php`
- `app/Http/Controllers/Admin/PostController.php`
- `app/Http/Controllers/Admin/PostVisibilityController.php`

### Views

- `resources/views/posts/index.blade.php`
- `resources/views/posts/show.blade.php`
- `resources/views/writer/posts/create.blade.php`
- `resources/views/writer/posts/edit.blade.php`
- `resources/views/writers/show.blade.php`
- `resources/views/circles/show.blade.php`
- `resources/views/tips/checkout.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/navigation.blade.php`
- `resources/views/components/authenticated-layout.blade.php`
- `resources/views/components/sidebar-nav-link.blade.php`
- `resources/views/components/bottom-tab.blade.php`
- `resources/views/writer/posts/index.blade.php`
- `resources/views/admin/posts/index.blade.php`
- `resources/views/library/index.blade.php`
- `resources/views/stats/index.blade.php`
- `resources/views/search/index.blade.php`

## Local Setup

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

Create the environment file if needed:

```bash
cp .env.example .env
php artisan key:generate
```

Run migrations:

```bash
php artisan migrate
```

Build frontend assets:

```bash
npm run build
```

Run the app locally:

```bash
php artisan serve
```

For active development with Vite:

```bash
npm run dev
```

## Stripe Configuration

Writer tips require Stripe credentials in `.env`:

```env
STRIPE_KEY=pk_test_your_key
STRIPE_SECRET=sk_test_your_secret
```

These values are read from `config/services.php`.

## Testing

Run the full PHPUnit suite:

```bash
php artisan test --compact
```

Run the frontend build:

```bash
npm run build
```

Run Pint after PHP changes:

```bash
vendor/bin/pint --dirty --format agent
```

Current feature coverage includes:

- Post authorship
- Writer post create/edit/publish/delete
- Writer draft management
- Writer profiles (tabs, bio, activity feed)
- Follow/unfollow
- Threaded comments
- Reactions
- Reading circles
- Admin post curation
- Admin post moderation (list, delete, visibility toggle)
- Writer tip page access
- Authenticated sidebar layout

## Known Remaining Work

- Finish real Stripe checkout confirmation/webhook handling.
- Add avatar upload and social links to writer profiles.
- Implement Library, Stats, and Search pages (currently placeholder views).
- Add moderation tools for comments and circles.
- Add pagination/search/discovery once content volume grows.
- Add notification flows for follows, replies, reactions, circle messages, and tips.
- Expand admin dashboard UI beyond post featuring.
- Seed realistic local demo content for easier manual testing.

