<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Urutan tampil manual lewat kolom sort_order. Baris baru masuk paling akhir; pindah posisi
 * berarti bertukar sort_order dengan tetangganya di kelompok yang sama (lihat orderGroup()).
 */
trait Orderable
{
    protected static function bootOrderable(): void
    {
        static::creating(function (self $model): void {
            if (! $model->sort_order) {
                $model->sort_order = (int) $model->orderSiblings()->max('sort_order') + 1;
            }
        });
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Kolom yang membatasi kelompok urutan, mis. ['type'] supaya kategori prestasi dan galeri
     * punya urutan sendiri-sendiri.
     *
     * @return list<string>
     */
    protected function orderGroup(): array
    {
        return [];
    }

    /**
     * @return Builder<static>
     */
    protected function orderSiblings(): Builder
    {
        $query = static::query();

        foreach ($this->orderGroup() as $column) {
            $query->where($column, $this->{$column});
        }

        return $query;
    }

    public function moveUp(): void
    {
        $this->swapWith($this->orderSiblings()
            ->where(fn (Builder $q) => $q->where('sort_order', '<', $this->sort_order)
                ->orWhere(fn (Builder $q) => $q->where('sort_order', $this->sort_order)->where('id', '<', $this->id)))
            ->orderByDesc('sort_order')->orderByDesc('id')->first());
    }

    public function moveDown(): void
    {
        $this->swapWith($this->orderSiblings()
            ->where(fn (Builder $q) => $q->where('sort_order', '>', $this->sort_order)
                ->orWhere(fn (Builder $q) => $q->where('sort_order', $this->sort_order)->where('id', '>', $this->id)))
            ->orderBy('sort_order')->orderBy('id')->first());
    }

    private function swapWith(?self $neighbour): void
    {
        if ($neighbour === null) {
            return;
        }

        $mine = $this->sort_order;
        $theirs = $neighbour->sort_order;

        // Data lama bisa punya sort_order kembar; tanpa ini pertukaran tidak mengubah apa-apa.
        if ($mine === $theirs) {
            $theirs = $neighbour->id > $this->id ? $mine + 1 : max(0, $mine - 1);
        }

        static::withoutEvents(function () use ($neighbour, $mine, $theirs): void {
            $this->forceFill(['sort_order' => $theirs])->save();
            $neighbour->forceFill(['sort_order' => $mine])->save();
        });
    }
}
