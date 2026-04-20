# Simple Polls - Contensio Plugin

Create polls with multiple options, enforce one vote per IP or logged-in user, and display live results as an animated bar chart - all without a page reload.

---

## Features

- **Unlimited options** per poll (2–20)
- **One vote per user** - logged-in users tracked by user ID; guests tracked by IP address
- **Live bar chart** - results update instantly after voting via Alpine.js + `fetch`, no page reload
- **Flexible result visibility** - show results always, only after voting, or only after the poll closes
- **Auto-close** - set an optional end date/time; the poll stops accepting votes automatically
- **Guest voting toggle** - optionally restrict voting to logged-in users only
- **Admin results view** - full breakdown with vote counts and percentages
- **Embeddable** - drop any poll into any page, post, or widget area with one line
- **Status control** - Draft / Active / Closed

---

## How it works

1. An admin creates a poll in **Tools → Polls → New Poll**, adds options, and sets the status to **Active**.
2. The poll is embedded anywhere in a theme using `@include('polls::partials.poll', ['pollId' => 1])`.
3. A visitor selects an option and clicks **Vote**. The vote is sent via `fetch` to `/polls/{id}/vote`.
4. The voting form is replaced by a bar chart showing results in real time.
5. If the visitor revisits the page, the bar chart is shown immediately (the plugin checks their IP / user ID on page load).

### Vote deduplication

- **Logged-in users** - enforced by a unique database constraint on `(poll_id, user_id)`. A second request hits the constraint and returns an error.
- **Guests** - checked by IP address before inserting. One IP = one vote per poll.
- A race condition between the IP check and the insert is handled by catching the database unique constraint exception.

---

## Installation

### Via admin panel

Go to **Plugins** in your Contensio admin, find **Simple Polls**, and click **Install**.

### Via Composer

```bash
composer require contensio/plugin-polls
```

The plugin is auto-discovered. Go to **Plugins** in the admin and enable it. Migrations run automatically on first enable.

---

## Embedding a poll

```blade
@include('polls::partials.poll', ['pollId' => 1])
```

Replace `1` with the poll's ID, which is shown on the polls list screen.

The embedded widget is self-contained - it includes its own Alpine.js component and handles voting, error states, and result rendering without any additional setup.

**Requirements for the embed to work:**

- Alpine.js must be loaded on the page (included in all Contensio default themes).
- A `<meta name="csrf-token">` tag must be present in the page `<head>` (included in all Contensio default themes).

---

## Admin

### Poll list (`/account/polls`)

Shows all polls with their status, total vote count, result visibility setting, and expiry date. From here you can create, edit, view results, or delete a poll.

The embed snippet is shown inline on the list for quick copy-paste.

### Create / edit form

| Field | Description |
|-------|-------------|
| **Question** | The poll question (up to 500 characters) |
| **Options** | Add or remove options dynamically; minimum 2, maximum 20 |
| **Status** | `Draft` (hidden), `Active` (accepting votes), `Closed` (no more votes) |
| **Show results** | `Always`, `After voting`, or `After closing` |
| **Allow guests** | Whether non-logged-in visitors can vote (one vote per IP) |
| **Auto-close at** | Optional date/time to automatically stop accepting votes |

### Results view (`/account/polls/{id}/results`)

Displays a bar chart with vote count and percentage per option, total votes, and current status.

---

## Routes

| Method | URL | Description |
|--------|-----|-------------|
| `GET` | `/account/polls` | Admin poll list |
| `GET` | `/account/polls/create` | New poll form |
| `POST` | `/account/polls` | Create poll |
| `GET` | `/account/polls/{id}/edit` | Edit poll |
| `PUT` | `/account/polls/{id}` | Update poll |
| `DELETE` | `/account/polls/{id}` | Delete poll + all votes |
| `GET` | `/account/polls/{id}/results` | Admin results view |
| `POST` | `/polls/{id}/vote` | Cast a vote (JSON) |
| `GET` | `/polls/{id}/results` | Fetch current results (JSON) |

---

## Database

Three tables created by the migration:

### `polls`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `question` | varchar(500) | The poll question |
| `status` | enum | `draft`, `active`, `closed` |
| `show_results` | enum | `always`, `after_vote`, `after_close` |
| `allow_guests` | boolean | Whether guests can vote |
| `ends_at` | timestamp | Auto-close date/time (nullable) |
| `created_at` / `updated_at` | timestamp | |

### `poll_options`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `poll_id` | bigint | FK → `polls.id` (cascade delete) |
| `label` | varchar(300) | Option text |
| `sort_order` | smallint | Display order |

### `poll_votes`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `poll_id` | bigint | FK → `polls.id` (cascade delete) |
| `option_id` | bigint | FK → `poll_options.id` (cascade delete) |
| `user_id` | bigint | Logged-in user ID (nullable) |
| `ip_address` | varchar(45) | Voter IP address |
| `created_at` | timestamp | Vote time |

Unique constraint on `(poll_id, user_id)` prevents duplicate votes from logged-in users at the database level.

---

## Requirements

- PHP 8.2+
- Contensio 2.0+
- Alpine.js (included in all Contensio default themes)

---

## License

AGPL-3.0-or-later - see [LICENSE](LICENSE).
