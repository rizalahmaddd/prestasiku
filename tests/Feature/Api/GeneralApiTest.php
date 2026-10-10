<?php

use App\Models\Achievement;
use App\Support\Features;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

it('shows only the stats a role may see', function () {
    Achievement::factory()->count(2)->create();
    Achievement::factory()->hidden()->create();

    apiActingAs('staff');
    $this->getJson('/api/v1/dashboard')
        ->assertOk()
        ->assertJsonPath('data.stats.0.key', 'achievements_published')
        ->assertJsonPath('data.stats.0.count', 2);
});

it('drops stats of disabled features', function () {
    apiActingAs('admin');
    Features::setDisabled(['content']);

    expect($this->getJson('/api/v1/dashboard')->json('data.stats'))->toBe([]);
});

it('lists notifications and marks them read', function () {
    $user = apiActingAs('admin');
    DatabaseNotification::create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\BackupFinishedNotification',
        'notifiable_type' => $user->getMorphClass(),
        'notifiable_id' => $user->id,
        'data' => ['message' => 'Backup selesai', 'url' => route('settings.backups', absolute: false)],
    ]);
    Sanctum::actingAs($user);

    $notification = $this->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonPath('meta.unread_count', 1)
        ->assertJsonPath('data.0.message', 'Backup selesai')
        ->assertJsonPath('data.0.target', null)
        ->json('data.0');

    $this->postJson("/api/v1/notifications/{$notification['id']}/read")->assertOk();
    $this->getJson('/api/v1/notifications/unread-count')->assertJsonPath('data.unread_count', 0);
});

it('searches achievements the same way as the web search box', function () {
    apiActingAs('staff');
    Achievement::factory()->create(['title' => 'Lomba Pencarian Unik']);

    $this->getJson('/api/v1/search?q=Pencarian')
        ->assertOk()
        ->assertJsonPath('data.0.group', 'Prestasi')
        ->assertJsonPath('data.0.items.0.label', 'Lomba Pencarian Unik');
});

it('returns app configuration in meta', function () {
    apiActingAs('staff');

    $this->getJson('/api/v1/meta')
        ->assertOk()
        ->assertJsonStructure(['data' => ['app' => ['name', 'tagline', 'logo_url'], 'enums' => ['achievement_levels', 'achievement_medals']]]);
});
