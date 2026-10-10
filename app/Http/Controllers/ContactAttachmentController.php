<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Membuka lampiran pesan kontak dari disk privat untuk pengelola yang sedang login.
 */
class ContactAttachmentController extends Controller
{
    public function __invoke(ContactMessage $message, int $index): StreamedResponse
    {
        $attachment = $message->attachmentList()[$index] ?? null;
        $disk = Storage::disk(ContactMessage::ATTACHMENT_DISK);

        abort_unless($attachment !== null && $disk->exists($attachment['path']), 404);

        // nosniff: berkasnya kiriman orang luar, browser tidak boleh menebak tipenya sendiri.
        return $disk->response($attachment['path'], $attachment['name'], [
            'Content-Type' => $attachment['mime'],
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
