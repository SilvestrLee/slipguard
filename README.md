# SlipGuard

## What This Is

SlipGuard is a betting risk intelligence platform. It evaluates betting slips and exposes unnecessary structural risk. It does not predict match winners, provide tips, promise safe bets, or encourage more betting.

SlipGuard is:

- A betting risk intelligence platform.
- A slip analysis tool.
- A decision-support system.

SlipGuard is not a bookmaker, tipster, prediction engine, or guaranteed-win system. See `docs/00-governance/VISION_AND_PRINCIPLES.md` for the full statement.

## Technology Stack

- Laravel 13, PHP 8.3+.
- Customer-facing UI: Blade and Livewire.
- Internal operations: Filament.
- Testing: Pest.
- Frontend build: Vite + Tailwind CSS.
- Database: SQLite locally (see `.env`); do not change database technology without an ADR.

## Repository Structure

```
app/            Application code (Models, Console, Providers, Services, ...)
config/         Laravel configuration
database/       Migrations, factories, seeders
docs/           SGOS — the authoritative knowledge base (see below)
public/         Web root and built assets
resources/      Views, CSS, JS source
routes/         Route definitions
tests/          Pest test suite
CLAUDE.md       Instructions for AI coding agents working in this repo
PROJECT.md      Current project status at a glance
TASKS.md        Active milestone task list
CHANGELOG.md    Running log of what has shipped
```

## SGOS — SlipGuard Operating System

`docs/` holds SGOS, the durable repository context: governance, product definition, architecture, the risk-engine contract, UX rules, engineering standards, quality strategy, delivery roadmap, and architecture decision records (`docs/adr/`). See `docs/00-governance/SGOS_VERSION.md` for the current version and versioning policy, and `docs/README.md` for the entry point.

Documentation philosophy: **just-in-time**. Only populate a document when it supports the current milestone or preserves a decision that must remain stable across milestones. Do not read `docs/_legacy-bootstrap/` for current direction — it is a superseded draft, preserved only as history (see its own README).

This repository, not chat history, is the source of truth. If you are picking this project up cold — human or AI — start with `CLAUDE.md`, then `PROJECT.md`, then `docs/README.md`.

## Current Milestone

**Production Foundation: Certified (2026-07-25).** The deterministic engine, persistence boundary, and architectural layering have completed engineering validation (`docs/engineering/PRODUCTION_FOUNDATION_CERTIFICATE.md`, recommendation `READY WITH OBSERVATIONS`). Platform Engineering is closed; active work is now Customer Experience Engineering — see `TASKS.md`.

**E-02 — Customer Foundation.** See `TASKS.md` for the active task list and `docs/08-operations/DELIVERY_ROADMAP.md` for the full roadmap:

E-01 Engineering Initialization (complete) → **E-02 Customer Foundation** → E-03 Manual Slip Capture → E-04 Deterministic Risk Analysis (blocked on Data Science formula approval) → E-05 Risk Report → E-06 History and Journal → E-07 Public Trust Website → E-08 MVP Hardening → Release 1.0.

## Running Locally

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run dev
```

In a second terminal:

```bash
php artisan serve
```

Run tests with:

```bash
php artisan test
```

## Branch Strategy

- `main` — released/stable.
- `develop` — active development; branch from and merge back into this for day-to-day work.

## Where to Begin

Check `TASKS.md`'s "Ready" list for the current task. As of this writing, the next implementation work (customer authentication) depends on `docs/adr/ADR-005-AUTHENTICATION-STRATEGY.md` being approved by the founder — it currently carries a recommendation, not a decision. Don't start building against it until its status reads `Accepted`.

## Working With Claude Code

`CLAUDE.md` defines the product identity, locked decisions, and working rules for AI-assisted development in this repository — read it before making changes. Project-specific slash commands live in `.claude/commands/` (`start-task`, `finish-task`, `audit-context`).
