<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShareRequest;
use App\Http\Requests\UpdateShareRequest;
use App\Models\Share;

class ShareController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShareRequest $request)
    {
        //
    }

    /**
     * Display the specified resource by code.
     */
    public function show($code)
    {
        return response()->json(['status' => 'not_implemented']);
    }

    /**
     * Remove the specified resource from storage by code.
     */
    public function destroy($code)
    {
        return response()->json(['status' => 'not_implemented']);
    }
}
