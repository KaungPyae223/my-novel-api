<?php

namespace App\Http\Utils;

use Illuminate\Support\Str;


class GenerateUniqueName
{
    public static function generate($name)
    {
        $words = preg_split('/[\s\-_,]+/', $name    );
        $limitedWords = array_slice($words, 0, 20);
        $limitedName = implode(' ', $limitedWords);
        $uniqueName = Str::slug($limitedName);
        return '@'.$uniqueName;
    }
}
