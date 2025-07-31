<?php

namespace App\Repositories;

use App\Models\Chapter;

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

    public function updateChapter($id, $data) {

        $chapter = $this->chapter->find($id);

        return $chapter->update($data);
    }

    public function deleteChapter($id) {
        $chapter = $this->chapter->find($id);
        return $chapter->delete();
    }
}
