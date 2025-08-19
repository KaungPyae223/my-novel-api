<?php

namespace App\Repositories;

use App\Models\Post;

class PostRepository
{

    protected $post;

    public function __construct() {
        $this->post = new Post();
    }

    public function findPost($id)
    {
        return $this->post->find($id);
    }

    public function deletePost ($id)
    {
        $post = $this->findPost($id);
        $post->delete();
        return $post;
    }

    public function update($id, $data)
    {
        $post = $this->post->find($id);
        $post->update($data);
        return $post;
    }

}
