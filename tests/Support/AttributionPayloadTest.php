<?php

declare(strict_types=1);

namespace Isapp\LeadInsights\Tests\Support;

use Illuminate\Contracts\Support\Arrayable;
use Isapp\LeadInsights\Support\AttributionPayload;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for AttributionPayload value object.
 *
 * Covers: fromArray/toArray roundtrip, empty string normalization,
 * last-touch merge, first-touch preservation, and UTM detection.
 */
class AttributionPayloadTest extends TestCase
{
    #[Test]
    public function it_creates_from_array_and_serializes_back(): void
    {
        $data = [
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'spring_sale',
            'utm_term' => 'shoes',
            'utm_content' => 'banner_a',
            'referrer' => 'https://google.com',
            'landing_url' => 'https://example.com/page?utm_source=google',
            'first_seen_at' => '2025-01-01T00:00:00+00:00',
            'last_seen_at' => '2025-01-02T00:00:00+00:00',
            'attribution_version' => 1,
        ];

        $payload = AttributionPayload::fromArray($data);

        $this->assertSame($data, $payload->toArray());
    }

    #[Test]
    public function it_normalizes_empty_strings_to_null(): void
    {
        $payload = AttributionPayload::fromArray([
            'utm_source' => '',
            'utm_medium' => '',
            'utm_campaign' => null,
            'last_seen_at' => '2025-01-01T00:00:00+00:00',
        ]);

        $this->assertNull($payload->utmSource);
        $this->assertNull($payload->utmMedium);
        $this->assertNull($payload->utmCampaign);
        $this->assertSame('2025-01-01T00:00:00+00:00', $payload->lastSeenAt);
    }

    #[Test]
    public function it_defaults_missing_fields_to_null(): void
    {
        $payload = AttributionPayload::fromArray([]);

        $this->assertNull($payload->utmSource);
        $this->assertNull($payload->referrer);
        $this->assertNull($payload->lastSeenAt);
        $this->assertSame(AttributionPayload::VERSION, $payload->attributionVersion);
    }

    #[Test]
    public function merge_last_touch_overwrites_utm_and_preserves_first_seen_at(): void
    {
        $existing = AttributionPayload::fromArray([
            'utm_source' => 'google',
            'utm_campaign' => 'old_campaign',
            'first_seen_at' => '2025-01-01T00:00:00+00:00',
            'last_seen_at' => '2025-01-01T00:00:00+00:00',
        ]);

        $incoming = AttributionPayload::fromArray([
            'utm_source' => 'facebook',
            'utm_campaign' => 'new_campaign',
            'first_seen_at' => '2025-02-01T00:00:00+00:00',
            'last_seen_at' => '2025-02-01T00:00:00+00:00',
        ]);

        $merged = $existing->mergeLastTouch($incoming);

        // Last-touch fields are overwritten
        $this->assertSame('facebook', $merged->utmSource);
        $this->assertSame('new_campaign', $merged->utmCampaign);
        $this->assertSame('2025-02-01T00:00:00+00:00', $merged->lastSeenAt);

        // First-touch is preserved from existing
        $this->assertSame('2025-01-01T00:00:00+00:00', $merged->firstSeenAt);
    }

    #[Test]
    public function with_first_touch_sets_only_when_not_already_set(): void
    {
        $withoutFirstTouch = new AttributionPayload(
            utmSource: 'google',
            lastSeenAt: '2025-01-01T00:00:00+00:00',
        );

        $incoming = new AttributionPayload(
            firstSeenAt: '2025-01-01T00:00:00+00:00',
        );

        $result = $withoutFirstTouch->withFirstTouch($incoming);
        $this->assertSame('2025-01-01T00:00:00+00:00', $result->firstSeenAt);

        // If first_seen_at already set, it is not overwritten
        $withFirstTouch = new AttributionPayload(
            utmSource: 'google',
            firstSeenAt: '2025-01-01T00:00:00+00:00',
        );

        $laterIncoming = new AttributionPayload(
            firstSeenAt: '2025-06-01T00:00:00+00:00',
        );

        $result = $withFirstTouch->withFirstTouch($laterIncoming);
        $this->assertSame('2025-01-01T00:00:00+00:00', $result->firstSeenAt);
    }

    #[Test]
    public function has_utm_detects_any_utm_parameter(): void
    {
        $this->assertFalse((new AttributionPayload)->hasUtm());
        $this->assertTrue((new AttributionPayload(utmSource: 'google'))->hasUtm());
        $this->assertTrue((new AttributionPayload(utmMedium: 'cpc'))->hasUtm());
        $this->assertTrue((new AttributionPayload(utmCampaign: 'test'))->hasUtm());
        $this->assertTrue((new AttributionPayload(utmTerm: 'keyword'))->hasUtm());
        $this->assertTrue((new AttributionPayload(utmContent: 'banner'))->hasUtm());
    }

    #[Test]
    public function it_implements_arrayable(): void
    {
        $payload = new AttributionPayload(utmSource: 'google');

        $this->assertInstanceOf(Arrayable::class, $payload);
    }
}
