<?php

namespace App\Services;

use App\DTO\FeedReportDTO;
use App\FeedReportRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use function App\Console\Commands\cleanWord;

class FeedReportService
{
    public function __construct(protected FeedReportRepositoryInterface $feedReportRepository)
    {
    }

    private $stopWordsEn = [
        "a", "about", "above", "after", "again", "against", "all", "am", "an", "and", "any", "are", "aren't", "as",
        "at", "be", "because", "been", "before", "being", "below", "between", "both", "but", "by", "can't", "cannot",
        "could", "couldn't", "did", "didn't", "do", "does", "doesn't", "doing", "don't", "down", "during", "each",
        "few", "for", "from", "further", "had", "hadn't", "has", "hasn't", "have", "haven't", "having", "he", "he'd",
        "he'll", "he's", "her", "here", "here's", "hers", "herself", "him", "himself", "his", "how", "how's", "i",
        "i'd", "i'll", "i'm", "i've", "if", "in", "into", "is", "isn't", "it", "it's", "its", "itself", "let's", "me",
        "more", "most", "mustn't", "my", "myself", "no", "nor", "not", "of", "off", "on", "once", "only", "or", "other",
        "ought", "our", "ours", "ourselves", "out", "over", "own", "same", "shan't", "she", "she'd", "she'll", "she's",
        "should", "shouldn't", "so", "some", "such", "than", "that", "that's", "the", "their", "theirs", "them", "themselves",
        "then", "there", "there's", "these", "they", "they'd", "they'll", "they're", "they've", "this", "those", "through",
        "to", "too", "under", "until", "up", "very", "was", "wasn't", "we", "we'd", "we'll", "we're", "we've", "were",
        "weren't", "what", "what's", "when", "when's", "where", "where's", "which", "while", "who", "who's", "whom",
        "why", "why's", "with", "won't", "would", "wouldn't", "you", "you'd", "you'll", "you're", "you've", "your",
        "yours", "yourself", "yourselves"
    ];

    private $stopWordsUk = [
        "і", "в", "на", "з", "по", "о", "до", "та", "як", "що", "це", "але", "для", "або", "про", "без", "й", "від",
        "із", "над", "під", "при", "між", "через", "перед", "після", "крізь", "таки", "якщо", "бо", "хоч", "коли",
        "поки", "аби", "так", "тільки", "серед", "у", "він", "вона", "вони", "воно", "його", "її", "їх", "ми", "ти",
        "я", "ви", "нас", "нам", "вам", "моя", "твоє", "наш", "ваш", "їхній", "тут", "там", "де", "хто", "що", "кого",
        "кому", "ким", "чий", "який", "чия", "що", "котрий", "цей", "такий", "інший", "весь", "кожен", "свій",
        "один", "одна", "одне", "одні", "сам", "сама", "самі", "саме", "також", "був", "була", "були", "було",
        "буде", "є", "чи", "ніхто", "нічого", "ніде", "ніколи", "нікого", "нікому", "ніякий", "ніколи", "щось",
        "десь", "іноді", "майже", "завжди", "часом", "тому", "навіщо", "дарма", "проте", "чи", "б", "аби", "годі",
        "лише", "немов", "немовби", "немовбито", "наче", "неначе", "неначе", "немовля", "аби", "майже", "куди",
        "тоді", "хоча", "замість", "щодо", "хоч", "дещо", "іще"
    ];


    public function findReport($startIndex,  $maxResults)
    {
        return $this->feedReportRepository->findReport($startIndex, $maxResults);
    }

    public function generateReport($startIndex, $maxResults): ?array
    {
        $lastPubDate = session('last_pub_date');
        $lastStartIndex = session('start_index');
        $lastMaxResults = session('max_results');
        $url = 'https://sweet.tv/lg/feed/GetContents';
        $response = Http::get($url, [
            'maxresult' => $maxResults,
            'startindex' => $startIndex,
        ]);


        $xml = simplexml_load_string($response->body());
        $pubDate = (string)$xml->xpath('//channel/pubdate')[0];

        if($lastPubDate && $lastPubDate == $pubDate){
          return [
              'report' => $this->feedReportRepository->findReport($lastStartIndex, $lastMaxResults),
              'message' => __('messages.wait_message')
          ];
        }
        session(['last_pub_date' => $pubDate, 'start_index' => $startIndex, 'max_results' => $maxResults]);
        session()->save();

        $movies = $xml->xpath('//channel/item');
        if (count($movies) === 0) {
            return null;
        }

        $report = new FeedReportDTO();
        $report->setStartIndex($startIndex);
        $report->setMaxResults($maxResults);
        $report->setMoviesCount(count($movies));
        $report->setTotalCountries($this->getTotalCountryMovies($movies));
        $report->setAverageActors($this->getAverageActors($movies));
        $viewingOptions = $this->getViewingOptionsCount($movies);
        $report->setSubscriptionMovies($viewingOptions['subscription_movies']);
        $report->setPurchaseMovies($viewingOptions['purchase_movies']);
        $report->setMoviesByGenre($this->getMoviesByGenre($movies));
        $report->setMoviesByCountry($this->getMoviesByCountry($movies));
        $report->setKeywordFrequency($this->getTopWords($movies));
        $report->setMoviesByYear($this->getMoviesByYear($movies));

        return [
            'report' => $this->feedReportRepository->saveReport($report),
            'message' => __('messages.report_generated')
            ];
    }

    public function getTotalCountryMovies(array $movies)
    {
        $countries = [];
        foreach ($movies as $movie) {
            if (isset($movie->countryavailability)) {
                $countries[] = (string)$movie->countryavailability->country;
            }
        }

        return count(array_unique($countries));
    }

    public function getAverageActors(array $movies)
    {
        $totalActors = 0;
        $movieCountWithActors = 0;
        foreach ($movies as $movie) {
            $actors = $movie->credits->xpath('credit[role="actor"]');
            if (count($actors) > 0) {
                $totalActors += count($actors);
                $movieCountWithActors++;
            }
        }
        return $movieCountWithActors > 0 ? $totalActors / $movieCountWithActors : 0;
    }

    public function getViewingOptionsCount(array $movies)
    {
        $subscriptionMovies = 0;
        $purchaseMovies = 0;
        foreach ($movies as $movie) {
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

        return [
            'subscription_movies' => $subscriptionMovies,
            'purchase_movies' => $purchaseMovies
        ];
    }

    public function getMoviesByGenre(array $movies)
    {
        $genres = [];
        foreach ($movies as $movie) {
            if (isset($movie->genres)) {
                foreach ($movie->genres->genre as $genre) {
                    $genreName = trim((string)$genre);
                    if (!isset($genres[$genreName])) {
                        $genres[$genreName] = 0;
                    }
                    $genres[$genreName]++;
                }
            }
        }
        arsort($genres);

        return $genres;
    }

    public function getMoviesByCountry(array $movies)
    {
        $moviesByCountry = [];
        foreach ($movies as $movie) {
            if (isset($movie->countryavailability)) {
                $country = (string)$movie->countryavailability->country;

                if (!isset($moviesByCountry[$country])) {
                    $moviesByCountry[$country] = 0;
                }
                $moviesByCountry[$country]++;
            }
        }
        return $moviesByCountry;
    }

    public function getTopWords(array $movies): array
    {
        $keywordFrequencyUk = [];
        $keywordFrequencyEn = [];

        foreach ($movies as $movie) {
            if (isset($movie->descriptions)) {

                foreach ($movie->descriptions->description as $description) {
                    // Проверяем, что описание имеет локаль "uk-UK"
                    if ((string)$description['locale'] == "uk-UK") {
                        // Разбиваем описание на слова
                        $words = preg_split('/\s+/', (string)$description);

                        foreach ($words as $word) {
                            // Проверяем, что слово имеет длину >= 5 символов и не является стоп-словом
                            if (strlen($word) >= 5 && !in_array($word, $this->stopWordsUk)) {
                                if (!isset($keywordFrequencyUk[$word])) {
                                    $keywordFrequencyUk[$word] = 0;
                                }
                                $keywordFrequencyUk[$word]++;
                            }
                        }
                    } else if((string)$description['locale'] == "en-US") {
                        // Разбиваем описание на слова
                        $words = preg_split('/\s+/', (string)$description);

                        foreach ($words as $word) {
                            // Проверяем, что слово имеет длину >= 5 символов и не является стоп-словом
                            if (strlen($word) >= 5 && !in_array($word, $this->stopWordsEn)) {
                                if (!isset($keywordFrequencyEn[$word])) {
                                    $keywordFrequencyEn[$word] = 0;
                                }
                                $keywordFrequencyEn[$word]++;
                            }
                        }
                    }
                }
            }

        }

        arsort($keywordFrequencyEn);
        arsort($keywordFrequencyUk);

        return [
            'en' => array_slice($keywordFrequencyEn, 0, 15, true),
            'uk' => array_slice($keywordFrequencyUk, 0, 15, true)
        ];
    }


    public function getMoviesByYear(array $movies): array
    {
        $moviesByYear = [];
        foreach ($movies as $movie) {
            if (isset($movie->videoinfo->makeyear)) {
                $year = (string)$movie->videoinfo->makeyear;

                if (!isset($moviesByYear[$year])) {
                    $moviesByYear[$year] = 0;
                }
                $moviesByYear[$year]++;
            }
        }
        krsort($moviesByYear);

        return $moviesByYear;
    }
}
