<?php

use App\Livewire\Content\Messages;
use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\ContactMessageReceived;
use App\Support\Features;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function contactPayload(array $overrides = []): array
{
    return $overrides + [
        'name' => 'Ibu Ratna',
        'organization' => 'Panitia Olimpiade Kota',
        'phone' => '0812-3456-7890',
        'email' => '',
        'topic' => 'undangan',
        'message' => 'Kami ingin mengundang ananda sebagai peserta olimpiade bulan depan.',
    ];
}

test('a visitor can send a message and content managers are notified', function () {
    Notification::fake();
    $admin = User::factory()->create()->assignRole(seededRole('admin'));
    $staff = User::factory()->create()->assignRole(seededRole('staff'));

    $this->get(route('home'))->assertSee('Tulis Pesan')->assertSee('id="contact-form"', false);

    $this->postJson(route('contact'), contactPayload())
        ->assertCreated()
        ->assertJsonPath('message', 'Pesan terkirim. Kami akan membalas lewat kontak yang Anda berikan.');

    $message = ContactMessage::sole();

    expect($message->only(['name', 'topic', 'status', 'email']))->toBe(['name' => 'Ibu Ratna', 'topic' => 'undangan', 'status' => 'new', 'email' => null])
        ->and($message->whatsappReplyUrl())->toStartWith('https://wa.me/6281234567890?text=Halo%20Ibu%20Ratna');

    Notification::assertSentTo($admin, ContactMessageReceived::class, fn (ContactMessageReceived $notification) => $notification->toArray($admin)['url'] === route('content.messages', absolute: false));
    Notification::assertNotSentTo($staff, ContactMessageReceived::class);
});

test('a message needs a way to reply and a real message', function () {
    $this->postJson(route('contact'), contactPayload(['phone' => '', 'email' => '', 'message' => 'hai', 'topic' => 'spam']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['phone', 'email', 'message', 'topic'])
        ->assertJsonPath('errors.phone.0', 'Isi nomor WhatsApp atau email supaya kami bisa membalas.');

    $this->postJson(route('contact'), contactPayload(['phone' => '', 'email' => 'panitia@example.test']))->assertCreated();

    expect(ContactMessage::count())->toBe(1);
});

test('bots that fill the hidden field are ignored without being told', function () {
    $this->postJson(route('contact'), contactPayload(['contact_ref' => 'http://spam.example']))->assertOk();

    expect(ContactMessage::count())->toBe(0);
});

test('the form is rate limited per visitor', function () {
    foreach (range(1, 5) as $ignored) {
        $this->postJson(route('contact'), contactPayload())->assertCreated();
    }

    $this->postJson(route('contact'), contactPayload())->assertTooManyRequests();
});

test('switching the feature off removes the form from the website', function () {
    Features::setDisabled(['content.messages']);

    $this->get(route('home'))->assertDontSee('Tulis Pesan')->assertDontSee('id="contact-form"', false);
    $this->postJson(route('contact'), contactPayload())->assertNotFound();
});

test('the inbox lists what still needs follow-up first', function () {
    ContactMessage::factory()->create(['name' => 'Pengirim Baru']);
    ContactMessage::factory()->create(['name' => 'Pengirim Selesai', 'status' => ContactMessage::STATUS_DONE]);
    actingAsRole('staff');

    $this->get(route('content.messages'))->assertOk()->assertSee('Pengirim Baru')->assertDontSee('Pengirim Selesai');

    Livewire::test(Messages::class)
        ->set('filter', ContactMessage::STATUS_DONE)
        ->assertSee('Pengirim Selesai')
        ->assertDontSee('Pengirim Baru')
        ->set('filter', 'tidak-ada')
        ->assertSet('filter', 'open');
});

test('an admin follows up a message with a status and a note', function () {
    $admin = actingAsAdmin();
    $message = ContactMessage::factory()->create(['email' => 'panitia@example.test']);

    Livewire::test(Messages::class)
        ->call('openEditModal', $message->id)
        ->assertSee($message->message)
        ->assertSee('Balas lewat WhatsApp')
        ->assertSee('mailto:panitia@example.test', false)
        ->set('status', ContactMessage::STATUS_IN_PROGRESS)
        ->set('note', 'Sudah dibalas lewat WhatsApp.')
        ->call('save')
        ->assertHasNoErrors();

    $message->refresh();

    expect($message->status)->toBe(ContactMessage::STATUS_IN_PROGRESS)
        ->and($message->note)->toBe('Sudah dibalas lewat WhatsApp.')
        ->and($message->handled_by)->toBe($admin->id)
        ->and($message->handled_at)->not->toBeNull();

    Livewire::test(Messages::class)->call('markAs', $message->id, ContactMessage::STATUS_DONE);
    expect($message->fresh()->status)->toBe(ContactMessage::STATUS_DONE);

    Livewire::test(Messages::class)->call('confirmDelete', $message->id)->call('delete');
    expect(ContactMessage::count())->toBe(0);
});

test('read-only roles cannot change or delete messages, and nobody can add one by hand', function () {
    $message = ContactMessage::factory()->create();
    actingAsRole('staff');

    Livewire::test(Messages::class)->call('markAs', $message->id, ContactMessage::STATUS_DONE)->assertForbidden();
    Livewire::test(Messages::class)->call('confirmDelete', $message->id)->assertForbidden();

    actingAsAdmin();
    Livewire::test(Messages::class)->call('openCreateModal')->assertNotFound();

    expect($message->fresh()->status)->toBe(ContactMessage::STATUS_NEW);
});

test('the dashboard flags unread messages', function () {
    ContactMessage::factory()->count(2)->create();
    actingAsAdmin();

    $this->get(route('dashboard'))->assertSee('Pesan Baru')->assertSee('Menunggu ditindaklanjuti');
});

test('a message can carry photo and pdf attachments that only the dashboard can open', function () {
    Storage::fake('local');

    $this->post(route('contact'), contactPayload([
        'attachments' => [
            UploadedFile::fake()->image('poster acara.jpg', 600, 400),
            UploadedFile::fake()->create('Surat Undangan.pdf', 120, 'application/pdf'),
        ],
    ]), ['Accept' => 'application/json'])->assertCreated();

    $message = ContactMessage::sole();
    $attachments = $message->attachmentList();

    expect($attachments)->toHaveCount(2)
        ->and($attachments[1])->toMatchArray(['name' => 'Surat Undangan.pdf', 'mime' => 'application/pdf'])
        ->and($attachments[0]['path'])->toStartWith("contact/{$message->id}/");
    Storage::disk('local')->assertExists($attachments[0]['path']);

    $url = route('content.messages.attachment', [$message, 1]);
    $this->get($url)->assertRedirect('/login');

    actingAsRole('staff');
    $this->get($url)->assertOk()->assertHeader('content-type', 'application/pdf')->assertHeader('x-content-type-options', 'nosniff');
    $this->get(route('content.messages.attachment', [$message, 5]))->assertNotFound();

    actingAsAdmin();
    Livewire::test(Messages::class)->call('openEditModal', $message->id)->assertSee('Surat Undangan.pdf')->assertSee('poster acara.jpg');

    Livewire::test(Messages::class)->call('confirmDelete', $message->id)->call('delete');
    Storage::disk('local')->assertMissing($attachments[0]['path']);
    Storage::disk('local')->assertMissing($attachments[1]['path']);
});

test('attachments that are too many, too big, or the wrong type are rejected', function (array $files, string $errorKey, string $error) {
    Storage::fake('local');

    $this->post(route('contact'), contactPayload(['attachments' => $files]), ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([$errorKey => $error]);

    expect(ContactMessage::count())->toBe(0);
})->with([
    'wrong type' => [fn () => [UploadedFile::fake()->create('skrip.html', 5, 'text/html')], 'attachments.0', 'Lampiran harus berupa foto (JPG, PNG, WebP) atau PDF.'],
    'svg' => [fn () => [UploadedFile::fake()->create('gambar.svg', 5, 'image/svg+xml')], 'attachments.0', 'Lampiran harus berupa foto (JPG, PNG, WebP) atau PDF.'],
    'too big' => [fn () => [UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf')], 'attachments.0', 'Ukuran tiap lampiran paling besar 5 MB.'],
    'too many' => [fn () => array_map(fn (int $i) => UploadedFile::fake()->image("foto{$i}.jpg"), range(1, 4)), 'attachments', 'Lampiran paling banyak 3 berkas.'],
]);
