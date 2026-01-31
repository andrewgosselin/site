<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use DOMDocument;
use DOMXPath;

class INDController extends Controller
{
    /**
     * Display the IND Tool page.
     */
    public function index()
    {
        return Inertia::render('tools/INDTool');
    }

    /**
     * Fetch and parse the IND public register.
     * Cached for 24 hours.
     *
     * @unauthenticated
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRegister(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
        ]);

        $data = $this->getRegisterData();

        // Filter results if search query is present (legacy support)
        if ($request->has('search')) {
            $search = strtolower($request->get('search'));
            $data = array_values(array_filter($data, function ($item) use ($search) {
                return str_contains(strtolower($item['name']), $search) ||
                    str_contains($item['kvk_number'], $search);
            }));
        }

        return response()->json($data);
    }

    /**
     * Search the IND register by name or KvK.
     *
     * @unauthenticated
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string',
            'kvk' => 'nullable|string',
            'q' => 'nullable|string',
        ]);

        $data = $this->getRegisterData();
        $results = $data;

        if ($request->has('name')) {
            $name = strtolower($request->get('name'));
            $results = array_filter($results, function ($item) use ($name) {
                return str_contains(strtolower($item['name']), $name);
            });
        }

        if ($request->has('kvk')) {
            $kvk = $request->get('kvk');
            $results = array_filter($results, function ($item) use ($kvk) {
                return str_contains($item['kvk_number'], $kvk);
            });
        }

        // General search fallback
        if ($request->has('q')) {
            $search = strtolower($request->get('q'));
            $results = array_filter($results, function ($item) use ($search) {
                return str_contains(strtolower($item['name']), $search) ||
                    str_contains($item['kvk_number'], $search);
            });
        }

        return response()->json(array_values($results));
    }

    /**
     * Get the cached IND register data.
     * 
     * @return array
     */
    private function getRegisterData()
    {
        return Cache::remember('ind_register_data', 60 * 60 * 24, function () {
            $url = 'https://ind.nl/en/public-register-recognised-sponsors/public-register-regular-labour-and-highly-skilled-migrants';

            // Fetch the page content with a generic Chrome user agent
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ])->get($url);

            if (!$response->successful()) {
                return [];
            }

            $html = $response->body();

            // Suppress warnings due to malformed HTML
            libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            // Load HTML with options to handle encoding properly if needed, usually loadHTML is enough for basic scraping
            $dom->loadHTML($html);
            libxml_clear_errors();

            $xpath = new DOMXPath($dom);

            // Query all rows in the table
            $rows = $xpath->query('//table//tr');

            $results = [];

            // Skip the first row (header)
            $isHeader = true;
            foreach ($rows as $row) {
                if ($isHeader) {
                    $isHeader = false;
                    continue;
                }

                // The IND table uses <th> for the name and <td> for the KvK number
                // So we query for both tag types in the row
                $cells = $xpath->query('.//th|.//td', $row);

                // We expect at least 2 columns: Name (th) and KvK (td)
                if ($cells->length >= 2) {
                    // Normalize spaces to clean up weird formatting
                    $name = trim(preg_replace('/\s+/', ' ', $cells->item(0)->textContent));
                    $kvk = trim(preg_replace('/\s+/', ' ', $cells->item(1)->textContent));

                    if (!empty($name)) {
                        $results[] = [
                            'name' => $name,
                            'kvk_number' => $kvk,
                        ];
                    }
                }
            }

            return $results;
        });
    }
}
