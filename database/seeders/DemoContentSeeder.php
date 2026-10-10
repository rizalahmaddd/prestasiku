<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\Category;
use App\Models\Education;
use App\Models\GalleryItem;
use App\Models\Interest;
use App\Models\Location;
use App\Models\Media;
use App\Models\RoadmapStep;
use App\Models\Stat;
use App\Models\Testimonial;
use App\Support\Site\MediaStore;
use App\Support\Site\SiteContent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Konten contoh website: portofolio "Kenzo Al-Fatih" dari halaman statis awal, lengkap dengan
 * fotonya (database/seeders/media). Dipakai untuk pengembangan dan sebagai titik awal yang
 * tinggal diganti lewat menu Website. Dilewati kalau website sudah punya prestasi.
 *
 * sort_order diisi manual karena DatabaseSeeder mematikan event model.
 */
class DemoContentSeeder extends Seeder
{
    /** @var array<string, Media> */
    private array $media = [];

    public function run(): void
    {
        if (Achievement::query()->exists()) {
            return;
        }

        $this->seedMedia();
        $this->seedPageContent();
        $this->seedStats();
        $this->seedInterests();

        $locations = $this->seedLocations();
        $this->seedEducations($locations);
        $this->seedRoadmap();

        $gallery = $this->seedGallery();
        $this->seedAchievements($gallery, $locations);
        $this->seedTestimonials();
        $this->seedResume();
    }

    private function seedMedia(): void
    {
        $files = [
            'portrait-kenzo' => 'Foto profil Kenzo',
            'gallery-tokyo' => 'Podium Tokyo Tech 2025',
            'gallery-lab-robotika' => 'Lab robotika',
            'gallery-brin' => 'Sidang riset BRIN 2024',
            'gallery-catur' => 'Kejurda catur DKI',
            'gallery-osn' => 'Medali perak OSN IPA',
            'gallery-wro' => 'WRO RoboMission',
            'gallery-kpm' => 'Klinik MIPA',
            'gallery-labschool' => 'SMP Labschool Rawamangun',
            'gallery-singapura' => 'SASMO Singapura',
            'lokasi-alazhar' => 'SD Islam Al-Azhar 13',
            'lokasi-tokyo' => 'Kampus Tokyo Tech',
            'testimoni-rahmadi' => 'Drs. Rahmadi',
            'testimoni-agung' => 'Agung Wicaksono',
            'testimoni-maryam' => 'Siti Maryam',
        ];

        foreach ($files as $file => $name) {
            $this->media[$file] = MediaStore::fromPath(database_path("seeders/media/{$file}.jpg"), $name);
        }
    }

    private function seedPageContent(): void
    {
        SiteContent::putMany([
            'name' => 'Kenzo Al-Fatih',
            'grade' => 'Kelas IX',
            'school_line' => 'SMP Labschool Rawamangun, Jakarta',
            'tagline' => 'Pelajar & Peneliti Muda',
            'birth_date' => '2012-03-14',
            'portrait_id' => (string) $this->media['portrait-kenzo']->id,
            'highlight_1_label' => 'Sekolah Asal',
            'highlight_1_value' => 'SMP Labschool Rawamangun',
            'highlight_2_label' => 'Peringkat Akademik',
            'highlight_2_value' => 'Peringkat 1 Paralel (Rata-rata 94.8)',
            'highlight_3_label' => 'Fokus Bidang',
            'highlight_3_value' => 'Sains Terapan & Robotika Otonom',

            'hero_badge' => 'Portofolio Akademik',
            'hero_headline' => 'Eksplorasi Sains, Logika, &',
            'hero_headline_accent' => 'Dedikasi Belajar.',
            'hero_intro' => 'Laman dokumentasi rekam jejak **Kenzo Al-Fatih**. Menghimpun perjalanan belajar dari tingkat dasar, riset robotika otonom, olimpiade matematika, hingga kompetisi sains tingkat nasional dan internasional.',
            'hero_cta_primary' => 'Lihat Koleksi Prestasi',
            'hero_cta_secondary' => 'Riwayat Pendidikan',

            'about_eyebrow' => 'Profil & Karakter',
            'about_title' => 'Konsistensi Belajar & Semangat Menggali Solusi',
            'about_body' => 'Kenzo memandang kompetisi sebagai sarana memperluas wawasan dan menguji gagasan. Di luar jam sekolah, ia secara teratur melatih logika algoritma, membaca jurnal sains pemula, dan merakit rangkaian elektronika secara mandiri.',
            'education_eyebrow' => 'Perjalanan Akademik & Ekosistem Sekolah',
            'education_title' => 'Riwayat Pendidikan Formal & Peta Kampus',
            'education_body' => 'Menghubungkan rekam jejak sekolah formal, laboratorium riset robotika di Jakarta, hingga arena kompetisi internasional di Tokyo dan Singapura.',
            'venues_label' => 'Venue Internasional',
            'roadmap_eyebrow' => 'Perkembangan Berkelanjutan',
            'roadmap_title' => 'Alur Rekam Jejak 2017 – 2026',
            'roadmap_body' => 'Progres konsisten dari ketertarikan logika matematika dasar, penempaan sains berjenjang, hingga podium internasional.',
            'showcase_eyebrow' => 'Portofolio Prestasi',
            'showcase_title' => 'Koleksi Prestasi & Galeri Dokumentasi',
            'showcase_body' => 'Sembilan prestasi pilihan dari 28 raihan kejuaraan resmi, lengkap dengan nomor sertifikat dan dokumentasi saat pengujian sensor robotik, sidang riset BRIN, hingga panggung kompetisi internasional.',
            'gallery_title' => 'Album Foto Otentik & Panggung Perlombaan',
            'gallery_body' => 'Dokumentasi otentik saat pengujian modul sensor, penganugerahan piala, dan sidang riset.',
            'testimonials_eyebrow' => 'Rekomendasi Pendidik',
            'testimonials_title' => 'Catatan Guru & Pembina Olimpiade',

            'whatsapp' => '6285777775477',
            'email' => 'prestasi.kenzo@gmail.com',
            'whatsapp_message' => 'Halo Orang Tua Kenzo Al-Fatih, saya tertarik dengan portofolio Kenzo',
            'contact_cta' => 'Hubungi Wali',
            'contact_badge' => 'Narahubung Orang Tua / Wali',
            'contact_title' => 'Tertarik Mengundang atau Bekerja Sama?',
            'contact_body' => 'Terbuka untuk program pembinaan talenta riset, undangan kompetisi olimpiade internasional, serta beasiswa sekolah unggulan. Seluruh komunikasi dikoordinasikan langsung bersama orang tua / wali.',
            'resume_title' => 'Unduh Portofolio Resmi',
            'resume_body' => 'Dokumen ringkasan profil, riwayat pendidikan, dan daftar prestasi pilihan.',

            'seo_title' => 'Portofolio & Rekam Jejak Prestasi - Kenzo Al-Fatih',
            'seo_description' => 'Rekam jejak pendidikan dan prestasi olimpiade sains, robotika, dan riset Kenzo Al-Fatih.',
            'og_image_id' => null,
            'footer_title' => 'Kenzo Al-Fatih • Portofolio Siswa',
            'footer_note' => 'Dikelola oleh keluarga Kenzo Al-Fatih.',
        ]);
    }

    private function seedStats(): void
    {
        $stats = [
            [Stat::GROUP_SUMMARY, 18, null, 'Medali Emas & Juara 1', 'Olimpiade Sains & Robotika', 'amber'],
            [Stat::GROUP_SUMMARY, 28, '+', 'Total Penghargaan Resmi', 'Tingkat Kota hingga Dunia', 'slate'],
            [Stat::GROUP_SUMMARY, 5, null, 'Ajang Tingkat Internasional', 'Jepang, Singapura, dsb.', 'royal'],
            [Stat::GROUP_SUMMARY, 8, null, 'Karya Inovasi Mandiri', 'Prototipe IoT & Software AI', 'emerald'],
            [Stat::GROUP_ROADMAP, 9, '+', 'Tahun belajar', null, 'slate'],
            [Stat::GROUP_ROADMAP, 28, '+', 'Prestasi', null, 'royal'],
            [Stat::GROUP_ROADMAP, 2, null, 'Emas dunia', null, 'amber'],
        ];

        foreach ($stats as $index => [$group, $value, $suffix, $label, $caption, $tone]) {
            Stat::create(compact('group', 'value', 'suffix', 'label', 'caption', 'tone') + ['sort_order' => $index + 1]);
        }
    }

    private function seedInterests(): void
    {
        $interests = [
            ['Sains & Matematika Terapan', 'flask-conical', 'royal', 'Menekuni pemecahan soal analitik non-rutin, aljabar, dan konsep fisika dasar. Terbiasa membedah soal-soal berstandar OSN dan SIMCC sejak kelas 4 SD.', ['Logika Diskrit', 'Olimpiade IPA', 'Kalkulasi Cepat']],
            ['Robotika Otonom & IoT', 'cpu', 'amber', 'Mempelajari logika pemrograman berbasis C++ untuk mikrokontroler Arduino/ESP32 dan Lego Spike Prime. Mampu merancang alur otonom dengan sensor garis, sonar, dan inframerah.', ['Arduino C++', 'WRO RoboMission', 'Sensor Kalibrasi']],
            ['Catur & Komunikasi Ilmiah', 'crown', 'emerald', 'Menempa ketenangan berpikir strategis melalui catur cepat PERCASI serta melatih penyampaian argumen melalui pidato dan presentasi karya tulis ilmiah berbahasa Inggris.', ['Catur Cepat G-15', 'TOEFL Junior: 865', 'Public Speaking']],
        ];

        foreach ($interests as $index => [$title, $icon, $tone, $description, $tags]) {
            Interest::create(compact('title', 'icon', 'tone', 'description', 'tags') + ['sort_order' => $index + 1]);
        }
    }

    /**
     * @return array<string, Location>
     */
    private function seedLocations(): array
    {
        $locations = [
            'labschool' => ['SMP Labschool Rawamangun', null, 'Sekolah Formal Aktif • Program Pengayaan Sains & Robotika', 'Sekolah Utama Kenzo', 'Aktif (Kelas IX)', '#1d4ed8', -6.193262, 106.883713, 16, 'Jl. Pemuda No.1, Rawamangun, Kec. Pulo Gadung, Jakarta Timur, DKI Jakarta 13220', 'gallery-labschool', 'Laboratorium Sains & Gedung Utama SMP Labschool Rawamangun'],
            'alazhar' => ['SD Islam Al-Azhar 13 Rawamangun', null, 'Almamater Sekolah Dasar (2017 - 2023) • Valedictorian', 'Almamater SD Terbaik', 'Lulusan Terbaik', '#0f172a', -6.195300, 106.887200, 16, 'Jl. Sunan Giri No.1, Rawamangun, Kec. Pulo Gadung, Jakarta Timur, DKI Jakarta 13220', 'lokasi-alazhar', 'Kompleks Pendidikan Islam Al-Azhar Rawamangun'],
            'kpm' => ['Klinik Pendidikan MIPA & Sentra Robotika WRO', null, 'Pusat Pembinaan Penalaran Khusus & Pengujian Sensor C++', 'Pusat Pembinaan Khusus', 'Program Intensif', '#d97706', -6.597147, 106.799564, 15, 'Sentra Pelatihan MIPA & Workshop Robotika Mikrobot, Jabodetabek', 'gallery-lab-robotika', 'Arena Uji Lintasan Robot Otonom & Kalibrasi'],
            'tokyo' => ['Tokyo Institute of Technology (Tokyo Tech)', 'Tokyo Tech', 'Meguro, Tokyo, Jepang • Podium Emas Autonomous Rover 2025', 'Podium Internasional', 'Juara 1 Emas', '#dc2626', 35.6046, 139.6841, 14, '2-12-1 Ookayama, Meguro City, Tokyo 152-8550, Jepang', 'lokasi-tokyo', 'Kampus Utama Tokyo Tech & Arena STEM Cup'],
            'singapore' => ['Suntec Convention Centre (SIMCC)', 'Singapura', 'Suntec City, Singapura • Penganugerahan SASMO Gold Award 2024', 'Podium Asia Pasifik', 'Gold Award', '#0284c7', 1.2931, 103.8578, 14, '1 Raffles Blvd, Suntec City, Singapura 039593', 'gallery-singapura', 'Auditorium Suntec Singapura & Panggung Seremoni'],
        ];

        $created = [];
        $order = 0;

        foreach ($locations as $key => [$name, $shortName, $subtitle, $badge, $status, $color, $lat, $lng, $zoom, $address, $image, $caption]) {
            $created[$key] = Location::create([
                'name' => $name,
                'short_name' => $shortName,
                'subtitle' => $subtitle,
                'badge' => $badge,
                'status' => $status,
                'color' => $color,
                'lat' => $lat,
                'lng' => $lng,
                'zoom' => $zoom,
                'address' => $address,
                'image_id' => $this->media[$image]->id,
                'caption' => $caption,
                'sort_order' => ++$order,
            ]);
        }

        return $created;
    }

    /**
     * @param  array<string, Location>  $locations
     */
    private function seedEducations(array $locations): void
    {
        $educations = [
            ['SMP Labschool Rawamangun', 'Kelas Prestasi & Program Pengayaan Sains', '2023 - Sekarang (Aktif)', 'active', 'Jakarta Timur', 'Memimpin ekstrakurikuler Robotika dan aktif sebagai kontingen utama Olimpiade Sains Nasional (OSN). Rata-rata rapor 94.8 serta mewakili sekolah ke Tokyo dan Singapura.', 'Ranking 1 Paralel (2024 & 2025)', 'labschool'],
            ['SD Islam Al-Azhar 13', 'Lulusan Terbaik (Valedictorian)', '2017 - 2023 (Lulus)', 'done', 'Jakarta Timur', 'Mewakili sekolah di berbagai ajang MIPA sejak kelas 3 SD. Menerima anugerah Siswa Teladan Utama atas perolehan akumulatif 12 medali olimpiade SD.', 'Nilai Akhir Sempurna', 'alazhar'],
            ['Klinik MIPA (KPM) & Sentra Robotika', 'Penalaran Sains & Algoritma WRO', 'Program Pembinaan STEM', 'program', 'Bogor & Jakarta', 'Kurikulum pemecahan masalah non-konvensional MIPA serta pelatihan perakitan perangkat keras sensorik standar World Robot Olympiad.', 'Arena Uji Coba C++', 'kpm'],
        ];

        foreach ($educations as $index => [$name, $program, $period, $tone, $city, $description, $footnote, $location]) {
            Education::create([
                'name' => $name,
                'program' => $program,
                'period_label' => $period,
                'tone' => $tone,
                'city' => $city,
                'description' => $description,
                'footnote' => $footnote,
                'location_id' => $locations[$location]->id,
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function seedRoadmap(): void
    {
        $steps = [
            ['2017 – 2020', 'Fondasi Minat MIPA', 'Mulai menyukai logika matematika di SD Islam Al-Azhar 13 dan menjuarai catur pelajar tingkat kota.', 'Klub Sains & Catur', 'normal', 'catur'],
            ['2021 – 2023', 'Valedictorian & IKMC Paris', 'Skor sempurna 120/120 IKMC Paris, Emas Catur Junior DKI, dan lulus SD dengan predikat terbaik.', '12 Medali SD', 'normal', 'sains'],
            ['2023 – 2024', 'SMP Labschool & OSN', 'Pengayaan Sains Labschool Rawamangun, Ketua Robotika, dan Medali Perak OSN IPA Nasional Puspresnas.', 'Kontingen DKI', 'normal', 'sains'],
            ['2024 – 2025', 'Inovasi BRIN & Tokyo Tech', 'Juara 1 Inovasi Riset Remaja BRIN (EcoSort AI) dan Emas Robotika Otonom di Tokyo Tech, Jepang.', 'Podium Dunia', 'peak', 'internasional'],
            ['2025 – 2026', 'Eksplorasi Riset Lanjutan', 'Mematangkan portofolio machine learning terapan dan persiapan transisi jenjang SMA unggulan.', 'Tahap Berikutnya', 'next', 'riset'],
        ];

        foreach ($steps as $index => [$period, $title, $description, $tag, $state, $jumpTarget]) {
            RoadmapStep::create([
                'period' => $period,
                'title' => $title,
                'description' => $description,
                'tag' => $tag,
                'state' => $state,
                'jump_target' => $jumpTarget,
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * @return array<string, GalleryItem>
     */
    private function seedGallery(): array
    {
        $categories = [];

        foreach ([['podium', 'Podium & Medali', 'trophy'], ['robotik', 'Lab Robotika', 'cpu'], ['sains', 'Riset & Sains', 'flask-conical'], ['sekolah', 'Sekolah & Catur', 'school']] as $index => [$slug, $name, $icon]) {
            $categories[$slug] = Category::create(['type' => Category::TYPE_GALLERY, 'slug' => $slug, 'name' => $name, 'icon' => $icon, 'sort_order' => $index + 1]);
        }

        $items = [
            'tokyo' => ['podium', 'Podium Tokyo Tech 2025', 'Tokyo Institute of Technology', '2025-01-18', 'Penganugerahan Juara 1 Robotika di Tokyo, Jepang', 'Momen penerimaan piala emas pada ajang International Youth STEM Cup 2025 di Tokyo Institute of Technology. Kenzo memimpin tim autonomous rescue rover Indonesia menuntaskan rintangan simulasi bencana tanpa penalti waktu.', 'gallery-tokyo'],
            'lab' => ['robotik', 'Lab Robotika C++', 'Sentra Robotika Jakarta', '2024-12-14', 'Pengujian Modul Sensor Otonom & Kalibrasi', 'Sesi kalibrasi sensor inframerah dan penyesuaian rute pathfinding di laboratorium robotika. Menguji algoritma penghindaran rintangan dinamis berbasis mikrokontroler ESP32.', 'gallery-lab-robotika'],
            'brin' => ['sains', 'Sidang Riset BRIN 2024', 'Gedung BJ Habibie BRIN', '2024-10-24', 'Presentasi Sidang Ilmiah EcoSort AI di Hadapan Dewan BRIN', 'Penyampaian gagasan pemilah sampah otomatis EcoSort AI di hadapan dewan penilai riset BRIN & IYSA, mempertahankan tingkat akurasi klasifikasi citra 96.2%.', 'gallery-brin'],
            'catur' => ['sekolah', 'Kejurda Catur DKI', 'Percasi DKI Jakarta', '2023-07-15', 'Final Kejuaraan Daerah Catur Junior KU-12', 'Pertandingan catur cepat G-15 menit penentu gelar juara tanpa kekalahan KU-12. Mengamankan skor 6.5 dari 7 babak sistem Swiss resmi provinsi.', 'gallery-catur'],
            'osn' => ['podium', 'Medali Perak OSN IPA', 'Puspresnas Kemendikbudristek', '2024-09-10', 'Penganugerahan Medali Perak OSN SMP Tingkat Nasional', 'Penerimaan medali perak olimpiade sains nasional bidang IPA mewakili kontingen DKI Jakarta setelah menyelesaikan uji teori dan eksperimen praktikum.', 'gallery-osn'],
            'wro' => ['robotik', 'WRO RoboMission', 'Arena Seleksi WRO Nasional', '2023-08-28', 'Perakitan Perangkat Robotik World Robot Olympiad', 'Pengujian motor penggerak roda dan kestabilan sasis robot Spike Prime pada arena tugas logistik pelabuhan otonom, berhasil menuntaskan misi dalam 48 detik.', 'gallery-wro'],
            'kpm' => ['sains', 'Klinik MIPA & SASMO', 'KPM Jabodetabek', '2024-05-12', 'Latihan Intensif Penalaran Olimpiade Sains', 'Simulasi pengerjaan soal aljabar non-rutin dan persiapan olimpiade matematika internasional SASMO & IKMC Paris di Klinik Pendidikan MIPA.', 'gallery-kpm'],
            'labschool' => ['sekolah', 'SMP Labschool Rawamangun', 'Kampus Labschool Rawamangun', '2024-11-16', 'Edukasi & Pameran Sains Proyek Robotika Sekolah', 'Edukasi dan demonstrasi kerja sensor rover kepada adik-adik kelas serta guru pembimbing di selasar laboratorium sains SMP Labschool Rawamangun.', 'gallery-labschool'],
            'singapore' => ['podium', 'SASMO Gold Singapura', 'Suntec Convention Centre, Singapura', '2024-06-15', 'Seremoni Penghargaan SASMO Gold Award Singapura', 'Menerima penghargaan SASMO Gold Award di panggung internasional Suntec Singapura bersama perwakilan pelajar berbakat se-Asia Pasifik.', 'gallery-singapura'],
        ];

        $created = [];
        $order = 0;

        foreach ($items as $key => [$category, $badge, $place, $takenOn, $title, $description, $image]) {
            $created[$key] = GalleryItem::create([
                'category_id' => $categories[$category]->id,
                'badge' => $badge,
                'place' => $place,
                'taken_on' => $takenOn,
                'title' => $title,
                'description' => $description,
                'image_id' => $this->media[$image]->id,
                'sort_order' => ++$order,
            ]);
        }

        return $created;
    }

    /**
     * @param  array<string, GalleryItem>  $gallery
     * @param  array<string, Location>  $locations
     */
    private function seedAchievements(array $gallery, array $locations): void
    {
        $categories = [];

        foreach ([['robotika', 'Robotika & IoT'], ['sains', 'Sains & Matematika'], ['riset', 'Karya Tulis & Pidato'], ['catur', 'Catur']] as $index => [$slug, $name]) {
            $categories[$slug] = Category::create(['type' => Category::TYPE_ACHIEVEMENT, 'slug' => $slug, 'name' => $name, 'sort_order' => $index + 1]);
        }

        $achievements = [
            [
                'title' => 'International Youth STEM Robotics Cup', 'organizer' => 'Tokyo Institute of Technology • Tokyo, Jepang',
                'description' => 'Membangun robot autonomous penyelamat bencana "AeroScout Rover" berbasis pendeteksi korban inframerah dan sensor LIDAR pada arena uji standar kebencanaan.',
                'badge' => 'Juara 1 (Emas)', 'icon' => 'medal', 'tone' => 'amber', 'medal' => 'emas', 'level' => 'internasional', 'achieved_on' => '2025-01-18',
                'tags' => ['Tingkat Internasional', '42 Negara'], 'reference_no' => 'TYO-2025-089', 'keywords' => 'tokyo jepang emas juara 1', 'is_featured' => true,
                'cert_title' => 'Juara 1 (Gold Medal) - International Youth STEM Robotics Cup', 'cert_organizer' => 'Tokyo Institute of Technology, Tokyo, Jepang',
                'cert_number' => 'TYO-STEM-2025-089-GOLD', 'cert_predicate' => 'Juara 1 Kategori Autonomous Rescue Rover',
                'cert_description' => 'Mencapai skor navigasi otonom 100/100 dalam menyelesaikan rintangan simulasi bencana tanpa penalti waktu, bersaing dengan perwakilan pelajar dari 42 negara.',
                'cert_tags' => ['Internasional', 'Jepang', 'Tokyo Tech'],
                'cat' => ['robotika'], 'photo' => 'tokyo', 'loc' => 'tokyo',
            ],
            [
                'title' => 'Olimpiade Sains Nasional (OSN) SMP - Bidang IPA', 'organizer' => 'Puspresnas, Kemendikbudristek RI',
                'description' => 'Mewakili Provinsi DKI Jakarta setelah melewati tahapan seleksi kota dan provinsi, menyelesaikan tes teori sains komprehensif dan uji eksperimen lab.',
                'badge' => 'Medali Perak Nasional', 'icon' => 'medal', 'tone' => 'slate', 'medal' => 'perak', 'level' => 'nasional', 'achieved_on' => '2024-09-10',
                'tags' => ['Tingkat Nasional', '38 Provinsi'], 'reference_no' => 'OSN-24-IPA-41', 'keywords' => 'osn ipa perak kemendikbud',
                'cert_title' => 'Medali Perak Olimpiade Sains Nasional (OSN) SMP - Bidang IPA', 'cert_organizer' => 'Pusat Prestasi Nasional (Puspresnas), Kemendikbudristek RI',
                'cert_number' => 'PUSPRESNAS/OSN-SMP/2024/0912', 'cert_predicate' => 'Medali Perak Tingkat Nasional',
                'cert_description' => 'Mewakili kontingen Provinsi DKI Jakarta setelah lolos seleksi berjenjang tingkat kota dan provinsi, menuntaskan ujian teori dan praktikum laboratorium.',
                'cert_tags' => ['Nasional Resmi', 'Kemendikbudristek', 'OSN'],
                'cat' => ['sains'], 'photo' => 'osn', 'loc' => null,
            ],
            [
                'title' => 'Singapore and Asian Schools Math Olympiad (SASMO)', 'organizer' => 'SIMCC (Singapore International Math Contests Centre)',
                'description' => 'Menduduki jajaran peraih nilai tertinggi kelompok usia dan lolos otomatis sebagai delegasi Indonesia ke ajang final IJMO di Bali.',
                'badge' => 'Gold Award (Top 8%)', 'icon' => 'award', 'tone' => 'amber', 'medal' => 'emas', 'level' => 'internasional', 'achieved_on' => '2024-05-12',
                'tags' => ['Tingkat Asia', 'Matematika Olimpiade'], 'reference_no' => 'SASMO-ID-992', 'keywords' => 'sasmo singapura gold emas matematika',
                'cert_title' => 'SASMO Gold Award - Singapore Asian Schools Math Olympiad', 'cert_organizer' => 'Singapore International Math Contests Centre (SIMCC)',
                'cert_number' => 'SIMCC/SASMO/ID/2024/774', 'cert_predicate' => 'Gold Award (Jajaran 8% Teratas)',
                'cert_description' => 'Meraih skor medali emas dalam kompetisi pemecahan soal logika aljabar dan penalaran kombinatorika internasional.',
                'cert_tags' => ['Asia Pasifik', 'SIMCC Singapore', 'Matematika'],
                'cat' => ['sains'], 'photo' => 'singapore', 'loc' => 'singapore',
            ],
            [
                'title' => 'National Youth Scientific Research Fair', 'organizer' => 'BRIN (Badan Riset dan Inovasi Nasional) & IYSA',
                'description' => 'Mempresentasikan "EcoSort AI", prototipe kotak pemilah sampah anorganik berbasis sensor optik dan klasifikasi citra sederhana.',
                'badge' => 'Juara 1 Best Innovation', 'icon' => 'trophy', 'tone' => 'royal', 'medal' => 'emas', 'level' => 'nasional', 'achieved_on' => '2024-10-24',
                'tags' => ['Riset Terapan', 'BRIN & IYSA'], 'reference_no' => 'BRIN-2024-03', 'keywords' => 'brin inovasi ecosort juara 1 riset',
                'cert_title' => '1st Place Best Innovation - National Youth Scientific Research Fair', 'cert_organizer' => 'BRIN (Badan Riset dan Inovasi Nasional) & IYSA',
                'cert_number' => 'BRIN-IYSA/ENV/2024/011', 'cert_predicate' => 'Juara 1 Inovasi Sains & Lingkungan',
                'cert_description' => 'Pengembangan EcoSort AI: Prototipe kotak tempat sampah pintar berbasis pemrosesan citra kamera untuk penyortiran sampah anorganik.',
                'cert_tags' => ['Riset Terapan', 'BRIN', 'Inovasi'],
                'cat' => ['riset', 'robotika'], 'photo' => 'brin', 'loc' => null,
            ],
            [
                'title' => 'Kejuaraan Daerah Catur Junior PERCASI DKI Jakarta', 'organizer' => 'Pengurus Provinsi PERCASI DKI Jakarta',
                'description' => 'Memenangkan kategori catur cepat G-15 menit KU-12 dengan rekor tanpa kekalahan (skor 6.5 dari 7 babak Swiss).',
                'badge' => 'Juara 1 Emas Catur Cepat', 'icon' => 'crown', 'tone' => 'amber', 'medal' => 'emas', 'level' => 'provinsi', 'achieved_on' => '2023-07-15',
                'tags' => ['Tingkat Provinsi DKI', 'PERCASI Resmi'], 'reference_no' => 'DKI-CATUR-23', 'keywords' => 'catur percasi dki jakarta emas',
                'cert_title' => 'Juara 1 Kejuaraan Daerah Catur Junior DKI Jakarta', 'cert_organizer' => 'Pengurus Provinsi PERCASI DKI Jakarta',
                'cert_number' => 'PERCASI-DKI/KEJURDA/2023/KU12-01', 'cert_predicate' => 'Juara 1 Emas Catur Cepat (KU-12)',
                'cert_description' => 'Meraih skor 6.5 dari 7 babak Swiss System pada kejuaraan resmi catur junior provinsi.',
                'cert_tags' => ['Provinsi DKI', 'PERCASI', 'Catur Cepat'],
                'cat' => ['catur'], 'photo' => 'catur', 'loc' => null,
            ],
            [
                'title' => 'World Robot Olympiad (WRO) Indonesia - RoboMission', 'organizer' => 'Mikrobot & Asosiasi Robotika Indonesia',
                'description' => 'Menyelesaikan misi sorting kontainer otomatis pelabuhan dalam waktu 48 detik tanpa penalti poin teknis.',
                'badge' => 'Juara 2 (Perak Nasional)', 'icon' => 'medal', 'tone' => 'slate', 'medal' => 'perak', 'level' => 'nasional', 'achieved_on' => '2023-08-28',
                'tags' => ['Seleksi Nasional WRO', 'Junior Category'], 'reference_no' => 'WRO-ID-2023', 'keywords' => 'wro robomission perak juara 2',
                'cert_title' => 'Juara 2 Nasional World Robot Olympiad (WRO) - RoboMission Junior', 'cert_organizer' => 'World Robot Olympiad & Mikrobot Indonesia',
                'cert_number' => 'WRO-ID/2023/ROBO-JR-02', 'cert_predicate' => 'Juara 2 (Perak) Seleksi Nasional',
                'cert_description' => 'Menyelesaikan tantangan otomasi logistik pelabuhan menggunakan robot Spike Prime otonom dengan catatan waktu 48 detik.',
                'cert_tags' => ['WRO', 'Robotika Otonom', 'Nasional'],
                'cat' => ['robotika'], 'photo' => 'wro', 'loc' => null,
            ],
            [
                'title' => 'International Kangaroo Math Contest (IKMC)', 'organizer' => 'Association Kangourou Sans Frontières (AKSF) Paris',
                'description' => 'Mencatat skor sempurna pada seluruh 30 soal penalaran logika tanpa kesalahan, bersaing dengan puluhan ribu peserta se-Indonesia.',
                'badge' => 'Gold Award (Skor 120/120)', 'icon' => 'award', 'tone' => 'amber', 'medal' => 'emas', 'level' => 'internasional', 'achieved_on' => '2023-04-03',
                'tags' => ['Tingkat Internasional', 'Perfect Score'], 'reference_no' => 'IKMC-2023-120', 'keywords' => 'ikmc kangaroo skor sempurna 120 emas',
                'cert_title' => 'Perfect Scorer Gold Medal - International Kangaroo Math Contest', 'cert_organizer' => 'Association Kangourou Sans Frontières (AKSF) Paris',
                'cert_number' => 'IKMC-ID-2023-CADET-PERFECT', 'cert_predicate' => 'Skor Sempurna 120/120 & Medali Emas',
                'cert_description' => 'Menuntaskan seluruh butir soal penalaran logika tanpa pengurangan poin pada kategori peserta Cadet.',
                'cert_tags' => ['AKSF Paris', 'Skor 120/120', 'Internasional'],
                'cat' => ['sains'], 'photo' => 'kpm', 'loc' => null,
            ],
            [
                'title' => 'Youth English Oratorical Contest', 'organizer' => 'Universitas Indonesia & Mitra Edukasi',
                'description' => 'Membawakan pidato ilmiah berbahasa Inggris mengenai pemanfaatan etis teknologi bagi penanganan isu lingkungan di hadapan dewan akademisi.',
                'badge' => '1st Place & Best Speaker', 'icon' => 'mic', 'tone' => 'royal', 'medal' => 'emas', 'level' => 'nasional', 'achieved_on' => '2024-11-19',
                'tags' => ['Bahasa Inggris', 'Pidato Ilmiah'], 'reference_no' => 'UI-ENG-2024', 'keywords' => 'pidato bahasa inggris juara 1',
                'cert_title' => '1st Place & Best Speaker - Youth English Oratorical Contest', 'cert_organizer' => 'Universitas Indonesia & Mitra Edukasi Bahasa',
                'cert_number' => 'UI-ORATORICAL/2024/001', 'cert_predicate' => 'Juara 1 & Pembicara Terbaik',
                'cert_description' => 'Menyampaikan pidato ilmiah bahasa Inggris terkait etika pemanfaatan teknologi bagi pelajar dengan nilai artikulasi tertinggi.',
                'cert_tags' => ['Bahasa Inggris', 'Pidato Ilmiah', 'Universitas Indonesia'],
                'cat' => ['riset'], 'photo' => 'brin', 'loc' => null,
            ],
            [
                'title' => 'Tantangan Berpikir Komputasional Bebras Indonesia', 'organizer' => 'Biro Bebras Indonesia & Institut Teknologi Bandung (ITB)',
                'description' => 'Menuntaskan tantangan pemecahan masalah logika komputasi, dekomposisi struktur data, dan penalaran algoritma tingkat penggalang.',
                'badge' => 'Peringkat 1 Nasional', 'icon' => 'award', 'tone' => 'amber', 'medal' => 'emas', 'level' => 'nasional', 'achieved_on' => '2023-11-14',
                'tags' => ['Tingkat Nasional', 'Bebras Indonesia'], 'reference_no' => 'BEBRAS-2023', 'keywords' => 'bebras computational thinking itb juara 1 emas',
                'cert_title' => 'Peringkat 1 Nasional Tantangan Berpikir Komputasional Bebras', 'cert_organizer' => 'Biro Bebras Indonesia & Institut Teknologi Bandung (ITB)',
                'cert_number' => 'BEBRAS-ITB/2023/PENGALANG/01', 'cert_predicate' => 'Peringkat 1 Tingkat Nasional',
                'cert_description' => 'Kecakapan berpikir analitis pemecahan masalah algoritma dan abstraksi komputasional.',
                'cert_tags' => ['ITB Bandung', 'Algoritma', 'Bebras'],
                'cat' => ['sains', 'robotika'], 'photo' => 'labschool', 'loc' => null,
            ],
        ];

        foreach ($achievements as $index => $data) {
            $achievement = Achievement::create([
                ...collect($data)->except(['cat', 'photo', 'loc'])->all(),
                'gallery_item_id' => $gallery[$data['photo']]->id,
                'location_id' => $data['loc'] ? $locations[$data['loc']]->id : null,
                'sort_order' => $index + 1,
            ]);

            $achievement->categories()->sync(collect($data['cat'])->map(fn (string $slug) => $categories[$slug]->id)->all());
        }
    }

    private function seedTestimonials(): void
    {
        $testimonials = [
            ['Drs. Rahmadi, M.Pd.', 'Guru Pembina Olimpiade Sains & Matematika', 'testimoni-rahmadi', 'Kenzo memiliki ketajaman analitis yang sangat matang untuk usianya. Namun yang paling menonjol adalah kerendahan hatinya; ia selalu bersemangat membantu teman-teman sekelasnya yang kesulitan memahami materi matematika.'],
            ['Agung Wicaksono, S.T.', 'Kepala Laboratorium Robotika & Mentor WRO', 'testimoni-agung', 'Ketenangannya saat menghadapi kendala teknis saat uji coba robot di Tokyo mencerminkan kedewasaan mental seorang peneliti. Ia terbiasa mencari akar masalah secara sistematis dan tidak mudah menyerah.'],
            ['Siti Maryam, S.Pd.', 'Wali Kelas IX SMP Labschool Rawamangun', 'testimoni-maryam', 'Manajemen waktunya patut dijadikan teladan. Meskipun jadwal kompetisi dan pelatihannya padat, nilai akademik harian di seluruh mata pelajaran tetap berada di peringkat teratas.'],
        ];

        foreach ($testimonials as $index => [$name, $role, $image, $quote]) {
            Testimonial::create([
                'name' => $name,
                'role' => $role,
                'quote' => $quote,
                'image_id' => $this->media[$image]->id,
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * Halaman statis awal hanya mensimulasikan unduhan; di sini resume contoh benar-benar dibuat
     * dari data demo supaya tombol Resume PDF bisa dicoba.
     */
    private function seedResume(): void
    {
        $pdf = Pdf::loadView('site.resume-demo', [
            'content' => SiteContent::all(),
            'educations' => Education::query()->ordered()->get(),
            'achievements' => Achievement::query()->orderByDesc('achieved_on')->get(),
        ])->setPaper('a4');

        $path = 'resume/resume-demo.pdf';
        Storage::disk(Media::DISK)->put($path, $pdf->output());
        SiteContent::putMany([SiteContent::RESUME_KEY => $path]);
    }
}
