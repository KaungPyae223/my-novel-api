<?php

namespace App\Repositories;

use App\Models\Novel;
use App\Models\post;

class PostRepository
{

    protected $post;
    protected $novel;

    public function __construct() {
        $this->post = new Post();
        $this->novel = new Novel();
    }

    public function findPost($id)
    {
        return $this->post->find($id);
    }

    public function findNovel($id)
    {
        return $this->novel->find($id);
    }

    public function createNovelPost($id,$data)
    {
        $novel = $this->findNovel($id);
        $post = $novel->posts()->create($data);
        return $post;
    }



}
