<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\MetaResource;
use App\Http\Resources\V1\NotificationResource;
use App\Http\Resources\V1\SearchResultResource;
use App\Models\Achievement;
use App\Support\Branding;
use App\Support\OpenApi\Attributes\ApiQuery;
use App\Support\OpenApi\Attributes\ApiResponse;
use App\Support\OpenApi\Attributes\ApiTag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[ApiTag('Referensi Aplikasi', 'Akun')]
class MetaController extends Controller
{
    /**
     * Konfigurasi & enum.
     *
     * Nama/logo aplikasi dan label semua pilihan tetap untuk mengisi form. Cukup diambil sekali
     * saat aplikasi dibuka.
     */
    public function meta(): MetaResource
    {
        return new MetaResource([
            'app' => [
                'name' => Branding::appName(),
                'tagline' => Branding::tagline(),
                'logo_url' => Branding::logoUrl(),
            ],
            'enums' => [
                'achievement_levels' => Achievement::LEVELS,
                'achievement_medals' => Achievement::MEDALS,
            ],
        ]);
    }

    /**
     * Pencarian global.
     *
     * Sama dengan kotak pencarian di web (prestasi, foto galeri, pengguna, dan data lain sesuai hak akses). `target` menunjuk data yang bisa dibuka lewat API.
     */
    #[ApiQuery('q', description: 'Minimal 2 karakter.', required: true)]
    #[ApiResponse(SearchResultResource::class, collection: true)]
    public function search(Request $request): AnonymousResourceCollection
    {
        $query = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']])['q'];

        $component = app('livewire')->new('layout.global-search');
        $component->query = $query;
        $component->search();

        $groups = collect($component->results)
            ->reject(fn (array $group) => $group['group'] === 'Menu')
            ->map(fn (array $group) => [
                'group' => $group['group'],
                'icon' => $group['icon'],
                'color' => $group['color'],
                'items' => array_map(fn (array $item) => [
                    'label' => $item['label'],
                    'sub' => $item['sub'],
                    'flags' => $item['flags'],
                    'target' => NotificationResource::targetOf($item['url']),
                ], $group['items']),
            ])
            ->values();

        return SearchResultResource::collection($groups);
    }
}
