<?php

namespace App\Repositories;

use App\Models\Novel;

class NovelRepository
{

    protected $novel;

    public function __construct() {
        $this->novel = new Novel();
    }

    public function findNovel($id)
    {
        return $this->novel->find($id);
    }



    public function all()
    {
        return $this->novel->all();
    }

    public function create($data)
    {
        return $this->novel->create($data);
    }

    public function update($id, $data)
    {
        $novel = $this->novel->find($id);
        return $novel->update($data);
    }

}
