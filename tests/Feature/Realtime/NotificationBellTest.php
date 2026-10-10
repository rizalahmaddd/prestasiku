<?php

use App\Livewire\Layout\NotificationBell;
use App\Models\User;
use App\Support\Features;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Klik notifikasi wajib menandainya dibaca DAN pindah ke halaman yang dimaksud, bukan cuma
 * menandai dibaca tanpa aksi lanjutan.
 */
beforeEach(function () {
    $this->recipient = User::factory()->create();
    $this->recipient->assignRole(seededRole('admin'));

    $this->notification = DatabaseNotification::create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\BackupFinishedNotification',
        'notifiable_type' => $this->recipient->getMorphClass(),
        'notifiable_id' => $this->recipient->id,
        'data' => ['message' => 'Prestasi baru ditambahkan', 'url' => route('content.achievements', absolute: false)],
    ]);
});

test('opening a notification marks it read and redirects to its page', function () {
    expect($this->notification)->not->toBeNull();

    $this->actingAs($this->recipient);

    Livewire::test(NotificationBell::class)
        ->call('open', $this->notification->id)
        ->assertRedirect(route('content.achievements', absolute: false));

    expect($this->recipient->fresh()->unreadNotifications)->toHaveCount(0);
});

test('opening a notification belonging to someone else does nothing', function () {
    actingAsAdmin();

    Livewire::test(NotificationBell::class)
        ->call('open', $this->notification->id)
        ->assertNoRedirect();

    expect($this->recipient->fresh()->unreadNotifications)->toHaveCount(1);
});

test('notifications pointing at a disabled feature are hidden', function () {
    $this->actingAs($this->recipient);
    Features::setDisabled(['content.achievements']);

    Livewire::test(NotificationBell::class)
        ->assertViewHas('unreadCount', 0);
});
