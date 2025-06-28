<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [[
            'genre' => 'Action',
        ],[
            'genre' => 'Adventure',
        ],[
            'genre' => 'Alternate History',
        ],[
            'genre' => 'Biopunk',
        ],[
            'genre' => 'Chick Lit',
        ],[
            'genre' => "Children's Fiction",
        ],[
            'genre' => 'Classic',
        ],[
            'genre' => 'Comedy',
        ],[
            'genre' => 'Coming-of-Age',
        ],[
            'genre' => 'Contemporary Fiction',
        ],[
            'genre' => 'Cozy Mystery',
        ],[
            'genre' => 'Crime',
        ],[
            'genre' => 'Cyberpunk',
        ],[
            'genre' => 'Dark Fantasy',
        ],[
            'genre' => 'Detective',
        ],[
            'genre' => 'Dystopian',
        ],[
            'genre' => 'Erotic Romance',
        ],[
            'genre' => 'Espionage',
        ],[
            'genre' => 'Fantasy',
        ],[
            'genre' => 'General Fiction',
        ],[
            'genre' => 'Gothic',
        ],[
            'genre' => 'Grimdark Fantasy',
        ],[
            'genre' => 'Hard Sci-Fi',
        ],[
            'genre' => 'Historical Fiction',
        ],[
            'genre' => 'Historical Fantasy',
        ],[
            'genre' => 'Historical Romance',
        ],[
            'genre' => 'Horror',
        ],[
            'genre' => 'Humor',
        ],[
            'genre' => 'LGBTQ+ Fiction',
        ],[
            'genre' => 'Literary Fiction',
        ],[
            'genre' => 'Magical Realism',
        ],[
            'genre' => 'Medical Thriller',
        ],[
            'genre' => 'Military Sci-Fi',
        ],[
            'genre' => 'Mystery',
        ],[
            'genre' => 'Mythic Fantasy',
        ],[
            'genre' => 'New Adult',
        ],[
            'genre' => 'Noir',
        ],[
            'genre' => 'Paranormal',
        ],[
            'genre' => 'Paranormal Romance',
        ],[
            'genre' => 'Philosophical Fiction',
        ],[
            'genre' => 'Poetry',
        ],[
            'genre' => 'Political Thriller',
        ],[
            'genre' => 'Portal Fantasy',
        ],[
            'genre' => 'Post-Apocalyptic',
        ],[
            'genre' => 'Psychological Thriller',
        ],[
            'genre' => 'Realistic Fiction',
        ],[
            'genre' => 'Romance',
        ],[
            'genre' => 'Romantic Comedy',
        ],[
            'genre' => 'Satire',
        ],[
            'genre' => 'Science Fiction',
        ],[
            'genre' => 'Short Stories',
        ],[
            'genre' => 'Slice of Life',
        ],[
            'genre' => 'Soft Sci-Fi',
        ],[
            'genre' => 'Space Opera',
        ],[
            'genre' => 'Steampunk',
        ],[
            'genre' => 'Supernatural',
        ],[
            'genre' => 'Suspense',
        ],[
            'genre' => 'Techno-Thriller',
        ],[
            'genre' => 'Thriller',
        ],[
            'genre' => 'Time Travel',
        ],[
            'genre' => 'Tragedy',
        ],[
            'genre' => 'Urban Fantasy',
        ],[
            'genre' => 'Upmarket Fiction',
        ],[
            'genre' => 'Western',
        ],[
            'genre' => 'Young Adult (YA)',
        ],[
            'genre' => 'Young Adult Fantasy',
        ],[
            'genre' => 'Young Adult Romance',
        ]];

        Genre::insert($genres);
    }
}
