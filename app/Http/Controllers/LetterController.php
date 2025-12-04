<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReplyLetterRequest;
use App\Repositories\LetterRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LetterController extends Controller
{

    use AuthorizesRequests;

    protected $letterRepository;

    public function __construct(LetterRepository $letterRepository)
    {
        $this->letterRepository = $letterRepository;
    }

    public function notFound()
    {
        return response()->json([
            'message' => 'Letter not found',
        ], 404);
    }

    public function replyLetter($id, ReplyLetterRequest $request)
    {
        $letter = $this->letterRepository->findLetter($id);

        if (!$letter) {
            return $this->notFound();
        }

        $this->authorize('replyLetter', $letter);

        $letter = $this->letterRepository->replyLetter($id, $request->all());

        return response()->json([
            'message' => 'Letter replied successfully',
            'letter' => $letter,
        ]);
    }

    public function deleteAuthorLetter($id)
    {
        $letter = $this->letterRepository->findLetter($id);

        if (!$letter) {
            return $this->notFound();
        }

        $this->authorize('replyLetter', $letter);

        $letter = $this->letterRepository->deleteAuthorLetter($id);

        return response()->json([
            'message' => 'Letter deleted successfully',

        ]);
    }

    public function deleteReaderLetter($id)
    {
        $letter = $this->letterRepository->findLetter($id);

        if (!$letter) {
           return $this->notFound();
        }

        $this->authorize('deleteLetter', $letter);

        $letter = $this->letterRepository->deleteReaderLetter($id);

        return response()->json([
            'message' => 'Letter deleted successfully',

        ]);
    }
}
