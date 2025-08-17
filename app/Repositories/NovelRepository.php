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

    public function getMyNovels($user_id,$q)
    {

        $query = $this->novel->where('user_id', $user_id);

        if ($q) {
            $query->where(
                function ($query) use ($q) {
                    $query->orWhere('title', 'like', '%' . $q . '%')
                        ->orWhere('description', 'like', '%' . $q . '%');
                }
            );
        }

        return $query->get();

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
        $novel->update($data);
        return $novel;
    }

    public function delete($id)
    {
        $novel = $this->novel->find($id);
        return $novel->delete();
    }

    public function createNovelPost($id,$data)
    {
        $novel = $this->findNovel($id);
        $post = $novel->posts()->create($data);
        return $post;
    }

}
