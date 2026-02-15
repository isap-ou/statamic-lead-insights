<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Support;

use Statamic\Facades\Addon;

/**
 * Typed DTO for Lead Insights addon settings.
 *
 * Registered as a singleton in the service container.
 * Provides type-safe access to all addon configuration values.
 */
class Settings
{
    public function __construct(
        public readonly bool $enabled = true,
        public readonly string $attributionKey = '__attribution',
        public readonly string $cookieName = 'lead_insights_attribution',
        public readonly int $cookieTtlDays = 30,
        public readonly bool $consentRequired = true,
        public readonly ?string $consentCookieName = null,
        public readonly ?string $consentCookieValue = null,
        public readonly bool $storeLandingWithoutConsent = true,
        public readonly bool $storeReferrerWithoutConsent = false,
        public readonly int $topN = 10,
        public readonly int $defaultDateRangeDays = 30,
        public readonly int $retentionDays = 365,
    ) {}

    /**
     * Create an instance from the addon's stored settings.
     */
    public static function fromAddon(): static
    {
        $values = Addon::get('isapp/statamic-lead-insights')?->settings()?->all() ?? [];

        return static::fromArray($values);
    }

    /**
     * Create an instance from an associative array.
     */
    public static function fromArray(array $values): static
    {
        return new static(
            enabled: (bool) ($values['enabled'] ?? true),
            attributionKey: (string) ($values['attribution_key'] ?? '__attribution'),
            cookieName: (string) ($values['cookie_name'] ?? 'lead_insights_attribution'),
            cookieTtlDays: (int) ($values['cookie_ttl_days'] ?? 30),
            consentRequired: (bool) ($values['consent_required'] ?? true),
            consentCookieName: ($values['consent_cookie_name'] ?? null) ?: null,
            consentCookieValue: ($values['consent_cookie_value'] ?? null) ?: null,
            storeLandingWithoutConsent: (bool) ($values['store_landing_without_consent'] ?? true),
            storeReferrerWithoutConsent: (bool) ($values['store_referrer_without_consent'] ?? false),
            topN: (int) ($values['top_n'] ?? 10),
            defaultDateRangeDays: (int) ($values['default_date_range_days'] ?? 30),
            retentionDays: (int) ($values['retention_days'] ?? 365),
        );
    }
}
