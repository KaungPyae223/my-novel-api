<?php

namespace App\Repositories;

use App\Http\Utils\WriteLog;
use App\Models\Chapter;
use App\Models\Letter;
use App\Models\Novel;
use App\Models\Post;

class NovelRepository
{

    protected $novel;

    public function __construct()
    {
        $this->novel = new Novel();
    }

    public function findNovel($id)
    {
        return $this->novel->find($id);
    }

    public function findNovelWithTrash($id)
    {
        return $this->novel->withTrashed()->find($id);
    }

    public function getMyNovels($user_id, $q)
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
        $novel = $this->findNovelWithTrash($id);

        if ($novel->trashed()) {
            if ($novel->hasMedia('cover_images')) {
                $novel->getFirstMedia('cover_images')->delete();
            }
            return $novel->forceDelete();
        } else {
            WriteLog::write($novel, 'trashed', $novel->getAttributes());
            return $novel->delete();
        }
    }

    public function getNovelLogs($id, $request)
    {

        $model = $request->input('tab', 'novel');
        $action = $request->input('action', 'all');
        $q = $request->input('q', '');

        $novel = $this->findNovelWithTrash($id);

        $logs = $novel->logs();

        if ($model == 'novel') {
            $logs->where('logable_type', Novel::class);
        } else if ($model == 'chapters') {
            $logs->where('logable_type', Chapter::class);
        } else {
            $logs->where('logable_type', Post::class);
        }

        if ($action !== 'all') {
            $logs->where('action', $action);
        }

        if ($q) {
            $logs->where(function ($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhere('ip_address', 'like', '%' . $q . '%')
                    ->orWhere('user_agent', 'like', '%' . $q . '%');
            });
        }

        $logs = $logs->orderBy('created_at', 'desc')->paginate(10);

        return $logs;
    }

    public function getNovelChapters($id,$request)
    {

        $novel = $this->findNovel($id);
        $q = $request->input('q');
        $filter = $request->input('filter');
        $sort = $request->input('sort', 'newest');

       
        $chapters = $novel->chapters();

        if ($q) {
            $chapters->where('title', 'like', '%' . $q . '%');
        }

        if ($filter && $filter != 'all') {
            $chapters->where('status', $filter);
        }

        match ($sort) {
            'oldest' => $chapters->orderBy('created_at'),
            'az'     => $chapters->orderBy('title'),
            'za'     => $chapters->orderByDesc('title'),
            default  => $chapters->orderByDesc('created_at'), 
        };

        $chapters = $chapters->paginate(15);

        return $chapters;
    }

    public function createNovelPost($id, $data)
    {
        $novel = $this->findNovel($id);
        $post = $novel->posts()->create($data);
        return $post;
    }

    public function getNovelPost($id)
    {
        $novel = $this->findNovelWithTrash($id);
        return $novel->posts()->orderBy('created_at', 'desc')->paginate(10);
    }

    public function getNovelReviews($id)
    {
        $novel = $this->findNovelWithTrash($id);
        return $novel->review()->orderBy('created_at', 'desc')->paginate(3);
    }

  

    public function addHistory($id, $user_id)
    {
        $novel = $this->findNovel($id);

        $alreadyExists = $novel->history()->where('user_id', $user_id)->exists();
       
        if ($alreadyExists) {
            return;
        }

        $novel->history()->create(['user_id' => $user_id]);
    }

  

    public function getTrashedChapters($id)
    {
        $novel = $this->findNovelWithTrash($id);
        return $novel->chapters()->onlyTrashed()->get();
    }

    public function createLetter($id, $data)
    {
        $novel = $this->findNovel($id);
        $letter = $novel->letter()->create($data);
        return $letter;
    }

    public function getLetters($id)
    {
        $novel = $this->findNovel($id);

        $letters = $novel->letter()->orderBy('created_at', 'desc')->paginate(10);

        $unreadIds = $letters->where('status', 'unread')->pluck('id');

        if ($unreadIds->isNotEmpty()) {
            Letter::whereIn('id', $unreadIds)->update(['status' => 'read']);
        }

        return $letters;
    }

    public function getUserLetter($id, $user_id)
    {
        $novel = $this->findNovel($id);
        return $novel->letter()->withTrashed()->where('user_id', $user_id)->orderBy('created_at', 'desc')->paginate(10);
    }

    
    public function getBannedUsers($id, $q)
    {

        $novel = $this->findNovel($id);
        $bannedUsers = $novel->ban();

        if ($q) {
            $bannedUsers = $bannedUsers->whereHas('user', function ($query) use ($q) {
                $query->where('full_name', 'like', '%' . $q . '%')->orWhere('email', 'like', '%' . $q . '%');
            });
        }

        $bannedUsers = $bannedUsers->orderBy('created_at', 'desc')->paginate(10);


        return $bannedUsers;
    }

    public function getTotalBannedUsers($id)
    {
        $novel = $this->findNovel($id);
        return $novel->ban()->count();
    }

}
