<?php

declare(strict_types=1);

return [

    // Settings — tab labels
    'settings.tab_general' => 'Allgemein',
    'settings.tab_consent' => 'Einwilligung / DSGVO',
    'settings.tab_reporting' => 'Berichte',
    'settings.tab_retention' => 'Aufbewahrung',

    // Settings — General
    'settings.enabled' => 'Aktiviert',
    'settings.enabled_instructions' => 'Addon global aktivieren oder deaktivieren.',
    'settings.attribution_key' => 'Attribution-Schlüssel',
    'settings.attribution_key_instructions' => 'Der Schlüssel, unter dem Attributionsdaten in Formulareinsendungen gespeichert werden.',
    'settings.cookie_name' => 'Cookie-Name',
    'settings.cookie_name_instructions' => 'Name des First-Party-Cookies zur sitzungsübergreifenden Speicherung der Attribution.',
    'settings.cookie_ttl_days' => 'Cookie-Lebensdauer (Tage)',
    'settings.cookie_ttl_days_instructions' => 'Wie viele Tage das Attributions-Cookie gespeichert bleiben soll.',

    // Settings — Consent / GDPR
    'settings.consent_required' => 'Einwilligung erforderlich',
    'settings.consent_required_instructions' => 'Wenn aktiviert, erfordern Attributions-Cookies und UTM-Speicherung die Einwilligung des Nutzers. EU-first Standard.',
    'settings.consent_cookie_name' => 'Einwilligungs-Cookie-Name',
    'settings.consent_cookie_name_instructions' => 'Der Cookie-Name, der auf Marketing-Einwilligung geprüft wird.',
    'settings.consent_cookie_value' => 'Einwilligungs-Cookie-Wert',
    'settings.consent_cookie_value_instructions' => 'Der erwartete Cookie-Wert, der eine erteilte Einwilligung anzeigt. Leer lassen, um nur auf Cookie-Vorhandensein zu prüfen.',
    'settings.store_landing_without_consent' => 'Landing-URL ohne Einwilligung speichern',
    'settings.store_landing_without_consent_instructions' => 'Erlaubt die Speicherung der Landing-URL auch ohne Einwilligung.',
    'settings.store_referrer_without_consent' => 'Referrer ohne Einwilligung speichern',
    'settings.store_referrer_without_consent_instructions' => 'Erlaubt die Speicherung des Referrers auch ohne Einwilligung. Standardmäßig deaktiviert für DSGVO-Konformität.',

    // Settings — Reporting
    'settings.top_n' => 'Top-N-Zeilen',
    'settings.top_n_instructions' => 'Maximale Anzahl der in Dashboard-Widgets angezeigten Zeilen.',
    'settings.default_date_range_days' => 'Standard-Zeitraum (Tage)',
    'settings.default_date_range_days_instructions' => 'Standard-Anzahl der Tage für die Zeitraumfilterung in Widgets.',

    // Settings — Retention
    'settings.retention_days' => 'Aufbewahrungsdauer (Tage)',
    'settings.retention_days_instructions' => 'Anzahl der Tage, die Attributionsdaten vor dem Bereinigen aufbewahrt werden. Wird vom Befehl lead-insights:prune verwendet. (Pro)',

    // Permissions
    'permissions.view' => 'Lead Insights anzeigen',
    'permissions.export' => 'Lead Insights exportieren',

    // Widgets
    'widgets.leads_by_source' => 'Leads nach Quelle',
    'widgets.leads_by_campaign' => 'Leads nach Kampagne',
    'widgets.leads_by_form' => 'Leads nach Formular',
    'widgets.sources_for_form' => 'Quellen für :form',
    'widgets.no_config' => 'Bitte wählen Sie ein Formular in den Widget-Einstellungen aus.',
    'widgets.last_n_days' => 'Letzte :days Tage',
    'widgets.no_data' => 'Keine Daten für diesen Zeitraum verfügbar.',
    'widgets.label' => 'Bezeichnung',
    'widgets.leads' => 'Leads',
    'widgets.share' => 'Anteil',

    // Commands
    'commands.prune_description' => 'Attributionsdaten aus Formulareinsendungen entfernen, die älter als die Aufbewahrungsfrist sind',
    'commands.prune_result' => 'Attributionsdaten aus :count Einsendung(en) entfernt, die älter als :days Tage sind.',

    // Export
    'export.label' => 'Bezeichnung',
    'export.leads' => 'Leads',
    'export.share' => 'Anteil %',

    // Edition
    'edition.pro_required' => 'Diese Funktion erfordert die Pro-Edition.',

];
