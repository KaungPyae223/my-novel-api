<?php

namespace App\Repositories;

use App\Models\Letter;

class LetterRepository
{
    public function findLetter($id)
    {
        return Letter::withTrashed()->find($id);
    }

    public function replyLetter($id, $data)
    {
        $letter = $this->findLetter($id);
        $letter->update($data);
        return $letter;
    }

    public function deleteAuthorLetter($id)
    {
        $letter = $this->findLetter($id);
        $letter->delete();
    }

    public function deleteReaderLetter($id)
    {
        $letter = $this->findLetter($id);
        $letter->forceDelete();
    }
}
