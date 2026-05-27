# Writer Profile Redesign

**Date:** 2026-05-27  
**Route:** `GET /writers/{writer}` → `writers.show`

---

## Goal

Redesign the writer profile page to match a Medium-style layout: two-column, tabbed content on the left, profile card on the right, with a shareable link via a three-dots dropdown.

---

## Database

Add a `bio` column (nullable string) to the `users` table via migration. No avatar column — use Gravatar derived from the user's email (`md5(strtolower(email))`).

Add `bio` to the `User` model's `#[Fillable]` attribute. Add an `avatarUrl(): string` method that returns the Gravatar URL.

---

## Routes & Controller

Keep the single `GET writers/{writer}` route. The `WriterProfileController@show` method reads an optional `?tab=` query parameter and loads data accordingly:

| Tab | Query |
|-----|-------|
| `home` (default) | Published posts, latest first |
| `activity` | Writer's reactions and comments, merged and sorted by date |
| `lists` | Writer's reading circles |
| `about` | No extra query — bio and join date from the writer model |

The controller passes `$writer`, `$tab`, `$isFollowing`, and a tab-specific `$items` collection to the view.

---

## View Layout

Two-column layout using Tailwind. Left column holds the main content; right column holds the profile card sidebar.

### Left — Main Content

- **Header:** Writer name truncated with ellipsis (`Luqman Farhan A...`) + three-dots button. The button opens a small dropdown with a "Copy link" option that copies the profile URL to the clipboard via `navigator.clipboard.writeText()`.
- **Tab bar:** Home · Activity · Lists · About. Active tab has an underline. Each tab links to the same route with `?tab=` appended.
- **Tab content area** below the tab bar (see tab details below).

### Right — Sidebar

- Gravatar avatar (circle, ~80px)
- Full name
- Bio text
- "Edit profile" link — visible only when the authenticated user views their own profile
- Follow / Tip buttons — moved here from the current top-right position in the main column
- **Following section:** up to 5 users this writer follows, each shown as avatar + name linking to their profile page. A "See all (N)" link below opens the full list.

### Tab Content

**Home** — Post cards showing title, excerpt, published date, and thumbnail (if the post has one).

**Activity** — Chronological feed of the writer's reactions (post title + reaction type) and comments (post title + comment excerpt), newest first.

**Lists** — The writer's reading circles: circle name and the post it belongs to.

**About** — Bio paragraph and member-since date.

---

## Share Link

No new route needed. The three-dots dropdown uses a single `<button>` with an `onclick` handler:

```js
navigator.clipboard.writeText('{{ route("writers.show", $writer) }}');
```

A brief "Copied!" tooltip confirms the action.

---

## Out of Scope

- Avatar upload (Gravatar only)
- Followers list in sidebar (Following list only, per design decision)
- Livewire / SPA tab switching (query string only)
- New base directories or package additions
