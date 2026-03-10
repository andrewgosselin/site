<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class ToolsController extends Controller
{
    public function index()
    {
        return Inertia::render('tools/Index');
    }

    public function json()
    {
        return Inertia::render('tools/JsonTool');
    }

    public function encoder()
    {
        return Inertia::render('tools/EncoderTool');
    }

    public function generator()
    {
        return Inertia::render('tools/GeneratorTool');
    }

    public function seoChecker()
    {
        return Inertia::render('tools/MetaViewerTool');
    }

    public function image()
    {
        return Inertia::render('tools/ImageTool');
    }

    public function pdf()
    {
        return Inertia::render('tools/PDFTool');
    }

    public function translateText(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:20000'],
            'target_language' => ['required', 'string', 'size:2'],
            'source_language' => ['nullable', 'string', 'size:2'],
        ]);

        $sourceLanguage = $validated['source_language'] ?? 'auto';
        $targetLanguage = strtolower($validated['target_language']);

        $response = Http::timeout(20)->get('https://translate.googleapis.com/translate_a/single', [
            'client' => 'gtx',
            'sl' => $sourceLanguage,
            'tl' => $targetLanguage,
            'dt' => 't',
            'q' => $validated['text'],
        ]);

        if (!$response->ok()) {
            return response()->json([
                'message' => 'Translation service is currently unavailable.',
            ], 503);
        }

        $payload = $response->json();
        if (!is_array($payload) || !isset($payload[0]) || !is_array($payload[0])) {
            return response()->json([
                'message' => 'Could not parse translation response.',
            ], 502);
        }

        $translatedSegments = [];
        foreach ($payload[0] as $segment) {
            if (is_array($segment) && isset($segment[0]) && is_string($segment[0])) {
                $translatedSegments[] = $segment[0];
            }
        }

        return response()->json([
            'translated_text' => implode('', $translatedSegments),
        ]);
    }
}
