<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\DashboardResource;
use App\Models\Achievement;
use App\Support\Features;
use App\Support\OpenApi\Attributes\ApiTag;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

#[ApiTag('Beranda', 'Akun')]
class DashboardController extends Controller
{
    /**
     * Beranda: ringkasan angka.
     *
     * Hanya angka yang boleh dilihat akun ini dan fiturnya aktif yang dikirim; `target` menyebut
     * daftar yang dibuka saat kartu diketuk, atau null bila daftarnya belum tersedia di API.
     */
    public function __invoke(Request $request): DashboardResource
    {
        $user = $request->user();
        $stats = [];

        if (Features::enabled('content.achievements') && $user->can('view-content')) {
            $stats[] = [
                'key' => 'achievements_published',
                'label' => 'Prestasi tampil di website',
                'count' => Achievement::where('is_published', true)->count(),
                'target' => null,
            ];
        }

        return new DashboardResource([
            'date' => today()->toDateString(),
            'unread_notifications' => $user->unreadNotifications()->get()
                ->filter(fn (DatabaseNotification $notification) => Features::allowsUrl($notification->data['url'] ?? null))
                ->count(),
            'stats' => $stats,
        ]);
    }
}
