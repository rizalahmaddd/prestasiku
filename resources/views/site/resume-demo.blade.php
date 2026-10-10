{{-- Resume contoh untuk data demo (DemoContentSeeder). Resume asli diunggah dari Konten Halaman. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Resume {{ $content['name'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; }
        h1 { font-size: 22px; margin: 0; color: #0f172a; }
        h2 { font-size: 12px; margin: 22px 0 8px; padding-bottom: 4px; border-bottom: 1px solid #cbd5e1; color: #1e40af; }
        p { margin: 0 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 5px 0; vertical-align: top; border-bottom: 1px solid #e2e8f0; }
        .muted { color: #64748b; }
        .when { width: 70px; color: #64748b; }
        .result { width: 150px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $content['name'] }}</h1>
    <p class="muted">{{ collect([$content['grade'], $content['school_line']])->filter()->implode(' · ') }}</p>
    <p class="muted">{{ collect([$content['email'], \App\Support\Site\SiteContent::whatsappLabel()])->filter()->implode(' · ') }}</p>

    <h2>Profil</h2>
    <p>{{ $content['about_body'] }}</p>

    <h2>Pendidikan</h2>
    <table>
        @foreach ($educations as $education)
            <tr>
                <td class="result">{{ $education->period_label }}</td>
                <td><strong>{{ $education->name }}</strong><br><span class="muted">{{ $education->program }}</span></td>
            </tr>
        @endforeach
    </table>

    <h2>Prestasi Pilihan</h2>
    <table>
        @foreach ($achievements as $achievement)
            <tr>
                <td class="when">{{ $achievement->achieved_on->translatedFormat('M Y') }}</td>
                <td class="result">{{ $achievement->badge }}</td>
                <td>{{ $achievement->title }}<br><span class="muted">{{ $achievement->organizer }}</span></td>
            </tr>
        @endforeach
    </table>
</body>
</html>
