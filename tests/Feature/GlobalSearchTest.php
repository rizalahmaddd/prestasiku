<?php

use App\Models\Achievement;
use App\Models\User;
use App\Support\Features;
use Livewire\Volt\Volt;

/**
 * @return list<array{label: string, sub: ?string, url: string, flags: list<array{label: string, color: string}>}>
 */
function searchGroup(array $results, string $group): array
{
    return collect($results)->firstWhere('group', $group)['items'] ?? [];
}

test('matching part of a result label is bolded and the rest stays escaped', function () {
    actingAsAdmin();
    Achievement::factory()->create(['title' => 'PT Sinar <Jaya>']);

    Volt::test('layout.global-search')
        ->call('openSearch')
        ->set('query', 'sina')
        ->assertSeeHtml('PT <mark class="bg-transparent font-semibold text-slate-50">Sina</mark>r &lt;Jaya&gt;');
});

test('an achievement is found by title or organizer and opens its filtered list', function () {
    actingAsRole('staff');
    Achievement::factory()->hidden()->create(['title' => 'Olimpiade Kencana', 'organizer' => 'Puspresnas']);

    $byTitle = searchGroup(Volt::test('layout.global-search')->set('query', 'kencana')->get('results'), 'Prestasi');
    $byOrganizer = searchGroup(Volt::test('layout.global-search')->set('query', 'pusp')->get('results'), 'Prestasi');

    expect($byTitle[0]['url'])->toBe(route('content.achievements', ['cari' => 'Olimpiade Kencana']))
        ->and($byTitle[0]['flags'])->toBe([['label' => 'Disembunyikan', 'color' => 'slate']])
        ->and($byOrganizer[0]['label'])->toBe('Olimpiade Kencana');
});

test('achievements are hidden from the search when their feature is off', function () {
    actingAsAdmin();
    Achievement::factory()->create(['title' => 'Lomba Tersembunyi']);
    Features::setDisabled(['content.achievements']);

    expect(searchGroup(Volt::test('layout.global-search')->set('query', 'tersembunyi')->get('results'), 'Prestasi'))->toBe([]);
});

test('users are only searchable by roles that manage user roles', function () {
    User::factory()->create(['name' => 'Rahmat Hidayat', 'username' => 'rahmat']);

    actingAsAdmin();
    expect(searchGroup(Volt::test('layout.global-search')->set('query', 'rahmat')->get('results'), 'Pengguna'))->toHaveCount(1);

    actingAsRole('staff');
    expect(searchGroup(Volt::test('layout.global-search')->set('query', 'rahmat')->get('results'), 'Pengguna'))->toBe([]);
});

test('menus are searchable by partial words across group and label', function () {
    actingAsAdmin();

    $results = Volt::test('layout.global-search')->set('query', 'lap aktiv')->get('results');

    expect(collect(searchGroup($results, 'Menu'))->pluck('url')->all())->toBe([route('reports.activity-log')]);
});

test('menus the user cannot access are left out of the search', function () {
    actingAsRole('staff');

    $results = Volt::test('layout.global-search')->set('query', 'pengaturan')->get('results');

    expect(searchGroup($results, 'Menu'))->toBe([]);
});
