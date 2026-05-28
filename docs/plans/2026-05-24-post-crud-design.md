# Post CRUD Design

**Date:** 2026-05-24

## Overview

Add full post CRUD for writers (users and admins) with a draft management page, plus an admin moderation panel for hiding and deleting any post.

---

## Data Layer

### Migration

Add `hidden_at` (nullable timestamp) to the `posts` table. This mirrors the existing `published_at` pattern and records when an admin hid a post.

### Post Model

- Add `hidden_at` to `casts()` as `datetime`.
- Update `scopePublished` to also filter `->whereNull('hidden_at')`, so hidden posts never appear publicly.
- Add `scopeHidden` for admin queries that need to surface hidden posts.

### PostPolicy

- `delete()`: returns `true` if `$user->is_admin` OR the user owns the post.
- `hide()` (new): returns `true` only if `$user->is_admin`.

---

## Writer (User) Flow

### New: Draft Management Page

`WriterPostController::index` lists the authenticated user's own posts — drafts, published, and hidden — ordered latest first. Hidden posts show a badge but remain editable by the owner.

**Route:** `GET /writer/posts` → `writer.posts.index`

**View:** `resources/views/writer/posts/index.blade.php`

- Table with columns: Title, Status (Draft / Published / Hidden), Published date, Actions (Edit, Delete).
- "New story" button at top.

### Existing Methods (unchanged)

`create`, `store`, `edit`, `update`, `publish`, `destroy` in `WriterPostController` remain as-is.

---

## Admin Flow

### Admin Post List

`Admin\PostController::index` shows a paginated list of all posts from all users, ordered latest first.

**Route:** `GET /admin/posts` → `admin.posts.index`

**View:** `resources/views/admin/posts/index.blade.php`

- Table with columns: Title, Author, Status, Hidden, Actions (Hide/Unhide, Delete).

`Admin\PostController::destroy` deletes any post regardless of ownership.

**Route:** `DELETE /admin/posts/{post}` → `admin.posts.destroy`

### Admin Post Visibility

`Admin\PostVisibilityController::toggle` flips `hidden_at` between `now()` and `null`.

**Route:** `PATCH /admin/posts/{post}/visibility` → `admin.posts.visibility`

### Admin Writing

Admins post as themselves using the existing `writer.posts.*` routes. No separate flow needed.

---

## Routes Summary

```
GET    /writer/posts                    writer.posts.index        (auth)
GET    /admin/posts                     admin.posts.index         (admin)
DELETE /admin/posts/{post}              admin.posts.destroy       (admin)
PATCH  /admin/posts/{post}/visibility   admin.posts.visibility    (admin)
```

---

## What Does Not Change

- `WriterPostController` create/store/edit/update/publish/destroy
- `StoreWriterPostRequest`, `UpdateWriterPostRequest`
- Public `PostController` index and show
- All existing routes
