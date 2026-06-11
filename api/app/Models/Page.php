<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'static_pages';

    protected $fillable = [
        'slug',
        'title',
        'content',
        'status',
    ];

    protected $casts = [
        'title' => 'array',
        'content' => 'array',
    ];
}
