<?php

use App\Livewire\Settings\BrandingSettings;
use App\Models\Setting;
use App\Support\Branding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('guests cannot view the branding settings page', function () {
    $this->get(route('settings.branding'))->assertRedirect('/login');
});

test('roles without the branding permission cannot view the page', function () {
    actingAsRole('staff');

    $this->get(route('settings.branding'))->assertForbidden();
    Livewire::test(BrandingSettings::class)->assertForbidden();
});

test('the app name and tagline from settings replace the brand across the layout', function () {
    actingAsAdmin();

    Livewire::test(BrandingSettings::class)
        ->set('app_name', 'Portofolio Alya')
        ->set('app_tagline', 'Kelola & Pantau')
        ->call('saveBranding')
        ->assertHasNoErrors()
        ->assertRedirect(route('settings.branding'));

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee('<title>Dashboard - Portofolio Alya</title>', false)
        ->assertSee('Kelola &amp; Pantau', false);
});

test('the app name is required', function () {
    actingAsAdmin();

    Livewire::test(BrandingSettings::class)
        ->set('app_name', '')
        ->call('saveBranding')
        ->assertHasErrors(['app_name' => 'required']);
});

test('the layout falls back to the configured app name when none is set', function () {
    config(['app.name' => 'Nama Dari Env']);
    actingAsAdmin();

    $this->get(route('dashboard'))->assertOk()->assertSee('<title>Dashboard - Nama Dari Env</title>', false);
});

test('an uploaded logo is served publicly and replaces the default mark', function () {
    Storage::fake('local');
    actingAsAdmin();

    Livewire::test(BrandingSettings::class)
        ->set('app_name', 'Portofolio Alya')
        ->set('logo', UploadedFile::fake()->image('logo.png', 200, 200))
        ->call('saveBranding')
        ->assertHasNoErrors();

    $path = Branding::logoPath();
    Storage::disk('local')->assertExists($path);

    $this->get(route('dashboard'))->assertSee(Branding::logoUrl(), false);

    auth()->logout();
    $this->get(route('login'))->assertSee(Branding::logoUrl(), false);
    $this->get(route('home'))->assertSee(Branding::logoUrl(), false);
    $this->get(Branding::logoUrl())->assertOk();
});

test('replacing or removing the logo deletes the previous file', function () {
    Storage::fake('local');
    actingAsAdmin();

    Livewire::test(BrandingSettings::class)
        ->set('app_name', 'Portofolio Alya')
        ->set('logo', UploadedFile::fake()->image('first.png'))
        ->call('saveBranding');
    $first = Branding::logoPath();

    Livewire::test(BrandingSettings::class)
        ->set('logo', UploadedFile::fake()->image('second.png'))
        ->call('saveBranding');
    $second = Branding::logoPath();

    Storage::disk('local')->assertMissing($first);
    Storage::disk('local')->assertExists($second);

    Livewire::test(BrandingSettings::class)->call('removeLogo');

    Storage::disk('local')->assertMissing($second);
    expect(Branding::logoPath())->toBeNull();
    $this->get(route('branding.logo'))->assertNotFound();
});

test('svg logos are rejected because they can carry scripts', function () {
    Storage::fake('local');
    actingAsAdmin();

    Livewire::test(BrandingSettings::class)
        ->set('app_name', 'Portofolio Alya')
        ->set('logo', UploadedFile::fake()->create('logo.svg', 5, 'image/svg+xml'))
        ->call('saveBranding')
        ->assertHasErrors('logo');
});

test('any signed-in account can load the logo', function () {
    Storage::fake('local');
    Storage::disk('local')->put('branding/logo-test.png', UploadedFile::fake()->image('logo.png')->getContent());
    Setting::put(Branding::LOGO_KEY, 'branding/logo-test.png');
    actingAsRole('staff');

    $this->get(Branding::logoUrl())->assertOk();
});
