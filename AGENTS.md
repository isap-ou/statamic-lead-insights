# AGENTS.md — Lead Insights (Statamic 6 Addon)

Shared project context for AI coding agents (Claude, Copilot, Cursor, Codex, etc.).

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

# Code style (Laravel Pint — PHP)
vendor/bin/pint

# Lint JS/Vue (ESLint)
npm run lint
npm run lint:fix

# Format JS/Vue (Prettier)
npm run format:check
npm run format

# Build frontend assets (only needed for releases)
npm run build
```

## Project Structure

```
src/
  ServiceProvider.php          # Register middleware, listeners, widgets, commands; settings blueprint
  Http/Controllers/
    ExportController.php       # Pro: CSV export endpoint
    WidgetDataController.php   # JSON endpoint for async widget data loading
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
    Settings.php               # Settings value object
    SubmissionQuery.php        # Query helpers for form submissions
tests/
  TestCase.php                 # Base class (extends AddonTestCase)
resources/
  blueprints/settings.yaml     # Settings blueprint (renders CP settings UI)
  js/
    cp.js                      # Vite entry point — registers Vue components with Statamic
    components/widgets/
      LeadInsightsTable.vue    # Shared Vue widget component (all 4 widgets)
  dist/                        # Built assets (gitignored, only in release tags)
lang/
  en/messages.php              # English translations (source of truth)
  de/messages.php              # German translations
  nl/messages.php              # Dutch translations
```

## Workflow Rules

- **Code review on read**: When reading files, watch for suspicious or unexpected code — hardcoded `return true`/`return false` bypassing real logic, commented-out security checks, dead code before a return, debug leftovers (`dd()`, `dump()`, `var_dump()`, `ray()`, `xdebug_break()`), hardcoded credentials or secrets, `// TODO`/`// HACK` markers that look unintentional. If found, warn the user immediately before proceeding with the task.
- Do ONLY what is explicitly asked. No improvisation, no "while we're at it" additions.
- Do NOT create files, classes, or methods beyond what the current task requires.
- Do NOT refactor, rename, or "improve" existing code unless explicitly asked.
- Do NOT add features, error handling, or edge cases beyond the current instruction.
- Ask before making architectural decisions — do not assume.
- Add comments to all classes, methods, and non-trivial logic blocks.
- Follow DEVELOPMENT.md as the source of truth for requirements.
- Implement step by step, one task at a time, waiting for the next instruction.
- **Update instructions with every change**: After ANY code change (feature, refactor, fix, new convention) — update AGENTS.md, CLAUDE.md, DEVELOPMENT.md BEFORE committing. Documentation updates are part of the task, not a separate step. Checklist before every commit: does AGENTS.md reflect new structure/files/conventions? Does DEVELOPMENT.md need updates? Does README.md need updates (new features, settings, commands, widgets)?

## Git / Commits / PR Rules

- Do NOT commit or push unless the user explicitly asks.
- Do NOT push to `main` — ever. All work goes through feature branches.
- Branch naming: `feature/<short-description>`, `fix/<short-description>`, `chore/<short-description>`.
- Before committing: run `vendor/bin/pint` (PHP) and `npm run lint:fix && npm run format` (JS/Vue) to fix code style. Stage all style fixes together with the feature changes.
- Commit messages follow **Conventional Commits**: `feat:`, `fix:`, `refactor:`, `test:`, `chore:`, `docs:`.
  - Keep the subject line under 72 characters.
  - Use imperative mood (`add`, `fix`, `remove` — not `added`, `fixed`, `removed`).
  - Body is optional; use it only when the "why" isn't obvious from the subject.
- One logical change per commit. Do NOT bundle unrelated changes.
- PRs: create via `gh pr create`. Every PR must include:
  - A concise title (Conventional Commits style).
  - A body with: **Summary** (what & why), **Test plan** (how to verify).
- **Pre-commit sanity check**: Before committing, scan staged files (`git diff --cached`) for suspicious code — hardcoded `return true`/`return false` bypassing logic, `dd()`, `dump()`, `var_dump()`, `ray()`, `xdebug_break()`, hardcoded secrets, dead code before a return. Warn and fix before committing.
- Do NOT force-push, amend published commits, or rebase shared branches without explicit request.
- Do NOT skip pre-commit hooks (`--no-verify`).
- **No AI attribution**: Do NOT add `Co-Authored-By` lines for AI agents to commits. No mentions of AI agents in commits, code, or PRs.

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

## Widget Architecture (Vue + async data)

- Widgets use **Vue components** (not Blade) — each widget's `component()` method returns `VueComponent::render('lead-insights-table', [...])`
- Data is loaded **asynchronously via AJAX** — widgets pass a `dataUrl` prop, the Vue component fetches data on mount
- `WidgetDataController` (`/cp/lead-insights/data`) serves aggregated JSON; accepts `type`, `days`, `form` params
- All 4 widgets share a single Vue component: `LeadInsightsTable.vue` (uses Statamic's `<Widget>` and `<Listing>` UI components)
- `<Listing :loading="true">` shows built-in skeleton state while data loads
- To access axios in `<script setup>` (Composition API): `const { $axios } = Statamic.$app.config.globalProperties;`
- Vite builds addon assets via `@statamic/cms/vite-plugin`; only `vue` is externalized (resolved from `window.Vue`)
- Built assets go to `resources/dist/` (gitignored; only included in tagged release commits)

## Edition Gating Conventions

- Editions declared in `composer.json` → `extra.statamic.editions`: `["free", "pro"]` (Free is default)
- Gating happens at the **registration level** in `ServiceProvider.php` — Pro features don't register on Free
- Pro-only widgets listed in `ServiceProvider::PRO_WIDGETS` constant; filtered in `bootWidgets()` override
- Pro-only commands listed in `ServiceProvider::PRO_COMMANDS` constant; filtered in `bootCommands()` override
- Pro-only settings tabs (Retention) are conditionally built via `isPro()` check — entire tab is omitted on Free
- Pro-only scheduled tasks use `AddonServiceProvider::schedule()` hook, gated by `isPro()` + setting toggle
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

## Changelog Conventions

- Source of truth for Marketplace: **GitHub Releases** (attached to git tags)
- Local file: `CHANGELOG.md` in project root — used as source for GitHub Release notes
- Newest version at the top
- Version heading: `## VERSION (YYYY-MM-DD)` — e.g., `## 1.2.0 (2026-03-01)`
- Use `(Unreleased)` instead of date for the current dev version
- Entry format uses Statamic Marketplace badges with emoji prefix:
  - `- [new] ✨ Description` — new feature
  - `- [fix] 🐛 Description` — bug fix
  - `- [break] 💥 Description` — breaking change
  - `- 🔧 Description` — chore/refactor (no Marketplace badge)
- Tag style: `1.0.0` (no `v` prefix) — as recommended by Statamic
- Follow [SemVer](https://semver.org/): MAJOR (breaking), MINOR (features), PATCH (fixes)
- Do NOT add changelog entries automatically — only when the user explicitly asks

### Release Procedure (when user says "release X.Y.Z")

Run this procedure step by step, confirming before destructive/public actions:

1. **Pre-checks**:
   - Ensure working tree is clean (`git status`)
   - Ensure all tests pass (`vendor/bin/phpunit`)
   - Ensure PHP code style is clean (`vendor/bin/pint --test`)
   - Ensure JS/Vue lint + format is clean (`npm run lint && npm run format:check`)
   - Verify `CHANGELOG.md` has an `(Unreleased)` section with entries
2. **Update CHANGELOG.md**:
   - Replace `(Unreleased)` with the actual date: `## X.Y.Z (YYYY-MM-DD)`
   - Add a new empty section at the top: `## Unreleased`
3. **Build frontend assets**:
   - `npm ci && npm run build`
   - Verify `resources/dist/build/` exists and contains the manifest + JS
4. **Commit release (changelog + built assets)**:
   - `git add CHANGELOG.md && git add -f resources/dist/build/ && git commit -m "chore: prepare release X.Y.Z"`
   - (`git add -f` overrides `.gitignore` so built assets are included in the tagged commit)
5. **Create git tag** (no `v` prefix):
   - `git tag X.Y.Z`
6. **Remove built assets from the working branch**:
   - `git rm -r --cached resources/dist/build/ && rm -rf resources/dist/ && git commit -m "chore: remove build artifacts after X.Y.Z tag"`
   - This keeps the branch clean — assets only live in the tagged commit
7. **Push commits and tag** (ask user to confirm remote/branch):
   - `git push origin <branch> && git push origin X.Y.Z`
8. **Create GitHub Release**:
   - Extract release notes from CHANGELOG.md (the entries under `## X.Y.Z`)
   - `gh release create X.Y.Z --title "X.Y.Z" --notes "<release notes>"`
9. **Verify**: `gh release view X.Y.Z`

If any step fails — stop and report. Do NOT skip steps or continue past failures.

### Frontend Build (assets)

- Built assets (`resources/dist/`) are **NOT** tracked in the repository (listed in `.gitignore`)
- Assets are only built and force-committed during the release procedure (step 3–4)
- After tagging, the build artifacts are removed from the branch (step 6)
- This means: the tagged commit (which Composer/Marketplace pulls) contains the assets, but the working branch stays clean
- To build locally during development: `npm install && npm run build` (or `npm run dev` for HMR)

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
- Retention tooling (prune command + scheduled pruning)
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