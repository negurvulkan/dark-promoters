# Dark Promoters — Agent/Contributor Playbook

This document splits the work into clear roles so humans (and code agents) can collaborate without stepping on each other.

## Roles & Responsibilities

### 1) Rules Arbiter
- **Owns:** `docs/RULES.md` (EN), consistency across code.
- **Tasks:** Validate server phases, uniqueness of Acts/Locations, Sponsor exclusivity (+1 slot, +25%).
- **Acceptance:** All phases present; formulas match RULES.md; price band set in Marketing R1.

### 2) Card Schema Guardian
- **Owns:** card JSON schemas & loaders.
- **Tasks:** Enforce minimal fields (`schema,type,id,name`), type fields, style block, i18n objects.
- **Acceptance:** `api/cards.php` returns stable structure; invalid cards skipped with warnings.

### 3) Frontend Renderer
- **Owns:** `/public/index.html`, `cards.css`, `app.js`.
- **Tasks:** Render cards MTG-like using frames; EN/DE toggle; lazy-load art; drag onto table (later).
- **Acceptance:** Cards display with frame/bg/art/title/type bar/rules/rarity; locale switch updates text.

### 4) Backend API Engineer
- **Owns:** `/api/state.php`, `/api/act.php`, `/api/stream.php`.
- **Tasks:** Stateless endpoints with JSON in/out; SSE optional; version locking; transactions per action.
- **Acceptance:** `409` on version mismatch; all rule checks server-side; logs in state.

### 5) Database & State
- **Owns:** DB schema & migrations.
- **Tasks:** `users`, `sessions`, `games`, `game_players`; `games.state_json` + `version` counters.
- **Acceptance:** Transaction safety (`SELECT … FOR UPDATE`), `version++` per action.

### 6) Balancing Analyst
- **Owns:** numeric ranges per mode (Club/Party/1-Day/2-Day).
- **Tasks:** Maintain soft caps (Acts: first 3 full, rest 50%; Marketing caps 60%/90%); adjust ticket price defaults.
- **Acceptance:** Sample scenarios produce plausible P&L; no trivial runaway combos.

### 7) Localization Lead
- **Owns:** `/i18n/ui.en.json`, `/i18n/ui.de.json` and card text consistency.
- **Tasks:** Add missing keys; ensure fallback to EN; verify fonts/diacritics.
- **Acceptance:** Language toggle persists; card embedded i18n renders; no mixed-language artefacts.

### 8) QA / Test Harness
- **Owns:** simple PHP unit scripts or JS sanity checks.
- **Tasks:** Validate action guards per phase; golden tests for scoring math.
- **Acceptance:** CI green; sample replays deterministic.

### 9) Art & Frames
- **Owns:** `/frames/*.json`, `/images/*`.
- **Tasks:** Provide base frames per type (act/sponsor/location/marketing/sabotage); optimize WebP; SVG icons.
- **Acceptance:** Visual hierarchy clear; 2X assets crisp; file sizes reasonable.

### 10) Docs & Samples
- **Owns:** README.md, RULES.md; sample cards; starter decks.
- **Tasks:** Keep examples synchronized; provide small print-n-play set.

---

## Conventions

**IDs:** `type_prefix_snake_case`  
**Locales:** `{"en":"…","de":"…"}` on cards; UI in `/i18n`.  
**Sets & Modes:** tags: `["core","C","P","F1","F2","S/M/L/XL"]`.  
**Schema version:** `schema: 1` (increment if breaking change).  
**JSON style:** 2-space indent, UTF-8, LF.

---

## Milestones

**M0 – Clickable Table (prototype)**
- Load cards, render frames, EN/DE toggle.
- Hardcoded demo state; switch through phases.

**M1 – Rules-complete**
- Server state machine, actions, scoring math.
- Uniqueness & exclusivity enforced.

**M2 – Online Play**
- Sessions, join-code, SSE updates.
- Minimal lobby UI.

**M3 – Deckbuilding & Sets**
- Core + Mode packs, per-card JSON loader tooling.
- Basic trade/export (later).

---

## Acceptance Checklist (for PRs)
- [ ] Matches RULES.md (phases, exclusivity, formulas)
- [ ] JSON valid, required fields present
- [ ] EN/DE shown correctly
- [ ] No new globals; code documented
- [ ] `state.version` increments; conflict handled
- [ ] Screenshots/gif for UI PRs
