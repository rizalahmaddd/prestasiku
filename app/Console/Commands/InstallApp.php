<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use App\Support\Branding;
use Database\Seeders\DemoContentSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('app:install
    {--app-name= : Nama dashboard yang tampil di sidebar, login, dan judul tab}
    {--name= : Nama akun superadmin}
    {--email= : Email akun superadmin}
    {--username= : Username akun superadmin}
    {--password= : Password akun superadmin}
    {--demo : Isi juga konten contoh website (portofolio demo beserta fotonya)}')]
#[Description('Siapkan project baru: migrasi, peran & izin, branding, dan akun superadmin pertama')]
class InstallApp extends Command
{
    public function handle(): int
    {
        $this->components->info('Menyiapkan aplikasi baru.');

        $this->call('migrate', ['--force' => true]);
        $this->callSilently('db:seed', ['--class' => RoleSeeder::class, '--force' => true]);
        $this->callSilently('db:seed', ['--class' => PermissionSeeder::class, '--force' => true]);
        $this->components->task('Peran & izin bawaan');

        $appName = $this->option('app-name') ?: text('Nama aplikasi', default: (string) config('app.name'), required: true);

        Setting::put(Branding::APP_NAME_KEY, $appName);
        $this->components->task('Branding aplikasi');

        // Gambar website disajikan dari disk public, jadi butuh symlink public/storage.
        if (! app()->runningUnitTests() && ! file_exists(public_path('storage'))) {
            $this->callSilently('storage:link');
            $this->components->task('Tautan penyimpanan gambar');
        }

        $user = $this->createSuperadmin();

        if ($user === null) {
            return self::FAILURE;
        }

        if ($this->option('demo') || ($this->input->isInteractive() && confirm('Isi konten contoh website (portofolio demo beserta fotonya)?', default: false))) {
            $this->callSilently('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);
            $this->components->task('Konten contoh website');
        }

        $this->newLine();
        $this->components->info("Selesai. Login sebagai {$user->email} di ".url('/login'));

        return self::SUCCESS;
    }

    private function createSuperadmin(): ?User
    {
        $data = [
            'name' => $this->option('name') ?: text('Nama superadmin', required: true),
            'email' => $this->option('email') ?: text('Email superadmin', required: true),
            'username' => $this->option('username') ?: text('Username superadmin', default: 'superadmin', required: true),
            'password' => $this->option('password') ?: password('Password superadmin (min. 8 karakter)', required: true),
        ];
        $data['username'] = strtolower(trim($data['username']));

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => User::usernameRules(),
            'password' => ['required', 'string', 'min:8'],
        ], User::identityValidationMessages());

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return null;
        }

        $user = User::create($data);
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole('superadmin');

        $this->components->task("Akun superadmin {$user->email}");

        return $user;
    }
}
