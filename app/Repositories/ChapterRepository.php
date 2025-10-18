<?php

namespace App\Repositories;

use App\Http\Utils\WriteLog;
use App\Models\Chapter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChapterRepository
{
    protected $chapter;

    public function __construct(Chapter $chapter) {
        $this->chapter = $chapter;
    }



    public function findChapter($id) {
        return $this->chapter->find($id);
    }

    public function findChapterWithTrash($id) {
        return $this->chapter->withTrashed()->find($id);
    }

    public function createChapter($data) {
        return $this->chapter->create($data);
    }

    public function addHistory($id, $user_id)
    {
        $chapter = $this->findChapter($id);

        $alreadyExists = $chapter->history()->where('user_id', $user_id)->exists();
       
        if ($alreadyExists) {
            return;
        }

        $chapter->history()->create(['user_id' => $user_id]);
    }



    public function addView($id,$user_id) {

        $chapter = $this->findChapter($id);

        $last_view = $chapter->view()->where('user_id', $user_id)->latest()->first() ;

        if(!$last_view || $last_view->created_at->diffInMinutes(now()) >= 5){
            $chapter->view()->create([
                'user_id' => $user_id,
            ]);
        }
    }
    public function updateChapter($id, $data) {

        $chapter = $this->findChapter($id);

        $chapter->update($data);

        return $chapter;
    }

    public function deleteChapter($id) {
        $chapter = $this->chapter->withTrashed()->find($id);

        if ($chapter->trashed()) {
            return $chapter->forceDelete();
        }else{
            WriteLog::write($chapter, 'trashed', $chapter->getAttributes());
            return $chapter->delete();
        }
    }

    public function restoreChapter($id) {
        $chapter = $this->chapter->withTrashed()->find($id);

        if ($chapter->trashed()) {
            return $chapter->restore();
        }
    }

    public function share($id)
    {
        $chapter = $this->findChapter($id);
        $chapter->share_count++;
        $chapter->save();
    }
}
