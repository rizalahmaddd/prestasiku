<?php

use App\Models\Achievement;
use App\Models\Media;
use App\Models\User;
use App\Support\Branding;
use App\Support\Site\SiteContent;
use Illuminate\Support\Facades\Storage;

test('app:install creates the first superadmin, roles, and branding', function () {
    $this->artisan('app:install', [
        '--app-name' => 'Aplikasi Baru',
        '--name' => 'Pemilik',
        '--email' => 'pemilik@example.test',
        '--username' => 'Pemilik',
        '--password' => 'rahasia123',
        '--no-interaction' => true,
    ])->assertSuccessful();

    $user = User::sole();

    expect($user->username)->toBe('pemilik')
        ->and($user->isSuperAdmin())->toBeTrue()
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and(Branding::appName())->toBe('Aplikasi Baru')
        ->and(Achievement::count())->toBe(0);
});

test('app:install stops without creating an account when the input is invalid', function () {
    $this->artisan('app:install', [
        '--app-name' => 'Aplikasi Baru',
        '--name' => 'Pemilik',
        '--email' => 'bukan-email',
        '--username' => 'pemilik',
        '--password' => 'pendek',
        '--no-interaction' => true,
    ])->assertFailed();

    expect(User::count())->toBe(0);
});

test('app:install --demo also seeds the demo website content', function () {
    Storage::fake('public');

    $this->artisan('app:install', [
        '--app-name' => 'Aplikasi Baru',
        '--name' => 'Pemilik',
        '--email' => 'pemilik@example.test',
        '--username' => 'pemilik',
        '--password' => 'rahasia123',
        '--demo' => true,
        '--no-interaction' => true,
    ])->assertSuccessful();

    expect(Achievement::count())->toBeGreaterThan(0)
        ->and(Media::count())->toBeGreaterThan(0)
        ->and(SiteContent::get('name'))->toBe('Kenzo Al-Fatih');
});
