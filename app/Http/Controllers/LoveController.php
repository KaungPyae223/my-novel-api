<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLoveRequest;
use App\Http\Requests\UpdateLoveRequest;
use App\Models\Love;

class LoveController extends Controller
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
    public function store(StoreLoveRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Love $love)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLoveRequest $request, Love $love)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Love $love)
    {
        //
    }
}
