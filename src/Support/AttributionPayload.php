<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Support;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Value object representing marketing attribution data.
 *
 * Holds UTM parameters, referrer, landing URL, and timestamps.
 * Used for cookie persistence, form submission enrichment, and reporting.
 */
class AttributionPayload implements Arrayable
{
    /** @var int Current schema version */
    public const VERSION = 1;

    public function __construct(
        public ?string $utmSource = null,
        public ?string $utmMedium = null,
        public ?string $utmCampaign = null,
        public ?string $utmTerm = null,
        public ?string $utmContent = null,
        public ?string $referrer = null,
        public ?string $landingUrl = null,
        public ?string $firstSeenAt = null,
        public ?string $lastSeenAt = null,
        public int $attributionVersion = self::VERSION,
    ) {}

    /**
     * Create an instance from an associative array (e.g. decoded cookie or submission data).
     * Normalizes empty strings to null.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            utmSource: static::normalize($data['utm_source'] ?? null),
            utmMedium: static::normalize($data['utm_medium'] ?? null),
            utmCampaign: static::normalize($data['utm_campaign'] ?? null),
            utmTerm: static::normalize($data['utm_term'] ?? null),
            utmContent: static::normalize($data['utm_content'] ?? null),
            referrer: static::normalize($data['referrer'] ?? null),
            landingUrl: static::normalize($data['landing_url'] ?? null),
            firstSeenAt: static::normalize($data['first_seen_at'] ?? null),
            lastSeenAt: static::normalize($data['last_seen_at'] ?? null),
            attributionVersion: (int) ($data['attribution_version'] ?? static::VERSION),
        );
    }

    /**
     * Serialize to an associative array for storage (cookie, submission payload).
     */
    public function toArray(): array
    {
        return [
            'utm_source' => $this->utmSource,
            'utm_medium' => $this->utmMedium,
            'utm_campaign' => $this->utmCampaign,
            'utm_term' => $this->utmTerm,
            'utm_content' => $this->utmContent,
            'referrer' => $this->referrer,
            'landing_url' => $this->landingUrl,
            'first_seen_at' => $this->firstSeenAt,
            'last_seen_at' => $this->lastSeenAt,
            'attribution_version' => $this->attributionVersion,
        ];
    }

    /**
     * Merge incoming data as last-touch update.
     *
     * Overwrites UTM fields, referrer, landing_url, and last_seen_at.
     * Preserves first-touch fields (first_seen_at) if already set.
     */
    public function mergeLastTouch(self $incoming): self
    {
        return new self(
            utmSource: $incoming->utmSource ?? $this->utmSource,
            utmMedium: $incoming->utmMedium ?? $this->utmMedium,
            utmCampaign: $incoming->utmCampaign ?? $this->utmCampaign,
            utmTerm: $incoming->utmTerm ?? $this->utmTerm,
            utmContent: $incoming->utmContent ?? $this->utmContent,
            referrer: $incoming->referrer ?? $this->referrer,
            landingUrl: $incoming->landingUrl ?? $this->landingUrl,
            firstSeenAt: $this->firstSeenAt,
            lastSeenAt: $incoming->lastSeenAt ?? $this->lastSeenAt,
            attributionVersion: static::VERSION,
        );
    }

    /**
     * Set first-touch fields if they are not already set.
     * Used by Pro edition to track the original attribution source.
     */
    public function withFirstTouch(self $incoming): self
    {
        return new self(
            utmSource: $this->utmSource,
            utmMedium: $this->utmMedium,
            utmCampaign: $this->utmCampaign,
            utmTerm: $this->utmTerm,
            utmContent: $this->utmContent,
            referrer: $this->referrer,
            landingUrl: $this->landingUrl,
            firstSeenAt: $this->firstSeenAt ?? $incoming->firstSeenAt,
            lastSeenAt: $this->lastSeenAt,
            attributionVersion: static::VERSION,
        );
    }

    /**
     * Check if any UTM parameter is present.
     */
    public function hasUtm(): bool
    {
        return $this->utmSource !== null
            || $this->utmMedium !== null
            || $this->utmCampaign !== null
            || $this->utmTerm !== null
            || $this->utmContent !== null;
    }

    /**
     * Normalize empty strings to null.
     */
    private static function normalize(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
