<?php

namespace App\Repositories;

use App\Models\Chapter;
use Illuminate\Support\Facades\Auth;

class ChapterRepository
{
    protected $chapter;

    public function __construct(Chapter $chapter) {
        $this->chapter = $chapter;
    }



    public function findChapter($id) {
        return $this->chapter->find($id);
    }

    public function createChapter($data) {
        return $this->chapter->create($data);
    }

    public function addView($id,$user_id) {
        $chapter = $this->findChapter($id);
        $chapter->view()->create([
            'user_id' => $user_id,
        ]);
    }

    public function updateChapter($id, $data) {

        $chapter = $this->findChapter($id);

        $chapter->update($data);

        return $chapter;
    }

    public function deleteChapter($id) {
        $chapter = $this->chapter->find($id);
        return $chapter->delete();
    }

    public function share($id)
    {
        $chapter = $this->findChapter($id);
        $chapter->share_count++;
        $chapter->save();
    }
}
