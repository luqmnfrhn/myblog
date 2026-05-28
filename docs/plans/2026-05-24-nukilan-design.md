# Nukilan — Platform Design

**Tagline:** Tulis. Kongsi. Bermakna. *(Write. Share. Matter.)*

## Vision

A Malaysian/Southeast Asian writing platform for open publishing. Community-first, no paywall, no algorithmic noise. Writers earn through direct reader tips. Reading circles create intimate discussion — distinct from Medium's broadcast model.

## Target Audience

- Primary writers: Malaysian and SEA creators writing in English, Malay, or both
- Readers: Anyone seeking thoughtful, human writing from the region

## Core Differentiators

1. **Reading Circles** — small groups gather around a post to read and discuss together, privately
2. **Meaningful Reactions** — "Thought-provoking", "Beautifully written", "Changed my mind" instead of generic likes
3. **Writer tips** — readers tip writers directly, platform stays free forever
4. **Admin curation** — featured posts on homepage surface quality, not virality
5. **Bilingual-natural** — Malay/English code-switching is normal, not an edge case

## UI Direction

- Light, minimal, editorial
- White/off-white base
- Typography-first: large readable serif for post body
- Single muted accent color (warm sand, sage, or slate blue — TBD)
- Minimal chrome: no sidebars cluttering reading
- Reading circle as subtle bottom drawer or floating panel on post view
- Writer profile: clean card, no vanity metrics up front

## Feature Set

### MVP

| Feature | Description |
|---------|-------------|
| User-authored posts | Rich text editor, draft/publish flow |
| Writer profiles | Bio, avatar, post list, follower count |
| Follow system | Follow writers, get personalized feed |
| Comments | Threaded comments on posts |
| Reading Circles | Create circle around post, invite members, private discussion |
| Reactions | Typed reactions: "Thought-provoking", "Beautifully written", "Changed my mind" |
| Admin curation | Admin marks posts as Featured, shown on homepage |
| Tips | Reader tips writer directly (Stripe integration) |

### V2 (post-MVP)

- Bilingual tags and language filter
- Weekly digest email
- "Rising Voices" — surfaces new writers gaining traction
- Writer analytics dashboard

## Data Model

### New tables needed

```
posts
  + user_id (FK → users)          # writers own posts

follows
  follower_id  FK → users
  following_id FK → users

comments
  id
  post_id      FK → posts
  user_id      FK → users
  parent_id    FK → comments (nullable, for threading)
  body
  timestamps

reactions
  id
  post_id      FK → posts
  user_id      FK → users
  type         ENUM: thought_provoking, beautifully_written, changed_my_mind
  timestamps

reading_circles
  id
  post_id      FK → posts
  creator_id   FK → users
  name
  timestamps

circle_members
  circle_id    FK → reading_circles
  user_id      FK → users
  joined_at

circle_messages
  id
  circle_id    FK → reading_circles
  user_id      FK → users
  body
  timestamps

tips
  id
  sender_id    FK → users
  receiver_id  FK → users
  post_id      FK → posts (nullable)
  amount_cents INT
  stripe_payment_intent_id
  timestamps
```

## Monetization

- Free platform, no subscription wall
- Readers tip writers directly via Stripe
- Platform takes no cut (MVP) — revisit in V2

## Publishing Model

- Open: anyone who signs up can write and publish
- Admin curates Featured tier on homepage
- No approval gate for publishing
