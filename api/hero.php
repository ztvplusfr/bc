<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$moviesDir = __DIR__ . '/../data/movies/';
$seriesDir = __DIR__ . '/../data/series/';

$heroSlides = [];

// Helper to build slide data from either movie or series
function buildHeroSlide($item, $type) {
    if (!$item) {
        return null;
    }

    $genres = [];
    if (isset($item['genre']) && is_array($item['genre'])) {
        $genres = $item['genre'];
    } elseif (isset($item['genres']) && is_array($item['genres'])) {
        $genres = $item['genres'];
    }

    return [
        'id' => $item['id'],
        'type' => $type,
        'title' => $item['title'],
        'original_title' => $item['original_title'] ?? $item['title'],
        'description' => isset($item['description']) ? substr($item['description'], 0, 200) . '...' : '',
        'backdrop' => $item['backdrop'] ?? ($item['poster'] ?? ''),
        'poster' => $item['poster'] ?? '',
        'rating' => isset($item['rating']) ? floatval($item['rating']) : 0,
        'year' => $item['year'] ?? '',
        'release_date' => $item['release_date'] ?? null,
        'duration' => isset($item['duration']) ? intval($item['duration']) : 0,
        'genres' => $genres,
        'genre_string' => !empty($genres) ? implode(' / ', array_slice($genres, 0, 2)) : '',
        'age_rating' => $item['age_rating'] ?? 'N/A',
        'language' => $item['language'] ?? 'English',
        'studio' => $item['studio'] ?? '',
        'director' => $item['director'] ?? ''
    ];
}

// Charger quelques films
$movieFiles = glob($moviesDir . '*.json');
shuffle($movieFiles);
$movieFiles = array_slice($movieFiles, 0, 3);

foreach ($movieFiles as $file) {
    $json = file_get_contents($file);
    $movie = json_decode($json, true);
    $slide = buildHeroSlide($movie, 'movie');
    if ($slide) {
        $heroSlides[] = $slide;
    }
}

// Charger quelques séries
$seriesFiles = glob($seriesDir . '*.json');
shuffle($seriesFiles);
$seriesFiles = array_slice($seriesFiles, 0, 2);

foreach ($seriesFiles as $file) {
    $json = file_get_contents($file);
    $series = json_decode($json, true);
    $slide = buildHeroSlide($series, 'series');
    if ($slide) {
        $heroSlides[] = $slide;
    }
}

// Mélanger le tout pour un mix films / séries
shuffle($heroSlides);

echo json_encode($heroSlides);
?>
