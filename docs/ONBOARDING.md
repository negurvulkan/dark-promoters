# Onboarding

Follow these steps to get a local instance of **Dark Promoters** running.

## Prerequisites
- PHP 8
- MySQL or MariaDB

## Setup
1. Clone this repository.
2. Run `php setup.php` and follow the prompts to create the database, apply migrations and write `config.php`. Alternatively, serve the repo via a web server and visit `public/setup.php` for a graphical setup.
3. Serve the repo root via `php -S localhost:8080`.
4. Open `http://localhost:8080` in your browser.
5. Validate cards via `php api/cards.php` (should list demo cards without warnings).

## Next steps
- Review `docs/CONTRIBUTING.md` and `AGENTS.md` before opening a pull request.
- Use the sample cards under `cards/` and the demo deck under `starter_decks/` to explore the rules.
