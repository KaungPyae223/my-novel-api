<?php

namespace App\Repositories;

use App\Models\Novel;
use Illuminate\Support\Facades\Auth;

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

    public function getNovelPost($id)
    {
        $novel = $this->findNovel($id);
        return $novel->posts()->orderBy('created_at', 'desc')->get();
    }

    public function addView($id,$user_id)
    {
        $novel = $this->findNovel($id);
        $novel->view()->create([
            'user_id' => $user_id,
        ]);
    }

    public function addHistory($id,$user_id)
    {
        $novel = $this->findNovel($id);

        $findHistory = $novel->history()->where('user_id', $user_id)->exists();

        if ($findHistory) {
            return;
        }

        $novel->history()->create([
            'user_id' => $user_id,
        ]);
    }

    public function addLove($id)
    {
        $novel = $this->findNovel($id);
        $novel->love()->create([
            'user_id' => Auth::user()->id,
        ]);
    }

    public function removeLove($id)
    {
        $novel = $this->findNovel($id);
        $novel->love()->where('user_id', Auth::user()->id)->delete();
    }

    public function addFavorite($id)
    {
        $novel = $this->findNovel($id);
        $novel->favorite()->create([
            'user_id' => Auth::user()->id,
        ]);
    }

    public function removeFavorite($id)
    {
        $novel = $this->findNovel($id);
        $novel->favorite()->where('user_id', Auth::user()->id)->delete();
    }

    public function share($id)
    {
        $novel = $this->findNovel($id);
        $novel->share_count++;
        $novel->save();
    }

}
