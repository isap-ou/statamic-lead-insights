# Lead Insights — Implementation Plan

## Status legend
- [ ] not started
- [x] done
- [~] in progress

---

## Step 1. Settings Blueprint
- [x] Define settings via `registerSettingsBlueprint()` in ServiceProvider
- [x] Tabs: General, Consent/GDPR, Reporting, Retention
- [x] Defaults per spec (consent ON, referrer without consent OFF, etc.)

## Step 2. AttributionPayload DTO
- [x] Create `src/Support/AttributionPayload.php`
- [x] Fields: utm_source, utm_medium, utm_campaign, utm_term, utm_content, referrer, landing_url, first_seen_at, last_seen_at, attribution_version
- [x] Methods: fromArray(), toArray(), mergeLastTouch(), withFirstTouch(), hasUtm(), normalize()

## Step 3. CaptureAttribution Middleware
- [x] Create `src/Middleware/CaptureAttribution.php`
- [x] Read UTMs from query params, Referer from header, current URL for landing
- [x] Check consent (cookie name/value from addon settings)
- [x] Without consent: no cookie set, no UTM stored
- [x] With consent: write/update attribution cookie (SameSite=Lax, Secure, HttpOnly, configurable TTL)
- [x] Last-touch: always update when UTMs present
- [x] First-touch (Pro): set via withFirstTouch() on mergeLastTouch()
- [x] Register middleware in ServiceProvider (`web` group, frontend only)

## Step 4. EnrichFormSubmission Listener
- [x] Create `src/Listeners/EnrichFormSubmission.php`
- [x] Listen to `SubmissionCreating` event (before persistence)
- [x] Read attribution from cookie via `Cookie::get()`
- [x] Without consent (no cookie): attach only landing_url/last_seen_at if `storeLandingWithoutConsent` enabled
- [x] With consent (cookie present): attach full payload under configured attribution key
- [x] Uses `$submission->set()` — storage-driver agnostic
- [x] Register listener in ServiceProvider via `$listen`

## Step 5. Widgets
- [x] Create `src/Support/SubmissionQuery.php` (shared aggregation logic)
- [x] Create `src/Widgets/LeadsBySourceWidget.php` (Free)
- [x] Create `src/Widgets/LeadsByCampaignWidget.php` (Pro)
- [x] Create `src/Widgets/LeadsByFormWidget.php` (Pro)
- [x] Create `src/Widgets/FormSourceWidget.php` (Pro, accepts form handle from widget config)
- [x] Aggregate from submissions on demand (MVP)
- [x] Bucketing: missing source/campaign → `(none)`
- [x] Share % in table view
- [x] Blade views: `resources/views/widgets/table.blade.php`, `no-config.blade.php`
- [x] Register widgets + viewNamespace in ServiceProvider
- [ ] Pro gating via licensing
- [ ] Date range presets (7/30/90) + attributed-only toggle (Pro) — deferred to UI iteration

## Step 6. CSV Export (Pro)
- [x] Create `src/Http/Controllers/ExportController.php`
- [x] CP route: `GET lead-insights/export?type=source|campaign|form|form_source&days=&form=`
- [x] Streams CSV with Label, Leads, Share % columns
- [x] Permission: `export lead insights` (Gate check)
- [ ] Pro gating

## Step 7. PruneCommand (Pro)
- [x] Create `src/Commands/PruneCommand.php`
- [x] Command: `php artisan lead-insights:prune --days=365`
- [x] Default behavior: strip only `__attribution` payload from old submissions (preserve submission itself)
- [x] Read `retention_days` from addon settings, overridable via `--days` option
- [x] Register command in ServiceProvider via `$commands`

## Step 8. Permissions
- [x] Add permission `view lead insights` (widgets)
- [x] Add permission `export lead insights` (CSV, Pro)
- [x] Registered via `Permission::extend()` in ServiceProvider

## Step 9. Tests
- [ ] Consent present → UTMs captured, cookie created, attribution in submission
- [ ] Consent missing → no cookie, no UTMs, only landing_url if configured
- [ ] Widget aggregation → correct counts for seeded data with date filters
- [ ] Prune → old attribution stripped, recent untouched

## Step 10. Finalization
- [ ] Verify all acceptance criteria from DEVELOPMENT.md (section 13)
- [ ] Confirm Free/Pro split works correctly
- [ ] Update README.md