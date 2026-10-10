<?php

namespace App\Livewire\Content;

use App\Support\Site\AnalyticsReport;
use App\Support\Site\TrafficSource;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app', ['heading' => 'Statistik Pengunjung'])]
#[Title('Statistik Pengunjung')]
class Analytics extends Component
{
    public const RANGES = [7 => '7 hari', 30 => '30 hari', 90 => '90 hari', 365 => '1 tahun'];

    #[Url(as: 'rentang', except: 30)]
    public int $days = 30;

    public function mount(): void
    {
        $this->normalizeRange();
    }

    public function updatedDays(): void
    {
        $this->normalizeRange();
    }

    public function render()
    {
        $report = new AnalyticsReport($this->days);
        $totals = $report->totals();

        return view('livewire.content.analytics', [
            'report' => $report,
            'totals' => $totals,
            'events' => $report->eventCounts(),
            'isEmpty' => $totals['views'] === 0,
            'shareLinks' => collect(TrafficSource::SHARE_TARGETS)
                ->map(fn (string $label, string $source): array => ['label' => $label, 'url' => route('home', ['utm_source' => $source])])
                ->values(),
        ]);
    }

    private function normalizeRange(): void
    {
        if (! isset(self::RANGES[$this->days])) {
            $this->days = 30;
        }
    }
}
