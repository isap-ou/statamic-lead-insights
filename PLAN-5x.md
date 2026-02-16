# Plan: Statamic 5 support (branch `5.x`, releases `1.x`)

Goal: create `5.x` branch from `6.x` and adapt all version-dependent files for Statamic 5 / Vue 2 / Tailwind 3.

## Files to modify (version-dependent)

### 1. `composer.json`
- PHP: `^8.2` → `^8.1|^8.2`
- `statamic/cms`: `^6.0` → `^5.0`
- `orchestra/testbench`: adjust to match Statamic 5 requirements
- Remove any Statamic 6-only dev dependencies if present

### 2. `package.json`
- Add explicit `vue: ^2.7.14`
- `tailwindcss: ^3.3`, `postcss`, `autoprefixer`
- `vite: ^4.5`
- `laravel-vite-plugin: ^0.7.2`
- `@vitejs/plugin-vue2` instead of `@statamic/cms/vite-plugin`
- Add `tailwind.config.js` and `postcss.config.js`

### 3. `vite.config.js`
- Replace `@statamic/cms/vite-plugin` → `@vitejs/plugin-vue2`
- Remove `statamic()` plugin call
- Add standard vue2 + laravel-vite-plugin config

### 4. `src/ServiceProvider.php`
- Remove `$this->registerSettingsBlueprint()` (does not exist in Statamic 5)
- Adapt settings loading (no `Addon::settings()` in v5)
- Keep all shared API: `$middlewareGroups`, `$widgets`, `$commands`, `$listen`, `schedule()`, `bootWidgets()`, `bootCommands()`, permissions

### 5. `src/Support/Settings.php`
- Rewrite `fromAddon()`: cannot use `$addon->settings()->all()` (Statamic 5 has no SettingsRepository)
- Alternative: read from YAML file directly or use Laravel config
- `edition()` method is available in both versions — safe to keep

### 6. `src/Widgets/*.php` (4 files)
- `LeadsBySourceWidget.php`
- `LeadsByCampaignWidget.php`
- `LeadsByFormWidget.php`
- `FormSourceWidget.php`

Changes:
- Remove `use Statamic\Widgets\VueComponent` (does not exist in v5)
- Replace `component()` method → `html()` method
- Return Blade view instead of `VueComponent::render()`

### 7. `resources/js/components/widgets/LeadInsightsTable.vue`
- Rewrite from Vue 3 `<script setup>` Composition API → Vue 2 Options API
- Replace `import { ref, computed, onMounted } from 'vue'` → `data()`, `computed`, `mounted()`
- Adapt Tailwind 4 classes → Tailwind 3 if needed

### 8. `resources/js/cp.js`
- Verify `Statamic.$components.register()` works the same (likely no change needed)
- Check imports

### 9. NEW: `resources/views/widgets/*.blade.php` (4 files)
Create Blade templates for each widget (required for `html()` method in Statamic 5):
- `leads-by-source.blade.php`
- `leads-by-campaign.blade.php`
- `leads-by-form.blade.php`
- `form-source.blade.php`

Each template renders a `<div>` with props passed as data attributes or inline JSON for the Vue 2 component.

## Files that stay identical (no changes needed)
- `src/Middleware/CaptureAttribution.php`
- `src/Listeners/EnrichFormSubmission.php`
- `src/Commands/PruneCommand.php`
- `src/Http/Controllers/WidgetDataController.php`
- `src/Http/Controllers/ExportController.php`
- `src/Support/AttributionPayload.php`
- `src/Support/SubmissionQuery.php`
- `tests/**`
- `lang/**`
- `AGENTS.md`, `CLAUDE.md`, `DEVELOPMENT.md`, `README.md`

## Execution order

1. Create `5.x` branch from `6.x`
2. `composer.json` + `package.json` — foundation deps
3. `vite.config.js` + add `tailwind.config.js` / `postcss.config.js`
4. `src/Support/Settings.php` — settings loading without v6 API
5. `src/ServiceProvider.php` — remove v6-only calls
6. `src/Widgets/*.php` (4 files) — switch to `html()` + Blade
7. Create `resources/views/widgets/*.blade.php` (4 files)
8. `resources/js/components/widgets/LeadInsightsTable.vue` — Vue 2 rewrite
9. `resources/js/cp.js` — verify / adjust
10. `npm install && npm run build` — verify frontend builds
11. `vendor/bin/phpunit` — verify all tests pass
12. Tag `1.0.0` when ready
