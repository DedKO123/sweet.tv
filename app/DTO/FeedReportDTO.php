<?php

namespace App\DTO;

class FeedReportDTO
{
  public int $start_index;
    public int $max_results;
    public int $movies_count;
    public int $average_actors;
    public int $total_countries;
    public int $subscription_movies;
    public int $purchase_movies;
    public array $movies_by_country;
    public array $movies_by_genre;
    public array $keyword_frequency;
    public array $movies_by_year;

    public function getStartIndex(): int
    {
        return $this->start_index;
    }

    public function setStartIndex(int $start_index): void
    {
        $this->start_index = $start_index;
    }

    public function getMaxResults(): int
    {
        return $this->max_results;
    }

    public function setMaxResults(int $max_results): void
    {
        $this->max_results = $max_results;
    }

    public function getMoviesCount(): int
    {
        return $this->movies_count;
    }

    public function setMoviesCount(int $movies_count): void
    {
        $this->movies_count = $movies_count;
    }

    public function getAverageActors(): int
    {
        return $this->average_actors;
    }

    public function setAverageActors(int $average_actors): void
    {
        $this->average_actors = $average_actors;
    }

    public function getTotalCountries(): int
    {
        return $this->total_countries;
    }

    public function setTotalCountries(int $total_countries): void
    {
        $this->total_countries = $total_countries;
    }

    public function getSubscriptionMovies(): int
    {
        return $this->subscription_movies;
    }

    public function setSubscriptionMovies(int $subscription_movies): void
    {
        $this->subscription_movies = $subscription_movies;
    }

    public function getPurchaseMovies(): int
    {
        return $this->purchase_movies;
    }

    public function setPurchaseMovies(int $purchase_movies): void
    {
        $this->purchase_movies = $purchase_movies;
    }

    public function getMoviesByCountry(): array
    {
        return $this->movies_by_country;
    }

    public function setMoviesByCountry(array $movies_by_country): void
    {
        $this->movies_by_country = $movies_by_country;
    }

    public function getMoviesByGenre(): array
    {
        return $this->movies_by_genre;
    }

    public function setMoviesByGenre(array $movies_by_genre): void
    {
        $this->movies_by_genre = $movies_by_genre;
    }

    public function getKeywordFrequency(): array
    {
        return $this->keyword_frequency;
    }

    public function setKeywordFrequency(array $keyword_frequency): void
    {
        $this->keyword_frequency = $keyword_frequency;
    }

    public function getMoviesByYear(): array
    {
        return $this->movies_by_year;
    }

    public function setMoviesByYear(array $movies_by_year): void
    {
        $this->movies_by_year = $movies_by_year;
    }

    public function toArray(): array
    {
        return [
            'start_index' => $this->getStartIndex(),
            'max_results' => $this->getMaxResults(),
            'movies_count' => $this->getMoviesCount(),
            'average_actors' => $this->getAverageActors(),
            'total_countries' => $this->getTotalCountries(),
            'subscription_movies' => $this->getSubscriptionMovies(),
            'purchase_movies' => $this->getPurchaseMovies(),
            'movies_by_country' => $this->getMoviesByCountry(),
            'movies_by_genre' => $this->getMoviesByGenre(),
            'keyword_frequency' => $this->getKeywordFrequency(),
            'movies_by_year' => $this->getMoviesByYear(),
        ];
    }
}
