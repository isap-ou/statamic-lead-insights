<?php

declare(strict_types=1);

return [

    // Settings — tab labels
    'settings.tab_general' => 'General',
    'settings.tab_consent' => 'Consent / GDPR',
    'settings.tab_reporting' => 'Reporting',
    'settings.tab_retention' => 'Retention',

    // Settings — General
    'settings.enabled' => 'Enabled',
    'settings.enabled_instructions' => 'Enable or disable the addon globally.',
    'settings.attribution_key' => 'Attribution Key',
    'settings.attribution_key_instructions' => 'The key used to store attribution data on form submissions.',
    'settings.cookie_name' => 'Cookie Name',
    'settings.cookie_name_instructions' => 'Name of the first-party cookie used to persist attribution between sessions.',
    'settings.cookie_ttl_days' => 'Cookie TTL (days)',
    'settings.cookie_ttl_days_instructions' => 'How many days the attribution cookie should persist.',

    // Settings — Consent / GDPR
    'settings.consent_required' => 'Consent Required',
    'settings.consent_required_instructions' => 'When enabled, attribution cookies and UTM storage require user consent. EU-first default.',
    'settings.consent_cookie_name' => 'Consent Cookie Name',
    'settings.consent_cookie_name_instructions' => 'The cookie name to check for marketing consent.',
    'settings.consent_cookie_value' => 'Consent Cookie Value',
    'settings.consent_cookie_value_instructions' => 'The expected cookie value that indicates consent was given. Leave empty to check for cookie presence only.',
    'settings.store_landing_without_consent' => 'Store Landing URL Without Consent',
    'settings.store_landing_without_consent_instructions' => 'Allow storing the landing URL even when consent is not given.',
    'settings.store_referrer_without_consent' => 'Store Referrer Without Consent',
    'settings.store_referrer_without_consent_instructions' => 'Allow storing the referrer even when consent is not given. Off by default for GDPR compliance.',

    // Settings — Reporting
    'settings.top_n' => 'Top N Rows',
    'settings.top_n_instructions' => 'Maximum number of rows displayed in dashboard widgets.',
    'settings.default_date_range_days' => 'Default Date Range (days)',
    'settings.default_date_range_days_instructions' => 'Default number of days for widget date range filtering.',

    // Settings — Retention
    'settings.retention_days' => 'Retention Days',
    'settings.retention_days_instructions' => 'Number of days to keep attribution data before pruning. Used by the lead-insights:prune command. (Pro)',

    // Permissions
    'permissions.view' => 'View Lead Insights',
    'permissions.export' => 'Export Lead Insights',

    // Widgets
    'widgets.leads_by_source' => 'Leads by Source',
    'widgets.leads_by_campaign' => 'Leads by Campaign',
    'widgets.leads_by_form' => 'Leads by Form',
    'widgets.sources_for_form' => 'Sources for :form',
    'widgets.no_config' => 'Please select a form in widget settings.',
    'widgets.last_n_days' => 'Last :days days',
    'widgets.no_data' => 'No data available for this period.',
    'widgets.export_csv' => 'Export CSV',
    'widgets.label' => 'Label',
    'widgets.leads' => 'Leads',
    'widgets.share' => 'Share',

    // Commands
    'commands.prune_description' => 'Strip attribution data from form submissions older than the retention period',
    'commands.prune_result' => 'Pruned attribution data from :count submission(s) older than :days days.',

    // Export
    'export.label' => 'Label',
    'export.leads' => 'Leads',
    'export.share' => 'Share %',

    // Edition
    'edition.pro_required' => 'This feature requires the Pro edition.',

];
