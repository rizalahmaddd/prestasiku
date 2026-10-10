<?php

namespace App\Models;

use App\Support\Site\SiteContent;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Pesan dari formulir kontak di halaman publik. Sengaja tidak Auditable: baris dibuat oleh
 * pengunjung (bisa spam) dan isinya data pribadi yang tidak perlu digandakan ke log aktivitas.
 */
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    public const STATUS_NEW = 'new';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_DONE = 'done';

    /**
     * @var array<string, array{label: string, color: string}>
     */
    public const STATUSES = [
        self::STATUS_NEW => ['label' => 'Baru', 'color' => 'amber'],
        self::STATUS_IN_PROGRESS => ['label' => 'Ditindaklanjuti', 'color' => 'sky'],
        self::STATUS_DONE => ['label' => 'Selesai', 'color' => 'emerald'],
    ];

    /**
     * @var array<string, string>
     */
    public const TOPICS = [
        'undangan' => 'Undangan lomba / acara',
        'kerja-sama' => 'Kerja sama / riset',
        'beasiswa' => 'Beasiswa / pembinaan',
        'lainnya' => 'Lainnya',
    ];

    /**
     * Lampiran disimpan di disk privat: isinya kiriman orang luar dan bisa memuat data pribadi,
     * jadi hanya boleh dibuka lewat dashboard (ContactAttachmentController).
     */
    public const ATTACHMENT_DISK = 'local';

    public const MAX_ATTACHMENTS = 3;

    public const MAX_ATTACHMENT_KB = 5120;

    protected $fillable = ['name', 'organization', 'phone', 'email', 'topic', 'message', 'attachments', 'status', 'note', 'handled_by', 'handled_at'];

    protected static function booted(): void
    {
        static::deleted(function (ContactMessage $message): void {
            Storage::disk(self::ATTACHMENT_DISK)->deleteDirectory($message->attachmentDirectory());
        });
    }

    public function attachmentDirectory(): string
    {
        return 'contact/'.$this->id;
    }

    /**
     * @return list<array{name: string, path: string, mime: string, size: int}>
     */
    public function attachmentList(): array
    {
        return array_values($this->attachments ?? []);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function topicLabel(): string
    {
        return self::TOPICS[$this->topic] ?? $this->topic;
    }

    /**
     * Tautan balas lewat WhatsApp dengan salam pembuka, atau null kalau pengirim tidak memberi nomor.
     */
    public function whatsappReplyUrl(): ?string
    {
        $number = SiteContent::normalizePhone((string) $this->phone);

        return $number ? 'https://wa.me/'.$number.'?text='.rawurlencode("Halo {$this->name}, terima kasih sudah menghubungi kami lewat website.") : null;
    }

    public function emailReplyUrl(): ?string
    {
        return $this->email ? 'mailto:'.$this->email.'?subject='.rawurlencode('Balasan: '.$this->topicLabel()) : null;
    }

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'handled_at' => 'datetime',
        ];
    }
}
