<?php

namespace App\Http\Controllers;

use App\Http\Utils\ElasticSetUp;
use App\Repositories\QuerySuggestionRepository;
use Illuminate\Http\Request;

class QuerySuggestionController extends Controller
{

    protected $elastic;
    protected $suggestionRepository;

    public function __construct(QuerySuggestionRepository $suggestionRepository)
    {
        $this->elastic = (new ElasticSetUp())->setUp();
        $this->suggestionRepository = $suggestionRepository;
    }

    public function index()
    {
        
    }

    public function novelSuggestion(Request $request)
    {
        $q = $request->input('q','');

        if (strlen($q) < 2) {
            return response()->json([]);
        }


        if (!$this->elastic){
            return $this->suggestionRepository->suggestNovelFromDB($q);
        }else{
            return $this->suggestionRepository->suggestNovelFromElastic($q);
        }

       
    }
}
