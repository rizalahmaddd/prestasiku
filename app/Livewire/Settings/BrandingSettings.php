<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use App\Support\Branding;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Branding dashboard (nama, tagline, logo). Semua dibaca lewat App\Support\Branding, jadi tidak
 * ada nama aplikasi yang di-hardcode di layout. Identitas di website publik diatur terpisah di
 * Konten Halaman.
 */
#[Layout('layouts.app', ['heading' => 'Branding Aplikasi'])]
#[Title('Branding Aplikasi')]
class BrandingSettings extends Component
{
    use WithFileUploads;

    public string $app_name = '';

    public string $app_tagline = '';

    public ?TemporaryUploadedFile $logo = null;

    public function mount(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $this->app_name = Setting::get(Branding::APP_NAME_KEY, '');
        $this->app_tagline = Setting::get(Branding::APP_TAGLINE_KEY, '');
    }

    public function saveBranding(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $validated = $this->validate([
            'app_name' => ['required', 'string', 'max:60'],
            'app_tagline' => ['nullable', 'string', 'max:80'],
            // Tanpa SVG: file disajikan dari domain aplikasi sendiri, SVG bisa membawa skrip.
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
        ], [], ['app_name' => 'nama aplikasi', 'app_tagline' => 'tagline', 'logo' => 'logo']);

        Setting::putMany([
            Branding::APP_NAME_KEY => $validated['app_name'],
            Branding::APP_TAGLINE_KEY => $validated['app_tagline'] ?: null,
        ]);

        if ($this->logo) {
            $path = $this->logo->storeAs('branding', 'logo-'.Str::lower(Str::random(12)).'.'.$this->logo->extension(), 'local');
            $this->deleteStoredLogo();
            Setting::put(Branding::LOGO_KEY, $path);
            $this->logo = null;
        }

        activity('settings')->causedBy(Auth::user())->log("Branding aplikasi diubah: {$validated['app_name']}.");

        session()->flash('notify', ['message' => __('Branding aplikasi tersimpan.'), 'type' => 'success']);
        $this->redirectRoute('settings.branding', navigate: true);
    }

    public function removeLogo(): void
    {
        abort_unless(Auth::user()->can('update', Setting::class), 403);

        $this->deleteStoredLogo();
        Setting::put(Branding::LOGO_KEY, null);

        activity('settings')->causedBy(Auth::user())->log('Logo aplikasi dihapus.');

        session()->flash('notify', ['message' => __('Logo dihapus.'), 'type' => 'success']);
        $this->redirectRoute('settings.branding', navigate: true);
    }

    protected function deleteStoredLogo(): void
    {
        if ($path = Branding::logoPath()) {
            Storage::disk('local')->delete($path);
        }
    }

    public function render()
    {
        return view('livewire.settings.branding');
    }
}
