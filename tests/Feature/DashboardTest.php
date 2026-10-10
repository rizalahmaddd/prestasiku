<?php

use App\Models\Achievement;
use App\Models\User;

test('root shows the public portfolio instead of redirecting to the dashboard', function () {
    $this->get('/')->assertOk()->assertSee('Lihat Koleksi Prestasi');
});

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('a user without a role sees an explanation instead of data', function () {
    Achievement::factory()->count(3)->create();
    $user = User::factory()->create();

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertSee('Akun Anda belum punya peran')
        ->assertDontSee('Aktivitas Terbaru');
});

test('staff sees the content counts but not the activity feed', function () {
    Achievement::factory()->count(3)->create();
    Achievement::factory()->hidden()->create();
    actingAsRole('staff');

    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('Prestasi')
        ->assertSee('3 tampil di website')
        ->assertSee('Lihat website')
        ->assertDontSee('Aktivitas Terbaru');
});

test('admin sees the recent activity feed', function () {
    actingAsAdmin();
    Achievement::factory()->create(['title' => 'PT Contoh Audit']);

    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('Aktivitas Terbaru')
        ->assertSee('PT Contoh Audit');
});
