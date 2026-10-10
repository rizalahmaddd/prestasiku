<?php

use App\Support\Navigation;

test('a group left with one link is shown as that link, larger groups stay grouped', function () {
    $items = collect(Navigation::forUser(actingAsSuperAdmin()))->pluck('items')->flatten(1);

    expect($items->pluck('route'))->toContain('reports.activity-log')
        ->and($items->pluck('key'))->not->toContain('reports')
        ->and($items->firstWhere('key', 'settings')['children'])->toHaveCount(4);
});
