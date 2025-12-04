<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use App\Repositories\NovelRepository;
use Illuminate\Support\Facades\Auth;

class NovelServices
{

    protected $novelRepository;

    public function __construct(NovelRepository $novelRepository)
    {
        $this->novelRepository = $novelRepository;
    }

    public function checkUser(): int | null
    {
        $user_id = Auth::guard('sanctum')->check() ? Auth::guard('sanctum')->user()->id : null;

        return $user_id;
    }

    public function checkUserID($user_id)
    {
        return  User::find($user_id)->exists();
    }

    public function toggleFanLetter(Novel $novel): string
    {

        $openLetter = $novel->open_letter;

        $novel = $this->novelRepository->update($novel->id, [
            'open_letter' => $openLetter == 'open' ? 'close' : 'open',
        ]);

        return $novel->open_letter;
    }

    public function toggleLove(Novel $novel): string
    {

        $user_id = $this->checkUser();

        $alreadyExists = $novel->love()->where('user_id', $user_id)->exists();

        if ($alreadyExists) {
            $novel->love()->where('user_id', $user_id)->delete();
            return 'Novel unloved successfully';
        } else {
            $novel->love()->create([
                'user_id' => $user_id,
            ]);
            return 'Novel loved successfully';
        }
    }

    public function getUserLastReadChapter(Novel $novel)
    {
        if ($this->checkUser()) {
            $userId = $this->checkUser();

            $chapters = $novel->chapters()
                ->where('status', 'published')
                ->orderBy('created_at');

            $allChapters = $chapters->pluck('id')->toArray();
            $readChapters = $chapters->whereHas('histories', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->pluck('id')
                ->toArray();

            $firstUnreadChapter = null;
            $firstUnreadChapterIndex = null;
            foreach ($allChapters as $index => $chapterId) {
                if (!in_array($chapterId, $readChapters)) {
                    $firstUnreadChapter = $chapterId;
                    $firstUnreadChapterIndex = $index;
                    break;
                }
            }

            $page = $firstUnreadChapterIndex ? floor($firstUnreadChapterIndex / 15) + 1 : 1;

            return [
                'last_read_chapter' => $firstUnreadChapter,
                'last_read_page' => $page
            ];
        }

        return [
            'last_read_chapter' => null,
            'last_read_page' => 1
        ];
    }

    public function toggleFavorite(Novel $novel): string
    {

        $user_id = $this->checkUser();

        $alreadyExists = $novel->favorite()->where('user_id', $user_id)->exists();

        if ($alreadyExists) {
            $novel->favorite()->where('user_id', $user_id)->delete();
            return 'Novel unfavorited successfully';
        } else {
            $novel->favorite()->create([
                'user_id' => $user_id,
            ]);
            return 'Novel favorited successfully';
        }
    }

    public function banUser(Novel $novel, $user_id): void
    {

        $checkBan = $novel->ban()->where('user_id', $user_id)->first();

        if (!$checkBan) {
            $novel->ban()->create(['user_id' => $user_id]);
        }
    }

    public function unbanUser(Novel $novel, $user_id): void
    {

        $BanUser = $novel->ban()->where('user_id', $user_id)->first();

        if ($BanUser) {
            $BanUser->delete();
        }
    }


    public function addView(Novel $novel, $user_id): void
    {
        $last_view = $novel->view()->where('user_id', $user_id)->latest()->first();

        if (!$last_view || $last_view->created_at->diffInMinutes(now()) >= 5) {
            $novel->view()->create([
                'user_id' => $user_id,
            ]);
        }
    }

    public function addHistory(Novel $novel, $user_id): void
    {

        $alreadyExists = $novel->history()->where('user_id', $user_id)->exists();

        if ($alreadyExists) {
            return;
        }

        $novel->history()->create(['user_id' => $user_id]);
    }


    public function share(Novel $novel): void
    {

        $userId = Auth::guard('sanctum')->user()->id ?? request()->ip();


        $alreadyExists = $novel->share()->where('user_id', $userId)->exists();

        if ($alreadyExists) {
            return;
        }

        $novel->share()->create([
            'user_id' => $userId,
        ]);
    }

    public function getFanLetterStatus(Novel $novel): array
    {
        $user_id = $this->checkUser();

        $isBanned = $user_id ? $novel->ban()->where('user_id', $user_id)->exists() : false;

        $openLetter = $novel->open_letter == 'open' && $user_id && !$isBanned;

        $message = ($openLetter === 'close')
            ? 'The author has closed the letter writing feature for this novel.'
            : ((!$user_id)
                ? 'You must be logged in to write a letter.'
                : ($isBanned
                    ? 'You have been banned from writing a letter.'
                    : '')
            );

        return [
            'open_letter' => $openLetter,
            'message' => $message,
        ];
    }
}
