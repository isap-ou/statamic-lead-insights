<?php

declare(strict_types=1);

return [

    // Settings — tab labels
    'settings.tab_general' => 'Algemeen',
    'settings.tab_consent' => 'Toestemming / AVG',
    'settings.tab_reporting' => 'Rapportage',
    'settings.tab_retention' => 'Bewaring',

    // Settings — General
    'settings.enabled' => 'Ingeschakeld',
    'settings.enabled_instructions' => 'Addon globaal in- of uitschakelen.',
    'settings.attribution_key' => 'Attributiesleutel',
    'settings.attribution_key_instructions' => 'De sleutel die wordt gebruikt om attributiegegevens op te slaan bij formulierinzendingen.',
    'settings.cookie_name' => 'Cookienaam',
    'settings.cookie_name_instructions' => 'Naam van de first-party cookie voor het bewaren van attributie tussen sessies.',
    'settings.cookie_ttl_days' => 'Cookie-levensduur (dagen)',
    'settings.cookie_ttl_days_instructions' => 'Hoeveel dagen de attributiecookie bewaard moet blijven.',

    // Settings — Consent / GDPR
    'settings.consent_required' => 'Toestemming vereist',
    'settings.consent_required_instructions' => 'Wanneer ingeschakeld, vereisen attributiecookies en UTM-opslag toestemming van de gebruiker. EU-first standaard.',
    'settings.consent_cookie_name' => 'Toestemmingscookienaam',
    'settings.consent_cookie_name_instructions' => 'De cookienaam die wordt gecontroleerd op marketingtoestemming.',
    'settings.consent_cookie_value' => 'Toestemmingscookiewaarde',
    'settings.consent_cookie_value_instructions' => 'De verwachte cookiewaarde die aangeeft dat toestemming is gegeven. Laat leeg om alleen op aanwezigheid van de cookie te controleren.',
    'settings.store_landing_without_consent' => 'Landing-URL opslaan zonder toestemming',
    'settings.store_landing_without_consent_instructions' => 'Sta opslag van de landing-URL toe, ook zonder toestemming.',
    'settings.store_referrer_without_consent' => 'Referrer opslaan zonder toestemming',
    'settings.store_referrer_without_consent_instructions' => 'Sta opslag van de referrer toe, ook zonder toestemming. Standaard uitgeschakeld voor AVG-naleving.',

    // Settings — Reporting
    'settings.top_n' => 'Top N rijen',
    'settings.top_n_instructions' => 'Maximaal aantal rijen dat wordt weergegeven in dashboardwidgets.',
    'settings.default_date_range_days' => 'Standaard datumbereik (dagen)',
    'settings.default_date_range_days_instructions' => 'Standaard aantal dagen voor datumbereikfiltering in widgets.',

    // Settings — Retention
    'settings.retention_days' => 'Bewaartermijn (dagen)',
    'settings.retention_days_instructions' => 'Aantal dagen dat attributiegegevens bewaard worden voordat ze worden opgeschoond. Gebruikt door het commando lead-insights:prune. (Pro)',

    // Permissions
    'permissions.view' => 'Lead Insights bekijken',
    'permissions.export' => 'Lead Insights exporteren',

    // Widgets
    'widgets.leads_by_source' => 'Leads per bron',
    'widgets.leads_by_campaign' => 'Leads per campagne',
    'widgets.leads_by_form' => 'Leads per formulier',
    'widgets.sources_for_form' => 'Bronnen voor :form',
    'widgets.no_config' => 'Selecteer een formulier in de widgetinstellingen.',
    'widgets.last_n_days' => 'Laatste :days dagen',
    'widgets.no_data' => 'Geen gegevens beschikbaar voor deze periode.',
    'widgets.export_csv' => 'CSV exporteren',
    'widgets.label' => 'Label',
    'widgets.leads' => 'Leads',
    'widgets.share' => 'Aandeel',

    // Commands
    'commands.prune_description' => 'Attributiegegevens verwijderen uit formulierinzendingen die ouder zijn dan de bewaartermijn',
    'commands.prune_result' => 'Attributiegegevens verwijderd uit :count inzending(en) ouder dan :days dagen.',

    // Export
    'export.label' => 'Label',
    'export.leads' => 'Leads',
    'export.share' => 'Aandeel %',

    // Edition
    'edition.pro_required' => 'Deze functie vereist de Pro-editie.',

];
