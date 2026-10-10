<?php

namespace App\Support\Site;

/**
 * Pembacaan user agent seperlunya untuk statistik pengunjung: perangkat, browser, sistem
 * operasi, dan apakah ini bot. Sengaja sederhana; angka kasar sudah cukup untuk laporan.
 */
class UserAgent
{
    private const BOT_PATTERN = '/bot|crawl|spider|slurp|preview|scrape|fetch|monitor|headless|lighthouse|pingdom|uptime|curl|wget|python|httpclient|postman|facebookexternalhit|whatsapp\/|telegrambot|embedly|quora link|bingpreview/i';

    public function __construct(private readonly string $value) {}

    public function isBot(): bool
    {
        return trim($this->value) === '' || preg_match(self::BOT_PATTERN, $this->value) === 1;
    }

    public function device(): string
    {
        return match (true) {
            preg_match('/ipad|tablet|playbook|silk|(android(?!.*mobile))/i', $this->value) === 1 => 'tablet',
            preg_match('/mobi|iphone|ipod|android|windows phone/i', $this->value) === 1 => 'mobile',
            default => 'desktop',
        };
    }

    public function os(): string
    {
        return match (true) {
            preg_match('/windows phone/i', $this->value) === 1 => 'Windows Phone',
            preg_match('/android/i', $this->value) === 1 => 'Android',
            preg_match('/iphone|ipad|ipod/i', $this->value) === 1 => 'iOS',
            preg_match('/windows/i', $this->value) === 1 => 'Windows',
            preg_match('/cros/i', $this->value) === 1 => 'ChromeOS',
            preg_match('/mac os x|macintosh/i', $this->value) === 1 => 'macOS',
            preg_match('/linux/i', $this->value) === 1 => 'Linux',
            default => 'Lainnya',
        };
    }

    /**
     * Browser dalam aplikasi dicek lebih dulu: user agent-nya juga memuat "Chrome"/"Safari".
     */
    public function browser(): string
    {
        return match (true) {
            preg_match('/instagram/i', $this->value) === 1 => 'Instagram',
            preg_match('/fban|fbav|fb_iab/i', $this->value) === 1 => 'Facebook',
            preg_match('/tiktok|musical_ly|bytedance/i', $this->value) === 1 => 'TikTok',
            preg_match('/\bline\//i', $this->value) === 1 => 'LINE',
            preg_match('/edg(e|a|ios)?\//i', $this->value) === 1 => 'Edge',
            preg_match('/opr\/|opera/i', $this->value) === 1 => 'Opera',
            preg_match('/samsungbrowser/i', $this->value) === 1 => 'Samsung Internet',
            preg_match('/ucbrowser/i', $this->value) === 1 => 'UC Browser',
            preg_match('/firefox|fxios/i', $this->value) === 1 => 'Firefox',
            preg_match('/chrome|crios/i', $this->value) === 1 => 'Chrome',
            preg_match('/safari/i', $this->value) === 1 => 'Safari',
            default => 'Lainnya',
        };
    }

    /**
     * Aplikasi tempat tautan dibuka, kalau browsernya browser dalam aplikasi. Dipakai sebagai
     * petunjuk sumber saat referrer kosong.
     */
    public function inAppSource(): ?string
    {
        return in_array($this->browser(), ['Instagram', 'Facebook', 'TikTok', 'LINE'], true) ? $this->browser() : null;
    }
}
