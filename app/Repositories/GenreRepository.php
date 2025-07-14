<?php

namespace App\Repositories;

use App\Models\Genre;

class GenreRepository
{

    protected $genre;

    public function __construct() {
        $this->genre = new Genre();
    }

    public function all()
    {
        return $this->genre->all();
    }

    public function create($data)
    {
        return $this->genre->create($data);
    }

}
