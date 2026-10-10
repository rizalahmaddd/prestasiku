<?php

namespace App\Support\Site;

use App\Models\SiteEvent;
use App\Models\SiteVisit;
use App\Support\Features;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Mencatat kunjungan dan interaksi dari halaman publik. Yang tidak dicatat: bot, akun yang
 * sedang login ke dashboard (supaya pengelola tidak menghitung dirinya sendiri), dan apa pun
 * saat fitur Statistik dimatikan.
 */
class VisitRecorder
{
    public function __construct(private readonly Request $request) {}

    public function shouldRecord(): bool
    {
        return Features::enabled('content.analytics')
            && $this->request->user() === null
            && ! $this->agent()->isBot();
    }

    /**
     * @param  array{referrer?: ?string, query?: ?string}  $data
     */
    public function recordVisit(array $data): void
    {
        parse_str(ltrim((string) ($data['query'] ?? ''), '?'), $query);

        $agent = $this->agent();
        $source = TrafficSource::resolve(
            $data['referrer'] ?? null,
            is_string($query['utm_source'] ?? null) ? $query['utm_source'] : null,
            $this->request->getHost(),
            $agent,
        );

        SiteVisit::create([
            ...$source,
            'visitor' => $this->visitor(),
            'visited_on' => now()->toDateString(),
            'hour' => now()->hour,
            'campaign' => is_string($query['utm_campaign'] ?? null) && $query['utm_campaign'] !== '' ? Str::limit($query['utm_campaign'], 120, '') : null,
            'country' => $this->country(),
            'city' => $this->city(),
            'device' => $agent->device(),
            'browser' => $agent->browser(),
            'os' => $agent->os(),
        ]);
    }

    public function recordEvent(string $name, ?string $label): void
    {
        SiteEvent::create([
            'visitor' => $this->visitor(),
            'occurred_on' => now()->toDateString(),
            'name' => $name,
            'label' => filled($label) ? Str::limit(trim((string) $label), 191, '') : null,
        ]);
    }

    /**
     * Pengenal anonim: hash dari IP + user agent + tanggal + APP_KEY. Berganti tiap hari, jadi
     * orang yang sama tidak bisa diikuti dari hari ke hari dan IP-nya tidak pernah tersimpan.
     */
    public function visitor(): string
    {
        return sha1(implode('|', [$this->request->ip(), $this->request->userAgent(), now()->toDateString(), config('app.key')]));
    }

    private function agent(): UserAgent
    {
        return new UserAgent((string) $this->request->userAgent());
    }

    /**
     * Lokasi hanya tersedia kalau website berada di belakang Cloudflare, yang menambahkan
     * header negara (dan kota bila "Add visitor location headers" diaktifkan).
     */
    private function country(): ?string
    {
        $country = Str::upper((string) $this->request->header('CF-IPCountry'));

        return preg_match('/^[A-Z]{2}$/', $country) === 1 && ! in_array($country, ['XX', 'T1'], true) ? $country : null;
    }

    private function city(): ?string
    {
        $city = trim((string) $this->request->header('CF-IPCity'));

        return $city !== '' ? Str::limit($city, 120, '') : null;
    }
}
