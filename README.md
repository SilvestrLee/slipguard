# SlipGuard

## What This Is

SlipGuard is a betting decision intelligence platform. It evaluates betting slips, exposes unnecessary structural risk, and assists customers in constructing accumulators through deterministic, explainable planning. It does not predict match winners, promise safe bets, guarantee wins, or encourage more betting.

SlipGuard is not a bookmaker, tipster, prediction engine, or guaranteed-win system. See `CLAUDE.md`'s Product Identity and Locked Decisions sections, `docs/00-governance/VISION_AND_PRINCIPLES.md`, and `docs/00-governance/DECISION_LOG.md` (SD-001) for the full, current statement.

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

`docs/` holds SGOS, the durable repository context: governance, product definition, architecture, the risk-engine contract, UX rules, engineering standards, quality strategy, delivery roadmap, architecture decision records (`docs/adr/`), the standing constitution for each governance office (`docs/offices/`), and engineering validation/certification reports (`docs/engineering/`). See `docs/00-governance/SGOS_VERSION.md` for the current version and versioning policy, and `docs/README.md` for the entry point.

Documentation philosophy: **just-in-time**. Only populate a document when it supports the current milestone or preserves a decision that must remain stable across milestones. Do not read `docs/_legacy-bootstrap/` for current direction — it is a superseded draft, preserved only as history (see its own README).

This repository, not chat history, is the source of truth. If you are picking this project up cold — human or AI — start with `CLAUDE.md`, then `PROJECT.md`, then `docs/README.md`.

## Current Milestone

This section intentionally does not name a specific milestone — that information goes stale the moment a sprint closes and this file is easy to forget to update. For current state, always check:

- `TASKS.md` — the active milestone, in-progress/blocked/completed task lists, and the full roadmap sequence.
- `docs/00-governance/REPOSITORY_STATE.md` — a dated snapshot of repository status, architecture stability, and current focus.
- `docs/00-governance/DECISION_LOG.md` — the full, dated history of how the project got here.

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

Check `TASKS.md`'s Active Milestone section for the current task and its acceptance criteria.

## Working With Claude Code

`CLAUDE.md` defines the product identity, locked decisions, and working rules for AI-assisted development in this repository — read it before making changes. Project-specific slash commands live in `.claude/commands/` (`start-task`, `finish-task`, `audit-context`).
