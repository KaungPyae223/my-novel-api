<?php

namespace App\Repositories;

use App\Jobs\DeleteImage;
use App\Models\Chapter;
use App\Models\Log;
use App\Models\Novel;
use App\Models\Post;
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
        $novel = $this->novel->withTrashed()->find($id);

        if ($novel->trashed()) {
            if ($novel->image_public_id) {
                dispatch(new DeleteImage($novel->image_public_id));
            }
            return $novel->forceDelete();
        }else{
            return $novel->delete();
        }
    }

    public function getNovelLogs($id,$request)
    {

        $model = $request->input('tab', 'novel');
        $action = $request->input('action','all');
        $q = $request->input('q', '');

        $novel = $this->novel->withTrashed()->find($id);
        
        $logs = Log::query();
        
        if($model == 'novel'){
            $novelID = $novel->id;
            $logs->where(function ($query) use ($novelID) {
                $query->where('logable_type', Novel::class)
                    ->where('logable_id', $novelID);
            });
        } else if($model == 'chapters') {
            $chapterID = $novel->chapters()->pluck('id');
            $logs->where(function ($query) use ($chapterID) {
                $query->where('logable_type', Chapter::class)
                    ->whereIn('logable_id', $chapterID);
            });
        } else {
            $postID = $novel->posts()->pluck('id');
            $logs->where(function ($query) use ($postID) {
                $query->where('logable_type', Post::class)
                    ->whereIn('logable_id', $postID);
            });
        }

        if($action !== 'all'){
            $logs->where('action', $action);
        }

        if($q){
            $logs->where(function ($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhere('ip_address', 'like', '%' . $q . '%')
                    ->orWhere('user_agent', 'like', '%' . $q . '%');
            });
        }

        
        
        $logs = $logs->with('user')->paginate(10);

        return $logs;
        
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

        $last_view = $novel->view()->where('user_id', $user_id)->latest()->first() ;

        if(!$last_view || $last_view->created_at->diffInMinutes(now()) >= 5){
            $novel->view()->create([
                'user_id' => $user_id,
            ]);
        }
    }

    public function addHistory($id, $user_id)
    {
        $novel = $this->findNovel($id);

        $user = Auth::user();
        if (!$user || !$user->save_history) {
            return;
        }

        $alreadyExists = $novel->history()->where('user_id', $user_id)->exists();
        if ($alreadyExists) {
            return;
        }

        $novel->history()->create(['user_id' => $user_id]);
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
