<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;
use App\Repositories\GenreRepository;

class GenreController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $genreRepository;

    public function __construct(GenreRepository $genreRepository) {
        $this->genreRepository = $genreRepository;
    }

    public function index()
    {
        return $this->genreRepository->all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGenreRequest $request)
    {
        return $this->genreRepository->create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Genre $genre)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGenreRequest $request, Genre $genre)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Genre $genre)
    {
        //
    }
}
