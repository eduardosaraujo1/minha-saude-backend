<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreDocumentRequest;
use App\Http\Requests\V1\UpdateDocumentRequest;
use App\Models\Document;

class DocumentController extends Controller
{
    /**
     * Upload new document(s).
     */
    public function upload()
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Download a specific document.
     */
    public function download(Document $document)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentRequest $request, Document $document)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        return response()->json(['status' => 'not_implemented']);
    }
}
