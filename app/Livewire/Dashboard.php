<?php

namespace App\Livewire;

use App\Models\Achievement;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\GalleryItem;
use App\Models\Media;
use App\Models\Setting;
use App\Models\User;
use App\Support\Features;
use App\Support\Site\AnalyticsReport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.app', ['heading' => 'Dashboard'])]
#[Title('Dashboard')]
class Dashboard extends Component
{
    /**
     * @return array{name: string, grade: string, school: string, tagline: ?string, hasResume: bool, hasWhatsapp: bool}
     */
    public function siteProfile(): array
    {
        return [
            'name' => (string) Setting::get('site.name', 'Kenzo Al-Fatih'),
            'grade' => (string) Setting::get('site.grade', 'Kelas IX'),
            'school' => (string) Setting::get('site.school_line', 'SMP Labschool Rawamangun, Jakarta'),
            'tagline' => Setting::get('site.tagline', 'Pelajar & Peneliti Muda'),
            'hasResume' => ! empty(Setting::get('site.resume_path')),
            'hasWhatsapp' => ! empty(Setting::get('site.whatsapp')),
        ];
    }

    /**
     * @return array{new: int, in_progress: int, done: int, open: int, total: int, recent: Collection<int, ContactMessage>}|null
     */
    public function messagesSummary(): ?array
    {
        if (! Auth::user()->can('view-content') || ! Features::enabled('content.messages')) {
            return null;
        }

        $new = ContactMessage::where('status', ContactMessage::STATUS_NEW)->count();
        $inProgress = ContactMessage::where('status', ContactMessage::STATUS_IN_PROGRESS)->count();
        $done = ContactMessage::where('status', ContactMessage::STATUS_DONE)->count();

        return [
            'new' => $new,
            'in_progress' => $inProgress,
            'done' => $done,
            'open' => $new + $inProgress,
            'total' => $new + $inProgress + $done,
            'recent' => ContactMessage::latest('id')->limit(4)->get(),
        ];
    }

    /**
     * @return array{total: int, published: int, international: int, national: int, recent: Collection<int, Achievement>, categories: Collection<int, array{label: string, value: int}>}|null
     */
    public function achievementsSummary(): ?array
    {
        if (! Auth::user()->can('view-content') || ! Features::enabled('content.achievements')) {
            return null;
        }

        $total = Achievement::count();
        $published = Achievement::where('is_published', true)->count();
        $international = Achievement::where('level', 'internasional')->count();
        $national = Achievement::where('level', 'nasional')->count();

        $categories = Category::where('type', 'achievement')
            ->withCount('achievements')
            ->orderByDesc('achievements_count')
            ->get()
            ->map(fn (Category $c): array => [
                'label' => $c->name,
                'value' => (int) $c->achievements_count,
            ]);

        return [
            'total' => $total,
            'published' => $published,
            'international' => $international,
            'national' => $national,
            'recent' => Achievement::with('categories')->latest('achieved_on')->latest('id')->limit(4)->get(),
            'categories' => $categories,
        ];
    }

    /**
     * @return list<array{label: string, value: int}>|null
     */
    public function analyticsSeries(): ?array
    {
        if (! Auth::user()->can('view-content') || ! Features::enabled('content.analytics')) {
            return null;
        }

        return (new AnalyticsReport(7))->series();
    }

    /**
     * @return array<int, array{title: string, value: string, icon: string, tone: string, subtitle: ?string, href: ?string, trend?: ?float, trendLabel?: ?string}>
     */
    public function stats(): array
    {
        $user = Auth::user();
        $stats = [];

        if ($user->can('view-content')) {
            if (Features::enabled('content.messages')) {
                $new = ContactMessage::where('status', ContactMessage::STATUS_NEW)->count();
                $inProgress = ContactMessage::where('status', ContactMessage::STATUS_IN_PROGRESS)->count();
                $done = ContactMessage::where('status', ContactMessage::STATUS_DONE)->count();
                $open = $new + $inProgress;

                $stats[] = [
                    'title' => $new > 0 ? 'Pesan Baru' : 'Pesan Masuk',
                    'value' => (string) ($new > 0 ? $new : ($open > 0 ? $open : '0')),
                    'icon' => 'inbox',
                    'tone' => $new > 0 ? 'amber' : ($open > 0 ? 'sky' : 'slate'),
                    'subtitle' => $new > 0
                        ? 'Menunggu ditindaklanjuti'
                        : ($open > 0 ? "{$open} sedang ditindaklanjuti" : ($done > 0 ? "{$done} pesan selesai" : 'Belum ada pesan')),
                    'href' => route('content.messages'),
                ];
            }

            if (Features::enabled('content.achievements')) {
                $achTotal = Achievement::count();
                $achPublished = Achievement::where('is_published', true)->count();
                $stats[] = [
                    'title' => 'Prestasi',
                    'value' => number_format($achTotal, 0, ',', '.'),
                    'icon' => 'trophy',
                    'tone' => 'slate',
                    'subtitle' => number_format($achPublished, 0, ',', '.').' tampil di website',
                    'href' => route('content.achievements'),
                ];
            }

            if (Features::enabled('content.analytics')) {
                $visitors = (new AnalyticsReport(7))->totals();
                $stats[] = [
                    'title' => 'Pengunjung 7 Hari',
                    'value' => number_format($visitors['visitors'], 0, ',', '.'),
                    'icon' => 'chart-no-axes-combined',
                    'tone' => 'emerald',
                    'trend' => $visitors['visitors_change'],
                    'trendLabel' => 'Dibanding 7 hari sebelumnya',
                    'subtitle' => $visitors['today'].' hari ini',
                    'href' => route('content.analytics'),
                ];
            }

            if (Features::enabled('content.gallery')) {
                $stats[] = [
                    'title' => 'Foto Galeri',
                    'value' => number_format(GalleryItem::count(), 0, ',', '.'),
                    'icon' => 'camera',
                    'tone' => 'slate',
                    'subtitle' => number_format(Media::count(), 0, ',', '.').' gambar di pustaka',
                    'href' => route('content.gallery'),
                ];
            }
        }

        if ($user->isSuperAdmin()) {
            $stats[] = [
                'title' => 'Pengguna',
                'value' => number_format(User::count(), 0, ',', '.'),
                'icon' => 'user-cog',
                'tone' => 'slate',
                'subtitle' => null,
                'href' => route('settings.roles-and-permissions'),
            ];
        }

        $unread = $user->unreadNotifications()->count();
        if ($unread > 0 || ! $user->can('view-content')) {
            $stats[] = [
                'title' => 'Notifikasi Belum Dibaca',
                'value' => (string) $unread,
                'icon' => 'bell',
                'tone' => $unread > 0 ? 'amber' : 'slate',
                'subtitle' => null,
                'href' => null,
            ];
        }

        return $stats;
    }

    /**
     * @return array<int, array{label: string, icon: string, href: string, hint: string, badge?: ?string}>
     */
    public function shortcuts(): array
    {
        $user = Auth::user();
        $links = [];

        if ($user->can('view-content')) {
            if (Features::enabled('content.messages')) {
                $new = ContactMessage::where('status', ContactMessage::STATUS_NEW)->count();
                $links[] = [
                    'label' => 'Pesan Masuk',
                    'icon' => 'inbox',
                    'href' => route('content.messages'),
                    'hint' => $new > 0 ? "{$new} pesan baru menunggu balasan" : 'Formulir kontak & tindak lanjut',
                    'badge' => $new > 0 ? (string) $new : null,
                ];
            }
            if (Features::enabled('content.achievements')) {
                $links[] = ['label' => 'Prestasi', 'icon' => 'trophy', 'href' => route('content.achievements'), 'hint' => 'Tambah atau kelola kartu prestasi'];
            }
            if (Features::enabled('content.page')) {
                $links[] = ['label' => 'Konten Halaman', 'icon' => 'layout-template', 'href' => route('content.page'), 'hint' => 'Nama, hero, kontak, dan teks halaman'];
            }
            if (Features::enabled('content.gallery')) {
                $links[] = ['label' => 'Galeri', 'icon' => 'camera', 'href' => route('content.gallery'), 'hint' => 'Unggah foto dokumentasi kegiatan'];
            }
            if (Features::enabled('content.locations')) {
                $links[] = ['label' => 'Lokasi & Peta', 'icon' => 'map-pin', 'href' => route('content.locations'), 'hint' => 'Atur titik venue di peta'];
            }
        }

        $links[] = ['label' => 'Profil Saya', 'icon' => 'user-round', 'href' => route('profile'), 'hint' => 'Ubah nama, kontak, dan password'];

        return $links;
    }

    /**
     * @return Collection<int, Activity>|null
     */
    public function recentActivities(): ?Collection
    {
        if (! Auth::user()->can('viewAny', Activity::class)) {
            return null;
        }

        return Activity::query()->with('causer')->latest('id')->limit(6)->get();
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'siteProfile' => $this->siteProfile(),
            'messagesSummary' => $this->messagesSummary(),
            'achievementsSummary' => $this->achievementsSummary(),
            'analyticsSeries' => $this->analyticsSeries(),
            'stats' => $this->stats(),
            'shortcuts' => $this->shortcuts(),
            'activities' => $this->recentActivities(),
        ]);
    }
}
