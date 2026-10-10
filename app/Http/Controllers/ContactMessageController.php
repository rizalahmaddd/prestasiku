<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\ContactMessageReceived;
use App\Support\Features;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Menerima formulir kontak dari halaman publik (dikirim lewat fetch oleh public/site/site.js).
 */
class ContactMessageController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless(Features::enabled('content.messages'), 404);

        // Kolom jebakan: tersembunyi dari manusia, tapi bot pengisi formulir biasanya mengisinya.
        // Dibalas sukses supaya bot tidak mencoba cara lain.
        if (filled($request->input('contact_ref'))) {
            // Dicatat supaya bisa ditelusuri kalau ada pengunjung asli yang pesannya ikut tersaring.
            Log::info('Formulir kontak ditolak: kolom jebakan terisi.', ['name' => $request->input('name')]);

            return response()->json(['message' => __('Pesan terkirim.')]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'organization' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'required_without:email', 'string', 'regex:/^[0-9+\-\s()]{8,30}$/'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:150'],
            'topic' => ['required', Rule::in(array_keys(ContactMessage::TOPICS))],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
            'attachments' => ['nullable', 'array', 'max:'.ContactMessage::MAX_ATTACHMENTS],
            // Tanpa SVG/HTML: berkas ini nanti dibuka pengelola dari domain dashboard.
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:'.ContactMessage::MAX_ATTACHMENT_KB],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'phone.required_without' => 'Isi nomor WhatsApp atau email supaya kami bisa membalas.',
            'email.required_without' => 'Isi nomor WhatsApp atau email supaya kami bisa membalas.',
            'phone.regex' => 'Nomor WhatsApp tidak valid.',
            'email.email' => 'Alamat email tidak valid.',
            'topic.required' => 'Pilih keperluan Anda.',
            'topic.in' => 'Pilih keperluan Anda.',
            'message.required' => 'Pesan wajib diisi.',
            'message.min' => 'Pesan terlalu pendek, ceritakan sedikit keperluan Anda.',
            'max' => 'Isian ini terlalu panjang.',
            'attachments.max' => 'Lampiran paling banyak '.ContactMessage::MAX_ATTACHMENTS.' berkas.',
            'attachments.*.mimes' => 'Lampiran harus berupa foto (JPG, PNG, WebP) atau PDF.',
            'attachments.*.max' => 'Ukuran tiap lampiran paling besar 5 MB.',
            'attachments.*.file' => 'Lampiran gagal diunggah. Coba pilih ulang berkasnya.',
            'attachments.*.uploaded' => 'Lampiran gagal diunggah. Ukurannya mungkin terlalu besar.',
        ]);

        $message = ContactMessage::create(Arr::except($data, 'attachments'));

        if ($files = $request->file('attachments')) {
            $message->update(['attachments' => collect($files)->map(fn (UploadedFile $file): array => [
                'name' => Str::limit(basename($file->getClientOriginalName()), 150, ''),
                'path' => $file->store($message->attachmentDirectory(), ContactMessage::ATTACHMENT_DISK),
                'mime' => (string) $file->getMimeType(),
                'size' => $file->getSize(),
            ])->values()->all()]);
        }

        Notification::send(
            User::query()->get()->filter(fn (User $user): bool => $user->can('manage-content')),
            new ContactMessageReceived($message),
        );

        return response()->json(['message' => __('Pesan terkirim. Kami akan membalas lewat kontak yang Anda berikan.')], 201);
    }
}
