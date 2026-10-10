<?php

namespace App\Livewire\Content;

use App\Livewire\Concerns\WithCrudActions;
use App\Livewire\Concerns\WithMediaPicker;
use Livewire\Component;

/**
 * Dasar semua halaman daftar konten website: CRUD modal dari WithCrudActions, pemilih gambar,
 * dan tombol naik/turun untuk urutan tampil.
 */
abstract class ContentCrud extends Component
{
    use WithCrudActions, WithMediaPicker;

    /**
     * Pencarian ikut di URL supaya hasil pencarian global bisa membuka daftar yang sudah tersaring.
     *
     * @var array<string, array<string, string>>
     */
    protected $queryString = ['search' => ['as' => 'cari', 'except' => '']];

    public function moveUp(int $id): void
    {
        $this->authorizeManage();
        $this->modelClass()::findOrFail($id)->moveUp();
    }

    public function moveDown(int $id): void
    {
        $this->authorizeManage();
        $this->modelClass()::findOrFail($id)->moveDown();
    }

    protected function mediaFields(): array
    {
        return [];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'max.string' => ':Attribute maksimal :max karakter.',
            'max.numeric' => ':Attribute maksimal :max.',
            'min.numeric' => ':Attribute minimal :min.',
            'integer' => ':Attribute harus berupa angka bulat.',
            'numeric' => ':Attribute harus berupa angka.',
            'between' => ':Attribute harus di antara :min dan :max.',
            'date' => ':Attribute bukan tanggal yang valid.',
            'in' => 'Pilihan :attribute tidak tersedia.',
            'exists' => 'Pilihan :attribute tidak ditemukan.',
            'unique' => ':Attribute sudah dipakai.',
            'regex' => 'Format :attribute tidak sesuai.',
            'alpha_dash' => ':Attribute hanya boleh berisi huruf, angka, dan tanda hubung.',
        ];
    }

    /**
     * "Robotika, IoT , " menjadi ['Robotika', 'IoT'].
     *
     * @return list<string>
     */
    protected function tagsFromText(string $text): array
    {
        return collect(explode(',', $text))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>|null  $tags
     */
    protected function tagsToText(?array $tags): string
    {
        return implode(', ', $tags ?? []);
    }
}
