<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SweetTvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sweet-tv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $maxresult = 50;
        $startindex = 100;

        $response = Http::get('https://sweet.tv/lg/feed/GetContents', [
            'maxresult' => $maxresult,
            'startindex' => $startindex,
        ]);

        $xml = simplexml_load_string($response->body());

        $movies = $xml->xpath('//channel/item'); // Получаем все фильмы
        $totalMovies = count($movies);
//        dd($totalMovies);
        $countries = [];
        $totalActors = 0;
        $movieCountWithActors = 0;
        $subscriptionMovies = 0;
        $purchaseMovies = 0;
        $moviesByCountry = [];
        $genres = [];
        $keywordFrequency = [];
        $moviesByYear = [];
        $stopWords = ["і", "в", "на", "з", "по", "о", "до", "та", "як", "що", "це", "але", "для"]; // Стоп-слова для украинского языка

        function cleanWord($word)
        {
            // Убираем пунктуацию и спецсимволы
            $word = preg_replace('/[^\p{L}\p{N}]+/u', '', $word);
            return mb_strlen($word) >= 5 ? mb_strtolower($word) : null; // Оставляем только слова >= 5 символов
        }

        foreach ($movies as $movie) {
            if (isset($movie->credits)) {
                $actors = $movie->credits->xpath('credit[role="actor"]');
                $actorCount = count($actors);
                $totalActors += $actorCount;

                if ($actorCount > 0) {
                    $movieCountWithActors++; // Учитываем только фильмы с актерами
                }
            }

            if (isset($movie->countryavailability)) {
                // Извлекаем страну и добавляем в массив
                $countries[] = (string)$movie->countryavailability->country;
            }

            if (isset($movie->viewingoptions)) {
                $licenses = $movie->viewingoptions->xpath('viewingoption/license');
                $hasPurchase = false;
                $hasSubscription = false;

                foreach ($licenses as $license) {
                    if ((string)$license == 'SUBSCRIPTION') {
                        $hasSubscription = true;
                    }
                    if ((string)$license == 'PURCHASE') {
                        $hasPurchase = true;
                    }
                }

                if ($hasSubscription) {
                    $subscriptionMovies++;
                }
                if ($hasPurchase) {
                    $purchaseMovies++;
                }
            }

            if (isset($movie->countryavailability)) {
                $country = (string)$movie->countryavailability->country;

                if (!isset($moviesByCountry[$country])) {
                    $moviesByCountry[$country] = 0;
                }
                $moviesByCountry[$country]++;
            }

            if (isset($movie->genres)) {
                foreach ($movie->genres->genre as $genre) {
                    $genreName = trim((string)$genre);
                    if (!isset($genres[$genreName])) {
                        $genres[$genreName] = 0;
                    }
                    $genres[$genreName]++;
                }
            }

            if (isset($movie->descriptions)) {
                foreach ($movie->descriptions->description as $description) {
                    // Проверяем, что описание имеет локаль "uk-UK"
                    if ((string)$description['locale'] == "uk-UK") {
                        // Разбиваем описание на слова
                        $words = preg_split('/\s+/', (string)$description);

                        foreach ($words as $word) {
                            $word = cleanWord($word); // Функция для очистки слова от знаков пунктуации и лишних символов

                            // Проверяем, что слово имеет длину >= 5 символов и не является стоп-словом
                            if (strlen($word) >= 5 && !in_array($word, $stopWords)) {
                                if (!isset($keywordFrequency[$word])) {
                                    $keywordFrequency[$word] = 0;
                                }
                                $keywordFrequency[$word]++;
                            }
                        }
                    }
                }
            }

            if (isset($movie->videoinfo->makeyear)) {
                $year = (string)$movie->videoinfo->makeyear;

                if (!isset($moviesByYear[$year])) {
                    $moviesByYear[$year] = 0;
                }
                $moviesByYear[$year]++;
            }
        }
        $averageActors = $movieCountWithActors > 0 ? $totalActors / $movieCountWithActors : 0;
        arsort($keywordFrequency);

// Получаем 15 самых частых слов
        $topKeywords = array_slice($keywordFrequency, 0, 15, true);
//        dd($topKeywords);

// Подсчитываем уникальные страны
        $totalCountries = count(array_unique($countries));
        krsort($moviesByYear);
        dd($moviesByYear);
    }

    public function getViewingOptions($movie)
    {
        $subscriptionMovies = 0;
        $purchaseMovies = 0;

        $licenses = $movie->viewingoptions->xpath('viewingoption/license');
        $hasPurchase = false;
        $hasSubscription = false;

        foreach ($licenses as $license) {
            if ((string)$license == 'SUBSCRIPTION') {
                $hasSubscription = true;
            }
            if ((string)$license == 'PURCHASE') {
                $hasPurchase = true;
            }
        }

        if ($hasSubscription) {
            $subscriptionMovies++;
        }
        if ($hasPurchase) {
            $purchaseMovies++;
        }

        return [
            'subscription' => $subscriptionMovies,
            'purchase' => $purchaseMovies
        ];
    }
}
