<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\LibraryRepository;

class LibraryController extends Controller
{

    protected $elastic;
    protected $libraryRepository;

    public function __construct(LibraryRepository $libraryRepository)
    {

        $this->libraryRepository = $libraryRepository;
        $this->elastic = app('elasticsearch');
    }

    public function Novels(Request $request)
    {

        $q = $request->query('q');
        $genre = $request->query('genre');
        $progress = $request->query('progress');
        $limit = $request->query('limit', 8);
        $page = $request->query('page', 1);

        if (!$this->elastic) {
            return $this->libraryRepository->searchNovelFromDB($q, $genre, $progress, $limit);
        } else {
            return $this->libraryRepository->searchNovelFromElastic($q, $genre, $progress, $limit, $page);
        }
    }

}
