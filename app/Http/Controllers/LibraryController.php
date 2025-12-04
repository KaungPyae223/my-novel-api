<?php

namespace App\Http\Controllers;

use App\Services\LibraryServices;
use Illuminate\Http\Request;

class LibraryController extends Controller
{

    protected $elastic;
    protected $libraryServices;

    public function __construct(LibraryServices $libraryServices)
    {

        $this->libraryServices = $libraryServices;
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
            return $this->libraryServices->searchNovelFromDB($q, $genre, $progress, $limit);
        } else {
            return $this->libraryServices->searchNovelFromElastic($q, $genre, $progress, $limit, $page);
        }
    }

}
