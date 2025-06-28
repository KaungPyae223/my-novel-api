<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNovelRequest;
use App\Http\Requests\UpdateNovelRequest;
use App\Models\Novel;

class NovelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNovelRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Novel $novel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNovelRequest $request, Novel $novel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Novel $novel)
    {
        //
    }
}
