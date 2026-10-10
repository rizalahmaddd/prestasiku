<?php

use App\Livewire\Content\Analytics;
use App\Models\SiteEvent;
use App\Models\SiteVisit;
use App\Support\Features;
use App\Support\Site\AnalyticsReport;
use App\Support\Site\TrafficSource;
use App\Support\Site\UserAgent;
use Livewire\Livewire;

const PHONE_UA = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Mobile Safari/537.36';
const LAPTOP_UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15';

function recordedVisit(array $attributes = []): SiteVisit
{
    return SiteVisit::create($attributes + [
        'visitor' => sha1(fake()->unique()->uuid()),
        'visited_on' => today()->toDateString(),
        'hour' => 10,
        'channel' => 'direct',
        'source' => TrafficSource::DIRECT,
        'device' => 'mobile',
        'browser' => 'Chrome',
        'os' => 'Android',
    ]);
}

test('a visitor signal records where they came from, their device, and their location', function () {
    $this->withHeaders(['User-Agent' => PHONE_UA, 'CF-IPCountry' => 'id', 'CF-IPCity' => 'Bekasi'])
        ->postJson(route('track'), [
            'type' => 'visit',
            'referrer' => 'https://l.instagram.com/?u=abc',
            'query' => '?utm_campaign=lomba-2026',
        ])
        ->assertNoContent();

    $visit = SiteVisit::sole();

    expect($visit->only(['source', 'channel', 'referrer_host', 'campaign', 'country', 'city', 'device', 'browser', 'os']))->toBe([
        'source' => 'Instagram',
        'channel' => 'social',
        'referrer_host' => 'l.instagram.com',
        'campaign' => 'lomba-2026',
        'country' => 'ID',
        'city' => 'Bekasi',
        'device' => 'mobile',
        'browser' => 'Chrome',
        'os' => 'Android',
    ])->and($visit->visitor)->toHaveLength(40);
});

test('the tracked link source wins over the referrer, and links without either count as direct', function (array $payload, string $source, string $channel) {
    $this->withHeaders(['User-Agent' => LAPTOP_UA])->postJson(route('track'), ['type' => 'visit'] + $payload)->assertNoContent();

    expect(SiteVisit::sole()->only(['source', 'channel']))->toBe(['source' => $source, 'channel' => $channel]);
})->with([
    'utm whatsapp' => [['query' => '?utm_source=whatsapp', 'referrer' => 'https://www.google.com/'], 'WhatsApp', 'chat'],
    'utm qr' => [['query' => '?utm_source=qr'], 'Kode QR / cetakan', 'campaign'],
    'google' => [['referrer' => 'https://www.google.co.id/search?q=kenzo'], 'Google', 'search'],
    'other site' => [['referrer' => 'https://www.sekolah.sch.id/berita'], 'sekolah.sch.id', 'referral'],
    'pinterest is not x' => [['referrer' => 'https://pinterest.com/pin/1'], 'pinterest.com', 'referral'],
    'own pages' => [['referrer' => 'http://localhost/dashboard'], 'Langsung', 'direct'],
    'nothing' => [[], 'Langsung', 'direct'],
]);

test('bots, signed-in admins, and a disabled feature are not counted', function () {
    $this->withHeaders(['User-Agent' => 'WhatsApp/2.23.20 A'])->postJson(route('track'), ['type' => 'visit'])->assertNoContent();
    $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1)'])->postJson(route('track'), ['type' => 'visit'])->assertNoContent();

    Features::setDisabled(['content.analytics']);
    $this->withHeaders(['User-Agent' => PHONE_UA])->postJson(route('track'), ['type' => 'visit'])->assertNoContent();
    $this->get(route('home'))->assertDontSee('track.js');
    Features::setDisabled([]);

    actingAsAdmin();
    $this->withHeaders(['User-Agent' => PHONE_UA])->postJson(route('track'), ['type' => 'visit'])->assertNoContent();
    $this->get(route('home'))->assertDontSee('track.js');

    expect(SiteVisit::count())->toBe(0);
});

test('guests get the tracker and their interactions are recorded', function () {
    $this->get(route('home'))->assertSee('track.js');

    $this->withHeaders(['User-Agent' => PHONE_UA])
        ->postJson(route('track'), ['type' => 'event', 'name' => 'achievement', 'label' => 'Olimpiade Sains'])
        ->assertNoContent();

    $this->postJson(route('track'), ['type' => 'event', 'name' => 'drop table'])->assertUnprocessable();

    expect(SiteEvent::sole()->only(['name', 'label']))->toBe(['name' => 'achievement', 'label' => 'Olimpiade Sains']);
});

test('the same person on the same day is one visitor however many pages they open', function () {
    foreach (range(1, 3) as $ignored) {
        $this->withHeaders(['User-Agent' => PHONE_UA])->postJson(route('track'), ['type' => 'visit']);
    }
    $this->withHeaders(['User-Agent' => LAPTOP_UA])->postJson(route('track'), ['type' => 'visit']);

    $totals = (new AnalyticsReport(7))->totals();

    expect($totals['views'])->toBe(4)->and($totals['visitors'])->toBe(2)->and($totals['today'])->toBe(2);
});

test('the report compares against the previous period and ranks sources', function () {
    recordedVisit(['source' => 'WhatsApp', 'channel' => 'chat']);
    recordedVisit(['source' => 'WhatsApp', 'channel' => 'chat', 'visited_on' => today()->subDays(2)->toDateString()]);
    recordedVisit(['source' => 'Google', 'channel' => 'search', 'country' => 'ID']);
    recordedVisit(['visited_on' => today()->subDays(8)->toDateString()]);
    recordedVisit(['visited_on' => today()->subDays(40)->toDateString()]);

    $report = new AnalyticsReport(7);

    expect($report->totals())->toMatchArray(['visitors' => 3, 'views' => 3, 'visitors_change' => 200.0])
        ->and($report->breakdown('source')->all())->toBe([['label' => 'WhatsApp', 'value' => 2], ['label' => 'Google', 'value' => 1]])
        ->and($report->channels()->pluck('label')->all())->toBe(['Aplikasi pesan', 'Mesin pencari'])
        ->and($report->countries()->pluck('value')->all())->toBe([1])
        ->and(collect($report->series())->sum('value'))->toBe(3)
        ->and($report->series())->toHaveCount(7)
        ->and((new AnalyticsReport(365))->totals()['visitors'])->toBe(5);
});

test('user agents are read into device, browser, and bot flags', function (string $agent, string $device, string $browser, string $os) {
    $parsed = new UserAgent($agent);

    expect([$parsed->device(), $parsed->browser(), $parsed->os(), $parsed->isBot()])->toBe([$device, $browser, $os, false]);
})->with([
    'android chrome' => [PHONE_UA, 'mobile', 'Chrome', 'Android'],
    'mac safari' => [LAPTOP_UA, 'desktop', 'Safari', 'macOS'],
    'ipad' => ['Mozilla/5.0 (iPad; CPU OS 17_4 like Mac OS X) AppleWebKit/605.1.15 Version/17.4 Mobile/15E148 Safari/604.1', 'tablet', 'Safari', 'iOS'],
    'instagram in-app' => ['Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Instagram 320.0', 'mobile', 'Instagram', 'iOS'],
    'windows edge' => ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/126.0 Safari/537.36 Edg/126.0', 'desktop', 'Edge', 'Windows'],
]);

test('the statistics page shows the numbers and explains missing location data', function () {
    recordedVisit(['source' => 'WhatsApp', 'channel' => 'chat']);
    SiteEvent::create(['visitor' => sha1('a'), 'occurred_on' => today()->toDateString(), 'name' => 'achievement', 'label' => 'Olimpiade Sains Kota']);
    actingAsRole('staff');

    $this->get(route('content.analytics'))
        ->assertOk()
        ->assertSee('WhatsApp')
        ->assertSee('Olimpiade Sains Kota')
        ->assertSee('Lokasi belum tersedia')
        ->assertSee('utm_source=whatsapp');

    Livewire::test(Analytics::class)
        ->set('days', 12)
        ->assertSet('days', 30)
        ->set('days', 7)
        ->assertSee('Pengunjung per hari');
});

test('the statistics page has an empty state and guests cannot open it', function () {
    $this->get(route('content.analytics'))->assertRedirect('/login');

    actingAsAdmin();

    $this->get(route('content.analytics'))->assertOk()->assertSee('Belum ada kunjungan pada rentang ini');
});

test('old statistics are pruned', function () {
    recordedVisit(['visited_on' => today()->subMonths(SiteVisit::RETENTION_MONTHS + 1)->toDateString()]);
    $recent = recordedVisit();

    $this->artisan('model:prune', ['--model' => [SiteVisit::class, SiteEvent::class]])->assertSuccessful();

    expect(SiteVisit::pluck('id')->all())->toBe([$recent->id]);
});
