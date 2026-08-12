<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use App\Services\DocumentService;
use App\Services\DocumentChunkService;

class DocumentController extends Controller
{
    private DocumentService $documentService;
    private DocumentChunkService $chunkService;

    public function __construct(
        DocumentService $documentService,
        DocumentChunkService $chunkService
    ) {
        $this->documentService = $documentService;
        $this->chunkService = $chunkService;
    }

    public function index()
    {
        $documents = auth()->user()
            ->documents()
            ->latest()
            ->get();

        return view('documents.index', compact('documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:txt,pdf', 'max:10240'],
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

        $this->chunkService->createChunks($document);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document uploaded successfully.');
    }

    public function destroy(Document $document)
    {
        abort_unless(
            $document->user_id === auth()->id(),
            403
        );

        if ($document->file_path) {
            \Storage::delete($document->file_path);
        }

        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }
}