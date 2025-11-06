<?php

namespace App\Services;

use App\Models\Letter;

class LetterRelation
{
    protected $novel;

    public function __construct($novel)
    {
        $this->novel = $novel;
    }

    public function create(array $attributes)
    {
        $attributes['novel_id'] = $this->novel->id;
        return Letter::create($attributes);
    }

    public function get()
    {
        return Letter::where('novel_id', $this->novel->id)->get();
    }

   
}
