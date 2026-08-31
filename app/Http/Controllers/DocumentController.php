<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use App\Services\DocumentService;
use App\Services\DocumentChunkService;
use App\Services\QdrantService;
use App\Jobs\ProcessDocumentJob;
use Illuminate\Support\Facades\Gate;

class DocumentController extends Controller
{
    private DocumentService $documentService;
    private DocumentChunkService $chunkService;
    private QdrantService $qdrantService;

    public function __construct(
        DocumentService $documentService,
        DocumentChunkService $chunkService,
        QdrantService $qdrantService
    ) {
        $this->documentService = $documentService;
        $this->chunkService = $chunkService;
        $this->qdrantService = $qdrantService;
    }

    public function index()
    {
        Gate::authorize('viewAny', Document::class);

        $documents = auth()->user()
            ->documents()
            ->latest()
            ->get();

        return view('documents.index', compact('documents'));
    }

    public function store(Request $request)
    {
         Gate::authorize('create', Document::class);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:txt,pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $file = $request->file('file');

        $path = $file->store('documents');

        $fullPath = storage_path('app/private/' . $path);

        $content = $this->documentService->extractText($fullPath);

        $document = auth()->user()->documents()->create([
            'title' => $request->title,
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'content' => $content,
        ]);

        ProcessDocumentJob::dispatch($document);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document uploaded successfully.');
    }

    public function destroy(Document $document)
    {
        Gate::authorize('delete', $document);

        $this->qdrantService->deleteByDocument($document->id);

        if ($document->file_path) {
            \Storage::delete($document->file_path);
        }

        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    public function view(Document $document)
    {
        Gate::authorize('view', $document);

        $path = storage_path('app/private/' . $document->file_path);

        abort_unless(file_exists($path), 404);

        return response()->file($path, [
            'Content-Type' => $document->mime_type,
        ]);
    }
}