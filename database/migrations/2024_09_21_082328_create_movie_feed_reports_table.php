<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('movie_feed_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('start_index');
            $table->integer('max_results');
            $table->integer('movies_count');
            $table->integer('average_actors');
            $table->integer('total_countries');
            $table->integer('subscription_movies');
            $table->integer('purchase_movies');
            $table->json('movies_by_country');
            $table->json('movies_by_genre');
            $table->json('keyword_frequency');
            $table->json('movies_by_year');

            $table->index('start_index');
            $table->index('max_results');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movie_feed_reports');
    }
};
