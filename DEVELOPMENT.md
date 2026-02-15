# Lead Insights — Technical Specification (Statamic 6, EU-first, Free + Pro)

## 1. Product Summary
**Lead Insights** is a **Statamic 6** addon that captures marketing attribution data (UTMs, referrer, landing page, timestamps) and attaches it to **form submissions**, then visualizes where leads come from using **Control Panel dashboard widgets**.

This project is **EU-first**: privacy-by-design, consent-aware defaults, minimal data collection, and retention controls.

---

## 2. Objectives & Business Value
- Provide immediate visibility into **lead sources** (campaigns/channels) without external analytics tooling.
- Help business owners answer:
  - Which UTM sources/campaigns generate leads?
  - Which forms receive the most leads, and from where?
- Enable agencies to ship attribution insight as a built-in part of Statamic sites.

---

## 3. Scope

### 3.1 In scope (MVP)
- Capture attribution on frontend requests.
- Attach attribution payload to Statamic form submissions.
- Dashboard widgets (Free + Pro).
- EU-first consent gating, data minimization, and retention.
- CSV export of aggregated reports (Pro).

### 3.2 Out of scope
- Any third-party integrations (CRM, Zapier/Make, webhooks)
- Ad platform APIs and spend import
- CPL / ROI calculations
- CP reporting pages beyond widgets
- Fingerprinting or cross-site tracking

---

## 4. Editions (Free vs Pro)

### 4.1 Free Edition
**Goal:** useful immediately after install.

Features:
- Capture: `utm_source`, `utm_medium`, `utm_campaign`, optional `utm_term`, `utm_content`
- Capture: `landing_url`, `last_seen_at` (ISO8601)
- Attach payload to submission under reserved key (default `__attribution`)
- Consent gating ON by default (EU)
- Dashboard widget:
  - **Leads by Source** (group by `utm_source`, bucket `(none)`)

Free limitations:
- No campaign/form widgets (Pro only)
- No export (Pro only)
- First-touch is optional; last-touch only is acceptable

### 4.2 Pro Edition
Features (in addition to Free):
- Dashboard widgets:
  1) Leads by Source
  2) Leads by Campaign (group by `utm_campaign`, bucket `(none)`)
  3) Leads by Form (group by form handle)
  4) Form → Source breakdown (widget setting: form handle)
- Filtering:
  - Date range presets (7/30/90) + custom range (if feasible)
  - Optional “attributed only” toggle (excludes `(none)`)
  - Optional form filter (where relevant)
- CSV export of aggregated tables
- Retention tooling (prune command + config)
- Recommended: first-touch + last-touch model (store `first_seen_at` + first landing/referrer/UTMs)

---

## 5. Data Capture Requirements

### 5.1 Attribution fields (stored on submission)
All editions store a single payload under a reserved key, default `__attribution`.

Payload schema v1:
- `utm_source` (string|null)
- `utm_medium` (string|null)
- `utm_campaign` (string|null)
- `utm_term` (string|null)
- `utm_content` (string|null)
- `referrer` (string|null)
- `landing_url` (string|null)
- `first_seen_at` (string ISO8601|null) *(Pro recommended)*
- `last_seen_at` (string ISO8601)
- `attribution_version` (int, value: 1)

### 5.2 Bucketing rules for reporting
- Missing `utm_source` => `(none)`
- Missing `utm_campaign` => `(none)`
- Optional: normalize empty strings to null

### 5.3 Capture rules (request-time)
Implement middleware `CaptureAttribution` on the **frontend web** requests.

**Input sources:**
- Query parameters: `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`
- Header: `Referer`
- Current URL for landing tracking

**Rules:**
- If any `utm_*` exists on the request:
  - Update last-touch UTMs and `last_seen_at`
  - If first-touch not set for current window (Pro), set it (UTMs + landing_url + referrer + first_seen_at)
- If no `utm_*` exists:
  - Optionally update last-touch **referrer** when it is external (configurable; OFF by default without consent)
  - Never overwrite first-touch (Pro)

**Persistence for attribution state:**
- Prefer **first-party cookie** (to survive sessions) with configurable TTL days
- Session fallback is acceptable if cookies unavailable
- Cookie settings:
  - SameSite=Lax
  - Secure on HTTPS
  - HttpOnly configurable (recommended true)

---

## 6. Consent & EU-first Behavior

### 6.1 Defaults (must be conservative)
- `consent_required = true` (default)
- Without consent:
  - Do not set attribution cookie
  - Do not store UTMs
  - `store_landing_without_consent = true` (default)
  - `store_referrer_without_consent = false` (default)

### 6.2 Consent signal
Support a configurable cookie check:
- `consent_cookie_name`
- `consent_cookie_value` (string or list)

Consent logic:
- If cookie missing or value not allowed => treat as **no consent**

### 6.3 Data minimization
- Do not store IP or user-agent (no config to enable in MVP)
- Do not store click IDs (gclid/fbclid) in MVP unless explicitly added later with consent gating

---

## 7. Submission Enrichment

### 7.1 Hook point
On Statamic form submission creation, inject the attribution payload into the submission.

### 7.2 Behavior
- Read attribution state from cookie/session (if consent allows)
- If consent missing:
  - attach only landing_url and timestamps if configured
- Ensure payload is written before persistence to the submission storage

### 7.3 Storage compatibility
- Must not assume a specific submission storage driver
- Must not break existing submissions or form processing

---

## 8. Control Panel Widgets

### 8.1 Common widget requirements
Each widget must support:
- Date range presets: last 7/30/90 days (minimum)
- Optional custom range (nice-to-have)
- Top N rows (configurable)
- Consistent `(none)` bucketing

### 8.2 Widget definitions
1) **Leads by Source** (Free + Pro)
- Group by `utm_source`
- Show count and share % (optional)

2) **Leads by Campaign** (Pro)
- Group by `utm_campaign`
- Optional secondary grouping by source (nice-to-have)

3) **Leads by Form** (Pro)
- Group by `form_handle`

4) **Form → Source Breakdown** (Pro)
- Widget setting: form handle
- Group by source (and optionally campaign)

### 8.3 Permissions
- Add a permission like `view lead insights`
- Exports require separate permission `export lead insights` (Pro)

---

## 9. Reporting Aggregation & Performance

### 9.1 MVP aggregation approach
For MVP, aggregate counts on demand from submissions.
If performance becomes an issue, implement optional daily aggregates later.

### 9.2 Optional future aggregate table (not required)
Keyed by:
- date (YYYY-MM-DD)
- form_handle
- utm_source
- utm_campaign
  - utm_medium

---

## 10. Retention & Pruning (Pro)
Implement:
- Config: `retention_days` (default 365)
- Command: `php artisan lead-insights:prune --days=365`
- Prune behavior (choose one, document clearly):
  A) Remove only `__attribution` payload from old submissions (preferred, preserves business records), or
  B) Delete old submissions entirely (configurable, but off by default)

Default recommendation: **strip attribution payload only**.

---

## 11. Configuration (CP Settings UI)
Settings are managed via the Statamic addon settings UI (`registerSettingsBlueprint()` in ServiceProvider).
Stored automatically as flat-file YAML in `resources/addons/{slug}.yaml`.
Accessed in code via `$this->addon->setting('key')`.

Settings keys:
- `enabled` (toggle, default true)
- `attribution_key` (text, default `__attribution`)
- `cookie_name` (text, default `lead_insights_attribution`)
- `cookie_ttl_days` (integer, default 30)
- `consent_required` (toggle, default true)
- `consent_cookie_name` (text)
- `consent_cookie_value` (text)
- `store_landing_without_consent` (toggle, default true)
- `store_referrer_without_consent` (toggle, default false)
- `retention_days` (integer, default 365) *(Pro)*
- `top_n` (integer, default 10)
- `default_date_range_days` (integer, default 30)

Note: `edition` is determined by license, not by settings.

---

## 12. Testing Plan (minimum)
### 12.1 Feature tests
1) Consent present:
- Request with UTM params => attribution cookie created/updated
- Form submit => submission contains UTMs

2) Consent missing:
- Request with UTM params => no attribution cookie, no UTMs stored
- Form submit => submission contains landing_url only if configured

3) Widget aggregation:
- Seed submissions with known attribution payloads
- Ensure widget results match expected counts for date filters

4) Prune (Pro):
- Seed old submissions
- Run prune command
- Verify attribution removed from old items and remains for recent ones

---

## 13. Acceptance Criteria (Definition of Done)
- UTM visit updates attribution state only when consent allows it.
- Every form submission contains the attribution payload under the reserved key.
- Widgets display correct lead counts by source/campaign/form for selected periods.
- EU-first defaults are enforced (consent required, minimal data, retention supported).
- Free/Pro split is implemented (Free has 1 widget; Pro unlocks remaining widgets + export + retention command).
