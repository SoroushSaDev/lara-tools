<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    protected $fillable = [
        'name', 'path', 'title', 'artist', 'album', 'duration', 'cover_image',
    ];
}
