# Onboarding

Follow these steps to get a local instance of **Dark Promoters** running.

## Prerequisites
- PHP 8
- PostgreSQL

## Setup
1. Clone this repository.
2. Apply migrations in `migrations/` (`001_initial.sql` then `002_add_password_hash.sql`).
3. Create a `config.php` with your database credentials.
4. Serve the repo root via `php -S localhost:8080`.
5. Open `http://localhost:8080` in your browser.
6. Validate cards via `php api/cards.php` (should list demo cards without warnings).

## Next steps
- Review `docs/CONTRIBUTING.md` and `AGENTS.md` before opening a pull request.
- Use the sample cards under `cards/` and the demo deck under `starter_decks/` to explore the rules.
