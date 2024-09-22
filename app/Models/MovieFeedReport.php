<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieFeedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_index',
        'max_results',
        'movies_count',
        'average_actors',
        'total_countries',
        'subscription_movies',
        'purchase_movies',
        'movies_by_country',
        'movies_by_genre',
        'keyword_frequency',
        'movies_by_year',
    ];

    protected $casts = [
        'movies_by_country' => 'array',
        'movies_by_genre' => 'array',
        'keyword_frequency' => 'array',
        'movies_by_year' => 'array',
    ];
}
