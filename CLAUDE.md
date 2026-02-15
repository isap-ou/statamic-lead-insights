
# CLAUDE.md — Lead Insights (Statamic 6 Addon)

## Workflow Rules

- Do ONLY what is explicitly asked. No improvisation, no "while we're at it" additions.
- Do NOT create files, classes, or methods beyond what the current task requires.
- Do NOT refactor, rename, or "improve" existing code unless explicitly asked.
- Do NOT add features, error handling, or edge cases beyond the current instruction.
- Ask before making architectural decisions — do not assume.
- Add comments to all classes, methods, and non-trivial logic blocks.
- Follow DEVELOPMENT.md as the source of truth for requirements.
- Implement step by step, one task at a time, waiting for the next instruction.
- When requirements change or new ones appear, update the relevant MD files (DEVELOPMENT.md, PLAN.md, CLAUDE.md) to keep them in sync.

## Git / Commits / PR Rules

- Do NOT commit or push unless the user explicitly asks.
- Do NOT push to `main` — ever. All work goes through feature branches.
- Branch naming: `feature/<short-description>`, `fix/<short-description>`, `chore/<short-description>`.
- Before committing: run `vendor/bin/pint` to fix code style. Stage the pint changes together with the feature changes.
- Commit messages follow **Conventional Commits**: `feat:`, `fix:`, `refactor:`, `test:`, `chore:`, `docs:`.
  - Keep the subject line under 72 characters.
  - Use imperative mood (`add`, `fix`, `remove` — not `added`, `fixed`, `removed`).
  - Body is optional; use it only when the "why" isn't obvious from the subject.
- One logical change per commit. Do NOT bundle unrelated changes.
- PRs: create via `gh pr create`. Every PR must include:
  - A concise title (Conventional Commits style).
  - A body with: **Summary** (what & why), **Test plan** (how to verify).
- Do NOT force-push, amend published commits, or rebase shared branches without explicit request.
- Do NOT skip pre-commit hooks (`--no-verify`).
- **No Claude attribution**: Do NOT add `Co-Authored-By: Claude` lines to commits. No mentions of Claude in commits, code, or PRs.

## Testing Rules

- No tests for the sake of tests. Every test must verify meaningful behavior.
- Test what the code DOES, not how it's structured internally.
- Each component must have tests covering its critical paths:
  - **Middleware**: consent check → cookie set/not set, UTM capture, landing URL capture without consent
  - **Listener**: payload attached to submission with/without consent, does not break existing submissions
  - **Widgets**: correct aggregation counts for seeded data, date range filtering, `(none)` bucketing
  - **PruneCommand**: old attribution stripped, recent data untouched
- Do NOT write tests for getters, setters, constructors, or trivial logic.
- Do NOT create test helpers or factories unless they are reused across multiple tests.
- Use Statamic's `AddonTestCase` as the base. Follow existing test patterns in `tests/TestCase.php`.
- PHPUnit 12: use `#[Test]` attribute (`use PHPUnit\Framework\Attributes\Test`). Docblock `@test` annotations are NOT supported in PHPUnit 12.

## Quick Reference

- **Package**: `isapp/statamic-lead-insights`
- **Namespace**: `Isapp\LeadInsights\`
- **Tests namespace**: `Isapp\LeadInsights\Tests\`
- **Entry point**: `src/ServiceProvider.php` (extends `Statamic\Providers\AddonServiceProvider`)
- **Full spec**: see `DEVELOPMENT.md` in this directory

## Stack

- Statamic 6 / Laravel 12+ / PHP 8.2+
- Orchestra Testbench 10.x for testing
- CP frontend: Vue 3 + Tailwind CSS 4 (from Statamic core)
- Flat-file storage (Stache) by default; must be storage-driver agnostic

## Commands

```bash
# Run all tests (from addon root)
vendor/bin/phpunit

# Run a single test
vendor/bin/phpunit tests/SomeTest.php

# Run with filter
vendor/bin/phpunit --filter=test_method_name

# Code style (Laravel Pint)
vendor/bin/pint
```

## Project Structure

```
src/
  ServiceProvider.php          # Register middleware, listeners, widgets, commands; settings blueprint
  Middleware/
    CaptureAttribution.php     # Frontend middleware: capture UTM/referrer/landing into cookie/session
  Listeners/
    EnrichFormSubmission.php   # Inject attribution payload into form submission at submit time
  Widgets/
    LeadsBySourceWidget.php    # Free: group by utm_source
    LeadsByCampaignWidget.php  # Pro: group by utm_campaign
    LeadsByFormWidget.php      # Pro: group by form handle
    FormSourceWidget.php       # Pro: source breakdown for a selected form
  Commands/
    PruneCommand.php           # Pro: php artisan lead-insights:prune
  Support/
    AttributionPayload.php     # DTO / value object for the attribution data
tests/
  TestCase.php                 # Base class (extends AddonTestCase)
resources/
  blueprints/settings.yaml     # Settings blueprint (renders CP settings UI)
  views/widgets/               # Blade/Vue widget views
```

## Addon Conventions (Statamic 6)

- Register middleware via `protected $middlewareGroups` in ServiceProvider
- Register widgets via `protected $widgets` in ServiceProvider
- Register commands via `protected $commands` in ServiceProvider
- Register event listeners via `protected $listen` or `$subscribe` in ServiceProvider
- Settings UI: define blueprint via `resources/blueprints/settings.yaml` or `registerSettingsBlueprint()` in `bootAddon()`
- Access settings in code: `$this->addon->setting('key')` or `$addon->settings()->all()`
- Settings stored automatically as flat-file YAML in `resources/addons/{slug}.yaml`
- Use `declare(strict_types=1)` in all PHP files
- Code style: Laravel Pint (run `vendor/bin/pint` before committing)
- Pro gating: use Statamic's standard addon licensing (`$this->addon->edition()`)

## Edition Gating Conventions

- Editions declared in `composer.json` → `extra.statamic.editions`: `["free", "pro"]` (Free is default)
- Gating happens at the **registration level** in `ServiceProvider.php` — Pro features don't register on Free
- Pro-only widgets listed in `ServiceProvider::PRO_WIDGETS` constant; filtered in `bootWidgets()` override
- Pro-only commands listed in `ServiceProvider::PRO_COMMANDS` constant; filtered in `bootCommands()` override
- Pro-only routes and permissions are conditionally registered in `bootAddon()` via `isPro()` check
- Controllers for Pro features include a defense-in-depth `abort_unless(edition === 'pro', 403)` check
- Tests run with edition set to `pro` in `TestCase::defineEnvironment()` so all Pro features are exercised
- When adding a new Pro-only feature: add its class to the appropriate constant, gate its registration, and add abort guard if it has a controller

## Translations (i18n)

- Namespace: `statamic-lead-insights` (loaded automatically by `AddonServiceProvider::bootTranslations()` from `lang/`)
- Key format: `__('statamic-lead-insights::messages.section.key')`
- Translation files: `lang/{en,de,nl}/messages.php`
- English (`lang/en/messages.php`) is the source of truth — add new keys there first, then to de/nl
- Use dot-separated keys grouped by section: `settings.*`, `permissions.*`, `widgets.*`, `commands.*`, `export.*`
- Placeholders use Laravel syntax: `:placeholder` (e.g., `'Pruned :count submission(s)'`)
- Artisan command `$description` stays in English (class property, cannot call `__()`) — the translation key exists for other consumers
- All CP-facing display text, instructions, permission labels, widget titles, and Blade strings must use translation keys

## Goal

Build a Statamic 6 addon that:
1. Captures marketing attribution (UTMs, referrer, landing URL, timestamps) on frontend requests
2. Attaches attribution payload to Statamic form submissions under `__attribution` key
3. Visualizes lead sources via CP dashboard widgets

Ships as **Free + Pro**. **EU-first** by default (consent required, no IP/UA, minimal data).

## Editions

### Free
- Capture UTMs + landing URL + timestamps (last-touch only)
- Attach `__attribution` payload to form submissions
- Consent gating ON by default
- 1 widget: Leads by Source

### Pro (everything in Free, plus)
- 4 widgets total (Source, Campaign, Form, Form→Source breakdown)
- Date range presets (7/30/90) + filters
- CSV export of aggregated tables
- Retention tooling (prune command)
- First-touch + last-touch attribution

## EU/GDPR Defaults (non-negotiable)

- `consent_required = true` — no cookie/UTM storage without consent
- Do NOT store: IP, user-agent, fingerprint IDs
- Without consent: only `landing_url` allowed (configurable)
- Referrer without consent: OFF by default
- Retention: 365 days default + prune command

## Data Model

Attribution payload (`__attribution`, v1):
- `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content` (all string|null)
- `referrer` (string|null)
- `landing_url` (string|null)
- `first_seen_at` (ISO8601|null, Pro)
- `last_seen_at` (ISO8601)
- `attribution_version` (int: 1)

Reporting buckets: missing source/campaign → `(none)`

## Non-goals

- No CRM/webhook/Zapier integrations
- No ad platform APIs or spend import
- No CPL/ROI calculations
- No CP pages beyond dashboard widgets
- No fingerprinting or cross-site tracking

## Documentation-first mode

When answering questions about Statamic / PHPUnit / PHP:
1) Prefer official documentation and/or source code as the primary authority.
2) Always state the assumed version (Statamic v?, PHPUnit v?). If unknown, ask or make a clearly labeled assumption.
3) Never invent API, config keys, tags, methods, or CLI flags. If not confirmed in docs/source, say so and propose how to verify.
4) Provide:
  - The relevant doc section name (and link if available)
  - A minimal working example adapted to this codebase
  - Common pitfalls / edge cases
5) If multiple approaches exist, compare trade-offs briefly and pick the safest default for production.

Output format:
- Recommended approach
- What the docs say (short)
- Minimal example
- Pitfalls / version notes
- How to verify quickly