<?php

namespace App\Jobs;

use App\Http\Utils\ImageUtils;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteImage implements ShouldQueue
{
    use Queueable;

    protected $publicId;

    /**
     * Create a new job instance.
     */
    public function __construct($publicId)
    {
        $this->publicId = $publicId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ImageUtils::deleteImage($this->publicId);
    }
}
