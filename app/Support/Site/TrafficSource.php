<?php

namespace App\Support\Site;

use Illuminate\Support\Str;

/**
 * Menentukan dari mana seorang pengunjung datang. Urutan kepercayaan: parameter utm_source di
 * tautan, lalu alamat perujuk (referrer), lalu browser dalam aplikasi. Tanpa ketiganya dianggap
 * "Langsung" (mengetik alamat, bookmark, atau tautan dari aplikasi pesan yang tidak mengirim perujuk).
 */
class TrafficSource
{
    public const DIRECT = 'Langsung';

    /**
     * @var array<string, string>
     */
    public const CHANNELS = [
        'direct' => 'Langsung',
        'social' => 'Media sosial',
        'chat' => 'Aplikasi pesan',
        'search' => 'Mesin pencari',
        'referral' => 'Situs lain',
        'campaign' => 'Tautan berpelacak lain',
    ];

    /**
     * Kata kunci di host perujuk atau utm_source => [nama sumber, saluran].
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const KNOWN = [
        'whatsapp' => ['WhatsApp', 'chat'],
        'wa.me' => ['WhatsApp', 'chat'],
        'telegram' => ['Telegram', 'chat'],
        't.me' => ['Telegram', 'chat'],
        'line' => ['LINE', 'chat'],
        'instagram' => ['Instagram', 'social'],
        'facebook' => ['Facebook', 'social'],
        'fb.' => ['Facebook', 'social'],
        'tiktok' => ['TikTok', 'social'],
        'youtube' => ['YouTube', 'social'],
        'youtu.be' => ['YouTube', 'social'],
        'twitter' => ['X (Twitter)', 'social'],
        't.co' => ['X (Twitter)', 'social'],
        'x.com' => ['X (Twitter)', 'social'],
        'linkedin' => ['LinkedIn', 'social'],
        'lnkd.in' => ['LinkedIn', 'social'],
        'threads' => ['Threads', 'social'],
        'google' => ['Google', 'search'],
        'bing' => ['Bing', 'search'],
        'yahoo' => ['Yahoo', 'search'],
        'duckduckgo' => ['DuckDuckGo', 'search'],
        'yandex' => ['Yandex', 'search'],
    ];

    /**
     * Sumber yang ditawarkan di pembuat tautan berpelacak: nilai utm_source => label.
     *
     * @var array<string, string>
     */
    public const SHARE_TARGETS = [
        'whatsapp' => 'WhatsApp',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'tiktok' => 'TikTok',
        'email' => 'Email',
        'qr' => 'Kode QR / cetakan',
    ];

    /**
     * @return array{source: string, channel: string, referrer_host: ?string}
     */
    public static function resolve(?string $referrer, ?string $utmSource, ?string $siteHost, UserAgent $agent): array
    {
        $host = self::host($referrer);

        if ($host !== null && $siteHost !== null && self::bare($host) === self::bare($siteHost)) {
            $host = null;
        }

        if (filled($utmSource)) {
            $utmSource = Str::limit(trim((string) $utmSource), 60, '');
            [$source, $channel] = self::match(Str::lower($utmSource)) ?? [match (Str::lower($utmSource)) {
                'email' => 'Email',
                'qr' => 'Kode QR / cetakan',
                default => Str::headline($utmSource),
            }, 'campaign'];

            return ['source' => $source, 'channel' => $channel, 'referrer_host' => $host];
        }

        if ($host !== null) {
            [$source, $channel] = self::match($host) ?? [self::bare($host), 'referral'];

            return ['source' => $source, 'channel' => $channel, 'referrer_host' => $host];
        }

        if ($inApp = $agent->inAppSource()) {
            return ['source' => $inApp, 'channel' => $inApp === 'LINE' ? 'chat' : 'social', 'referrer_host' => null];
        }

        return ['source' => self::DIRECT, 'channel' => 'direct', 'referrer_host' => null];
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private static function match(string $haystack): ?array
    {
        foreach (self::KNOWN as $needle => $result) {
            // "t.co" dan "x.com" harus cocok persis; kalau tidak, "pinterest.com" ikut terbaca "t.co".
            $matches = in_array($needle, ['t.co', 'x.com', 't.me', 'wa.me', 'line'], true)
                ? self::bare($haystack) === $needle
                : str_contains($haystack, $needle);

            if ($matches) {
                return $result;
            }
        }

        return null;
    }

    private static function host(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $host = parse_url((string) $url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? Str::limit(Str::lower($host), 191, '') : null;
    }

    private static function bare(string $host): string
    {
        return (string) preg_replace('/^(www|m|l|lm|mobile)\./', '', Str::lower($host));
    }
}
