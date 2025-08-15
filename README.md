# NRW Noir — Dark Promoters (TCG prototype)

A lightweight, extensible **virtual trading card game** about organizing gothic/alt-scene events in NRW.  
Players compete for **Locations, Acts, Sponsors, Marketing** and **Audience**. All events happen **on the same day**. Sample cards live in `/cards` and a demo starter deck is in `/starter_decks`.

- **Stack:** PHP 8 + PostgreSQL + HTML/CSS + vanilla JS (no frameworks)
- **Cards as JSON files:** one file **per card** for drop-in extensibility
- **Multilingual from the start:** **English + German** (UI + cards)
- **Target:** fast prototyping, easy rules iteration, printable assets later

## Quick Start (dev)

1. PHP 8 + MySQL or MariaDB installed.
2. Run `php setup.php` and follow the prompts to create the database, apply migrations and write `config.php`. Alternatively, start a web server and visit `setup.php` for a graphical setup.
3. Serve repo root via `php -S localhost:8080` (or your web server).
4. Open `http://localhost:8080`.

For detailed onboarding steps see `docs/ONBOARDING.md`.

## Using Smarty templates

### Installation

Run `composer install` to fetch dependencies, including [Smarty](https://www.smarty.net/).

### Directory structure

- `/templates/` – view templates (`.tpl` files)
- `/templates_c/` – compiled templates, generated automatically

### Creating a new view

1. Create a template in `/templates`, e.g. `example.tpl`.
2. In your PHP script, load Smarty via `require __DIR__ . '/../src/bootstrap.php';`.
3. Assign variables with `$smarty->assign('name', $value);` and render with `$smarty->display('example.tpl');`.

## Repository Layout (proposed)

/api/          # PHP endpoints (state, actions, cards loader, SSE)
/cards/        # one JSON per card
/starter_decks/# sample starter decks
/i18n/        # ui.en.json, ui.de.json (UI strings)
  /public/       # cards.css, app.js
  index.php      # entry point
/docs/         # RULES.md (EN), RULES.de.md (DE), ONBOARDING.md, CONTRIBUTING.md, AGENTS.md
/migrations/   # SQL schema migrations
/rulesets/     # versioned ruleset JSON


## Card Files (one JSON per card)
- Minimal fields: `schema`, `type`, `id`, `name`, plus type-specific gameplay fields.
- Optional `style` block for frame/background/art/rarity.
- **Localization on cards:** either `name` & `text` as objects (`{"en":"…","de":"…"}`) **or** `name_key` + UI i18n. **We default to embedded objects** for cards.

**Example (Act):**

{
  "schema": 1,
  "type": "act",
  "id": "act_velvet_kain",
  "name": {"en": "Velvet Kain", "de": "Velvet Kain"},
  "cost": 12000,
  "audience_pct": 0.65,
  "style": {
    "frame_id": "frame_act_dark",
    "art_uri": "images/art/velvet_kain.webp",
    "rarity": "rare",
    "badge": {"en":"Headliner","de":"Headliner"}
  },
  "tags": ["core","F1","F2","L"]  // set/mode/size tags
}

Frames

Frames are reusable style presets (per type). Cards reference them via style.frame_id.

Example (frame JSON):

{
  "id": "frame_act_dark",
  "type": "act",
  "name": "Act – Dark",
  "frame_uri": "images/frames/act_dark_base.webp",
  "overlay_uri": "images/frames/act_gloss.webp",
  "bg_texture_uri": "images/textures/paper_dark.webp",
  "palette": {
    "title_bg": "#121118",
    "type_bg": "#0d0c12",
    "rules_bg": "#0a0a0f",
    "text": "#eae7ff",
    "muted": "#c7c3e6"
  },
  "icon_tint": "#b3a6ff",
  "title_font": "Cinzel",
  "rules_font": "Inter"
}

Backend: simple API

    POST /api/register.php → {username, password} → create user

    POST /api/login.php → {username, password} → returns session_token

    POST /api/logout.php → Authorization header → invalidate session

    GET /api/cards.php → loads/merges all card JSONs (recursive scan + APCu/ETag caching)

    GET /api/state.php?game_id=… → filtered game state (no peeking at other hands)

    POST /api/act.php → {action, payload, client_version} (server validates rules/phase)

    Optional: GET /api/stream.php?game_id=… → SSE push updates

    POST /api/new_match.php → create match and auto-join creator

Economy: Points & Packs

    - Users start with 1000 points—enough for a starter pack and some booster packs on day one. A daily top-up script (`tools/points_topup.php`) adds 100 points to every account.

    - Winning a game awards the `global.winReward` from the active ruleset (100 by default).
    - GET /api/market.php → current point balance and available packs (from `packs.json`).
      - `starter_pack` costs 100 points for 3 acts + 2 locations.
      - `pro_pack` costs 500 points for 5 acts + 1 sponsor.
    - POST /api/market.php → {pack_id} spends points and returns awarded card IDs.

Inventory & Deck Building APIs

    - GET /api/inventory.php → {points, inventory: [{card_id, qty}]}
    - POST /api/inventory.php → {card_id, qty} to set quantity or remove a card.
    - GET /api/decks.php → list decks, or `?id=` to fetch a single deck.
    - POST /api/decks.php → create deck {name, cards[{card_id, qty}]} (validated against inventory).
    - PUT /api/decks.php → update existing deck (inventory validation).
    - DELETE /api/decks.php?id=… → remove deck.

Frontend pages & example flow

    - `/public/register.php` – create account
    - `/public/login.php` – sign in
    - `/public/market.php` – buy packs
    - `/public/inventory.php` – view owned cards
    - `/public/deckbuilder.php` – assemble decks
      - `/index.php` – play a game
    - `/public/admin.php` – admin dashboard for user/points management (requires admin account)

  Example: register → log in → buy a starter pack → check new cards in inventory → build a deck → start a game.

Session tokens are stored in the `sessions` table with an expiry and returned by `/api/login.php`. Clients persist the token (e.g., `localStorage`) and send it on requests via `Authorization: Bearer <token>`. `/api/logout.php` deletes the token. Ensure migrations `001_initial.sql` and `002_add_password_hash.sql` are applied before using these endpoints.

Game state is stored as JSON in the DB for quick iteration (games.state_json, versioned).
Internationalization (EN/DE)

    UI strings: /i18n/ui.en.json, /i18n/ui.de.json

    Card strings: embedded per card (name/en,de, optional rules/en,de), fallback to EN.

    Locale detection: ?lang=en|de or navigator language, with user override (cookie).

UI i18n example:

{
  "app_title": "Dark Promoters",
  "phase_finance": "Finance",
  "phase_location": "Location",
  "phase_booking": "Booking",
  "phase_marketing": "Marketing",
  "phase_sabotage": "Sabotage",
  "event_phase": "Event (Scoring)"
}

Building/Running

    No build step required.

    Recommended: enable APCu for faster card manifests.

    Images: prefer WEBP for art; SVG for icons.

Contributing

    See AGENTS.md for roles, tasks, and acceptance criteria.
    See `docs/CONTRIBUTING.md` for guidelines.

    Lint JSON (UTF-8, no BOM). Keep schema = 1 for now.

    IDs: type_prefix_snake_case (e.g., act_velvet_kain, loc_matrix_bochum).

License

TBD (add SPDX in headers when chosen).
