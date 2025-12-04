<?php

namespace App\Http\Controllers;

use App\Services\QuerySuggestionServices;
use Illuminate\Http\Request;

class QuerySuggestionController extends Controller
{

    protected $elastic;
    protected $suggestionServices;

    public function __construct(QuerySuggestionServices $suggestionServices)
    {
        $this->elastic = app('elasticsearch');
        $this->suggestionServices = $suggestionServices;
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
            return $this->suggestionServices->suggestNovelFromDB($q);
        }else{
            return $this->suggestionServices->suggestNovelFromElastic($q);
        }

       
    }
}
