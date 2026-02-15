# Lead Insights for Statamic

**Know where your leads come from — without sacrificing their privacy.**

Lead Insights automatically captures UTM parameters, referrers, and landing pages from your visitors and attaches them to Statamic form submissions. See which campaigns, sources, and pages drive real conversions — right from your Control Panel dashboard.

No database required. No third-party scripts. No cookies without consent.

---

## Features

- Automatic UTM capture (source, medium, campaign, term, content)
- Referrer and landing page tracking
- Attribution data attached to every form submission
- Dashboard widgets with lead counts and share percentages
- First-party cookie with configurable TTL
- Consent-gated by default (EU-first)
- Works with any cookie-based consent management platform
- Flat-file storage — no database migrations, no external services
- Available in English, German, and Dutch

## Free vs Pro

| Feature | Free | Pro |
|---|:---:|:---:|
| UTM capture & form enrichment | ✓ | ✓ |
| Consent gating (EU-first) | ✓ | ✓ |
| Configurable cookie & attribution key | ✓ | ✓ |
| Leads by Source widget | ✓ | ✓ |
| Leads by Campaign widget | | ✓ |
| Leads by Form widget | | ✓ |
| Form → Source Breakdown widget | | ✓ |
| CSV export | | ✓ |
| Data retention & prune command | | ✓ |
| First-touch + last-touch attribution | | ✓ |

## Installation

```bash
composer require isapp/statamic-lead-insights
```

The addon registers automatically via Statamic's addon discovery.

## How It Works

**1. Capture** — A lightweight middleware reads UTM parameters, the referrer, and the landing URL on every page visit. If consent is given, it stores the data in a first-party cookie.

**2. Enrich** — When a visitor submits a Statamic form, the attribution payload is automatically attached to the submission. No form changes needed.

**3. Visualize** — Dashboard widgets show which sources, campaigns, and forms drive the most leads. Export the data as CSV for deeper analysis.

## Configuration

All settings are managed in the Control Panel under **Addons → Lead Insights**.

### General

| Setting | Default | Description |
|---|---|---|
| `enabled` | `true` | Enable or disable the addon globally |
| `attribution_key` | `__attribution` | Key used to store attribution data on submissions |
| `cookie_name` | `lead_insights_attribution` | Name of the first-party cookie |
| `cookie_ttl_days` | `30` | Days the attribution cookie persists |

### Consent / GDPR

| Setting | Default | Description |
|---|---|---|
| `consent_required` | `true` | Require consent before storing cookies |
| `consent_cookie_name` | — | Cookie name to check for marketing consent |
| `consent_cookie_value` | — | Expected value (empty = presence check only) |
| `store_landing_without_consent` | `true` | Store landing URL even without consent |
| `store_referrer_without_consent` | `false` | Store referrer without consent |

### Reporting

| Setting | Default | Description |
|---|---|---|
| `top_n` | `10` | Maximum rows shown in dashboard widgets |
| `default_date_range_days` | `30` | Default date range for widgets |

### Retention (Pro)

| Setting | Default | Description |
|---|---|---|
| `retention_days` | `365` | Days to keep attribution data before pruning |

## Widgets

Widgets are registered automatically but need to be added to your dashboard. Add them in `config/statamic/cp.php`:

```php
'widgets' => [
    ['type' => 'leads_by_source', 'width' => 50],
    ['type' => 'leads_by_campaign', 'width' => 50],
    ['type' => 'leads_by_form', 'width' => 50],
    ['type' => 'form_source_breakdown', 'width' => 50, 'form' => 'contact'],
],
```

### Leads by Source (Free)

Groups form submissions by `utm_source` — see which traffic sources generate the most leads.

### Leads by Campaign (Pro)

Groups submissions by `utm_campaign` — measure which campaigns convert.

### Leads by Form (Pro)

Groups submissions by form handle — find out which forms get the most attributed leads.

### Form → Source Breakdown (Pro)

Select a specific form and see a per-source breakdown. Configure the `form` handle in widget settings.

All widgets display a table with **Label**, **Leads**, and **Share %** columns, filtered by the configured date range.

## CSV Export (Pro)

Export any widget's data as a CSV file. Available export types:

- `source` — Leads by source
- `campaign` — Leads by campaign
- `form` — Leads by form
- `form_source` — Source breakdown for a specific form

Access exports from the Control Panel or via:

```
GET /cp/lead-insights/export?type=source&days=30
```

Requires the **Export Lead Insights** permission.

## Data Retention (Pro)

Remove old attribution data while keeping form submissions intact:

```bash
php artisan lead-insights:prune
```

This strips the `__attribution` key from submissions older than the configured `retention_days` (default: 365). The submissions themselves are preserved.

Override the retention period per run:

```bash
php artisan lead-insights:prune --days=90
```

Schedule it in your `routes/console.php` for automated cleanup.

## Privacy & GDPR

Lead Insights is designed EU-first. Consent is required by default — no cookie is set and no UTM data is stored until consent is detected.

### What is NOT collected

- IP addresses
- User-agent strings
- Browser fingerprints
- Click IDs (gclid, fbclid, etc.)
- Cross-site or third-party tracking data

### Cookie properties

- First-party only
- `SameSite=Lax`
- `Secure` on HTTPS
- `HttpOnly`
- Configurable name and TTL

### Consent integration

Lead Insights works with any cookie-based consent management platform (Cookiebot, CookieYes, Complianz, etc.). Configure the consent cookie name and expected value in settings. No built-in consent banner — it respects your existing setup.

### Without consent

When `consent_required` is `true` (default) and no consent is detected:

- No cookie is set
- No UTM parameters are stored
- Only the landing URL and timestamp are attached at form submission time (configurable)

## Permissions

| Permission | Edition | Description |
|---|---|---|
| View Lead Insights | Free + Pro | Access dashboard widgets |
| Export Lead Insights | Pro | Download CSV exports |

Assign permissions to roles in **CP → Users → Roles**.

## Requirements

- Statamic 6
- PHP 8.2+
