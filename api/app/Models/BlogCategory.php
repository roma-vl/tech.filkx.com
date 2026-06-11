<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCategory extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'order',
    ];

    protected $casts = [
        'name' => 'array',
        'description' => 'array',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }
}
