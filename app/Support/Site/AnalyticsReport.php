<?php

namespace App\Support\Site;

use App\Models\SiteEvent;
use App\Models\SiteVisit;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Angka-angka halaman Statistik Pengunjung untuk satu rentang tanggal. "Pengunjung" berarti
 * pengunjung unik per hari: pengenalnya berganti tiap hari, jadi orang yang datang di dua hari
 * berbeda terhitung dua kali.
 */
class AnalyticsReport
{
    public readonly CarbonImmutable $from;

    public readonly CarbonImmutable $to;

    public function __construct(public readonly int $days)
    {
        $this->to = CarbonImmutable::today();
        $this->from = $this->to->subDays($days - 1);
    }

    /**
     * @return array{visitors: int, views: int, per_day: float, visitors_change: ?float, views_change: ?float, today: int}
     */
    public function totals(): array
    {
        $visitors = $this->visitors($this->visits());
        $views = $this->visits()->count();

        $previous = SiteVisit::query()->whereBetween('visited_on', [
            $this->from->subDays($this->days)->toDateString(),
            $this->from->subDay()->toDateString(),
        ]);
        $previousVisitors = $this->visitors(clone $previous);
        $previousViews = $previous->count();

        return [
            'visitors' => $visitors,
            'views' => $views,
            'per_day' => round($visitors / $this->days, 1),
            'visitors_change' => $this->change($visitors, $previousVisitors),
            'views_change' => $this->change($views, $previousViews),
            'today' => $this->visitors(SiteVisit::query()->whereDate('visited_on', $this->to->toDateString())),
        ];
    }

    /**
     * Pengunjung per hari, per minggu (rentang 90 hari), atau per bulan (setahun) supaya
     * jumlah batang grafiknya tetap terbaca.
     *
     * @return list<array{label: string, value: int}>
     */
    public function series(): array
    {
        $perDay = $this->visits()
            ->selectRaw('visited_on, count(distinct visitor) as total')
            ->groupBy('visited_on')
            ->pluck('total', 'visited_on')
            ->mapWithKeys(fn ($total, $date) => [substr((string) $date, 0, 10) => (int) $total]);

        $buckets = [];

        foreach (CarbonPeriod::create($this->from, $this->to) as $day) {
            [$key, $label] = match (true) {
                $this->days > 120 => [$day->format('Y-m'), $day->translatedFormat('M y')],
                $this->days > 31 => [$day->startOfWeek()->format('Y-m-d'), $day->startOfWeek()->translatedFormat('j M')],
                default => [$day->format('Y-m-d'), $day->translatedFormat($this->days > 7 ? 'j' : 'D j')],
            };

            $buckets[$key] ??= ['label' => $label, 'value' => 0];
            $buckets[$key]['value'] += $perDay[$day->format('Y-m-d')] ?? 0;
        }

        return array_values($buckets);
    }

    public function seriesUnit(): string
    {
        return match (true) {
            $this->days > 120 => 'per bulan',
            $this->days > 31 => 'per minggu',
            default => 'per hari',
        };
    }

    /**
     * Pengunjung dikelompokkan menurut satu kolom, terbanyak dulu.
     *
     * @return Collection<int, array{label: string, value: int}>
     */
    public function breakdown(string $column, int $limit = 8, bool $skipEmpty = false): Collection
    {
        return $this->visits()
            ->when($skipEmpty, fn (Builder $query) => $query->whereNotNull($column))
            ->selectRaw("{$column} as label, count(distinct visitor) as total")
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => ['label' => (string) $row->label, 'value' => (int) $row->total]);
    }

    /**
     * @return Collection<int, array{label: string, value: int}>
     */
    public function channels(): Collection
    {
        return $this->breakdown('channel')->map(fn (array $row): array => [
            'label' => TrafficSource::CHANNELS[$row['label']] ?? $row['label'],
            'value' => $row['value'],
        ]);
    }

    /**
     * @return Collection<int, array{label: string, value: int}>
     */
    public function countries(): Collection
    {
        return $this->breakdown('country', 8, skipEmpty: true)->map(fn (array $row): array => [
            'label' => self::countryName($row['label']),
            'value' => $row['value'],
        ]);
    }

    /**
     * Tayangan per jam (0-23) menurut zona waktu aplikasi.
     *
     * @return list<array{label: string, value: int}>
     */
    public function hours(): array
    {
        $perHour = $this->visits()->selectRaw('hour, count(*) as total')->groupBy('hour')->pluck('total', 'hour');

        return collect(range(0, 23))
            ->map(fn (int $hour): array => ['label' => str_pad((string) $hour, 2, '0', STR_PAD_LEFT), 'value' => (int) ($perHour[$hour] ?? 0)])
            ->all();
    }

    /**
     * @return array<string, int>
     */
    public function eventCounts(): array
    {
        $counts = $this->events()->selectRaw('name, count(*) as total')->groupBy('name')->pluck('total', 'name');

        return collect(array_keys(SiteEvent::NAMES))->mapWithKeys(fn (string $name) => [$name => (int) ($counts[$name] ?? 0)])->all();
    }

    /**
     * @return Collection<int, array{label: string, value: int}>
     */
    public function topEvents(string $name, int $limit = 6): Collection
    {
        return $this->events()
            ->where('name', $name)
            ->whereNotNull('label')
            ->selectRaw('label, count(distinct visitor) as total')
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row): array => ['label' => (string) $row->label, 'value' => (int) $row->total]);
    }

    /**
     * @return Collection<int, SiteVisit>
     */
    public function recent(int $limit = 12): Collection
    {
        return $this->visits()->latest('id')->limit($limit)->get();
    }

    public function hasLocationData(): bool
    {
        return $this->visits()->whereNotNull('country')->exists();
    }

    public static function countryName(string $code): string
    {
        $name = class_exists(\Locale::class) ? \Locale::getDisplayRegion('-'.$code, 'id') : '';

        return $name !== '' && $name !== $code ? $name : $code;
    }

    /**
     * @return Builder<SiteVisit>
     */
    private function visits(): Builder
    {
        return SiteVisit::query()->whereBetween('visited_on', [$this->from->toDateString(), $this->to->toDateString()]);
    }

    /**
     * @return Builder<SiteEvent>
     */
    private function events(): Builder
    {
        return SiteEvent::query()->whereBetween('occurred_on', [$this->from->toDateString(), $this->to->toDateString()]);
    }

    /**
     * @param  Builder<SiteVisit>  $query
     */
    private function visitors(Builder $query): int
    {
        return (int) $query->distinct()->count('visitor');
    }

    private function change(int $current, int $previous): ?float
    {
        return $previous > 0 ? round(($current - $previous) / $previous * 100, 1) : null;
    }
}
