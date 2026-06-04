<?php 
define('APP_ENV', 'local');

// DATABASE
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'twin_cities');
define('DB_USER', 'root');
define('DB_PASS', '');

// WEATHER
define('WEATHER_API_BASE_URL', 'https://api.openweathermap.org/data/2.5/forecast');
define('WEATHER_API_KEY', 'f86a01fb9b1d3eee9647afd5a33815f7');

// MAP
define('MAP_TILE_URL', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');

// CITY QUERIES
define('SWINDON_QUERY', 'Swindon,GB');
define('SALZGITTER_QUERY', 'Salzgitter,DE');

// PEXELS API
define('PEXELS_API_KEY', '7XmFBw2lrXJuwsMV8gJffXeVtYoGGUhLG1OJYk3qzeTJzClkYvBUTdbZ');
define('PEXELS_ENDPOINT', 'https://api.pexels.com/v1/search');

// CACHE
define('CACHE_DIR', __DIR__ . '/cache/');
define('CACHE_DURATION', 3600);

// ERRORS
if (APP_ENV === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
?>
