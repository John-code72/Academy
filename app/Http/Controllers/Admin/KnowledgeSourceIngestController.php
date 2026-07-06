<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeSourceDocument;
use App\Services\KnowledgeIngestion\KnowledgeIngestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KnowledgeSourceIngestController extends Controller
{
    public function index()
    {
        $documents = KnowledgeSourceDocument::orderByDesc('updated_at')->paginate(20);

        return view('admin.knowledge_sources.index', compact('documents'));
    }

    public function ingest(Request $request, KnowledgeIngestionService $ingestion): JsonResponse
    {
        $validated = $request->validate([
            'source' => 'nullable|string|max:120',
            'force' => 'nullable|boolean',
        ]);

        $result = $ingestion->ingest(
            $validated['source'] ?? null,
            (bool) ($validated['force'] ?? false)
        );

        return response()->json([
            'ok' => $result['failed'] === 0,
            'summary' => $result,
        ]);
    }

    public function search(Request $request, KnowledgeIngestionService $ingestion): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:3|max:500',
            'department' => 'nullable|string|max:80',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $matches = $ingestion->search(
            $validated['q'],
            $validated['department'] ?? null,
            (int) ($validated['limit'] ?? 8)
        );

        return response()->json([
            'query' => $validated['q'],
            'count' => count($matches),
            'matches' => $matches,
        ]);
    }
}
