<?php

namespace App\Http\Controllers;

use App\Models\SiteEvent;
use App\Support\Site\VisitRecorder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/**
 * Penerima sinyal dari public/site/track.js. Selalu membalas 204 tanpa isi: halaman publik
 * tidak perlu tahu apakah sinyalnya dicatat atau dilewati.
 */
class SiteTrackController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['visit', 'event'])],
            'referrer' => ['nullable', 'string', 'max:2000'],
            'query' => ['nullable', 'string', 'max:1000'],
            'name' => ['required_if:type,event', 'nullable', Rule::in(array_keys(SiteEvent::NAMES))],
            'label' => ['nullable', 'string', 'max:300'],
        ]);

        $recorder = new VisitRecorder($request);

        if ($recorder->shouldRecord()) {
            $data['type'] === 'visit'
                ? $recorder->recordVisit($data)
                : $recorder->recordEvent($data['name'], $data['label'] ?? null);
        }

        return response()->noContent();
    }
}
