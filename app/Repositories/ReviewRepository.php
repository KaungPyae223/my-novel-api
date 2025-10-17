<?php

namespace App\Repositories;

use App\Models\Review;

class ReviewRepository
{

    protected $review;

    public function __construct(Review $review)
    {
        $this->review = $review;
    }

    public function getReviews()
    {
        
    }

    public function create($data)
    {
        return $this->review->create($data);
    }

    public function  checkReview($novel_id,$user_id)
    {
        $review = $this->review->where('novel_id', $novel_id)->where('user_id', $user_id)->orderBy('created_at', 'desc')->first();

        if( $review && $review->created_at > now()->subMonth(1) ){
            return true;
        }

        return false;

    }
}

