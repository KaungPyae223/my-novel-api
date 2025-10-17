<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorereviewRequest;
use App\Http\Requests\UpdatereviewRequest;
use App\Models\review;
use App\Repositories\ReviewRepository;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $reviewRepository;

    public function __construct(ReviewRepository $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorereviewRequest $request)
    {

        $user_id = Auth::user()->id;

        $checkReview = $this->reviewRepository->checkReview($request->novel_id,$user_id);

        if($checkReview){
            return response()->json([
                'message' => 'You can only review once every month',
            ], 400);
        }

        $request->merge([
            'user_id' => $user_id,
        ]);

        $this->reviewRepository->create($request->all());

        return response()->json([
            'message' => 'Review submitted successfully',
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatereviewRequest $request, review $review)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(review $review)
    {
        //
    }
}
