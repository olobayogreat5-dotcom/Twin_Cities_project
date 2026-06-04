<?php
require_once 'config.php';

// DATABASE CONNECTION
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// POI DETAIL PAGE
if (isset($_GET['id']) && is_numeric($_GET['id'])) {

    $poi_id = (int) $_GET['id'];

    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS city_name 
        FROM place_of_interest p
        JOIN city c ON p.city_id = c.city_id
        WHERE p.place_id = ?
    ");
    $stmt->execute([$poi_id]);
    $poi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$poi) die("Place not found.");

    $photoStmt = $pdo->prepare("SELECT photo_url FROM photo WHERE poi_id = ?");
    $photoStmt->execute([$poi_id]);
    $photos = $photoStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($poi['name']); ?></title>
    <style>
        body { font-family: Arial; margin: 40px; }
        a { text-decoration:none; }
        .gallery img { width:300px; height:200px; margin:10px; object-fit:cover; }
        button { padding:8px 14px; cursor:pointer; }
    </style>
</head>
<body>

<a href="index3.php">← Back to Maps</a>
<a href="comments.php"><button type="button">Go to Comments</button></a>

<h1><?php echo htmlspecialchars($poi['name']); ?></h1>
<p><strong>City:</strong> <?php echo htmlspecialchars($poi['city_name']); ?></p>
<p><strong>Type:</strong> <?php echo htmlspecialchars($poi['type']); ?></p>
<p><strong>Address:</strong> <?php echo htmlspecialchars($poi['address']); ?></p>
<p><strong>Coordinates:</strong> <?php echo htmlspecialchars($poi['latitude']); ?>, <?php echo htmlspecialchars($poi['longitude']); ?></p>

<h2>Photo Gallery</h2>
<?php if (count($photos) > 0): ?>
    <div class="gallery">
        <?php foreach ($photos as $photo): ?>
            <img src="<?php echo htmlspecialchars($photo['photo_url']); ?>">
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No photos available.</p>
<?php endif; ?>

</body>
</html>
<?php
exit();
}

// MAP + WEATHER PAGE
$key = WEATHER_API_KEY;
$location = SWINDON_QUERY;
$twin = SALZGITTER_QUERY;

$xml_uk = @simplexml_load_file(WEATHER_API_BASE_URL . "?q={$location}&mode=xml&units=metric&appid={$key}");
$xml_twin = @simplexml_load_file(WEATHER_API_BASE_URL . "?q={$twin}&mode=xml&units=metric&appid={$key}");

$stmt1 = $pdo->prepare("SELECT * FROM place_of_interest WHERE city_id = 1");
$stmt1->execute();
$swindonPOIs = $stmt1->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $pdo->prepare("SELECT * FROM place_of_interest WHERE city_id = 2");
$stmt2->execute();
$salzgitterPOIs = $stmt2->fetchAll(PDO::FETCH_ASSOC);

function getData($xml){
    if (!$xml) {
        return [
            "name" => "Weather unavailable",
            "cloud" => "Unavailable",
            "temp" => "Unavailable",
            "windDirection" => "Unavailable",
            "windSpeed" => "Unavailable",
            "humidity" => "Unavailable",
            "pressure" => "Unavailable",
            "sunrise" => "",
            "sunset" => "",
            "icon" => ""
        ];
    }

    return [
        "name" => htmlspecialchars($xml->location->name),
        "cloud" => htmlspecialchars($xml->forecast->time[0]->clouds['value']),
        "temp" => htmlspecialchars($xml->forecast->time[0]->temperature['value']),
        "windDirection" => htmlspecialchars($xml->forecast->time[0]->windDirection["name"]),
        "windSpeed" => htmlspecialchars($xml->forecast->time[0]->windSpeed["mps"]),
        "humidity" => htmlspecialchars($xml->forecast->time[0]->humidity["value"]),
        "pressure" => htmlspecialchars($xml->forecast->time[0]->pressure["value"]),
        "sunrise" => htmlspecialchars($xml->sun["rise"]),
        "sunset" => htmlspecialchars($xml->sun["set"]),
        "icon" => (string)$xml->forecast->time[0]->symbol['var']
    ];
}

function getForecast($xml){
    $forecasts = [];
    if (!$xml) {
        return $forecasts;
    }

    foreach ($xml->forecast->time as $period) {
        $from = (string)$period['from'];
        $date = substr($from, 0, 10);
        $forecasts[$date][] = $period;
    }
    return $forecasts;
}

function displayForecast($forecast){
    if (empty($forecast)) {
        echo '<p>Forecast unavailable.</p>';
        return;
    }

    echo '<table>';
    echo '<tr><th>Date</th><th>Temp (&deg;C)</th><th>Condition</th><th>Icon</th></tr>';
    foreach ($forecast as $date => $periods) {
        foreach ($periods as $p) {
            if (strpos((string)$p['from'], '12:00:00') !== false) {
                echo "<tr>
                        <td>$date</td>
                        <td>{$p->temperature['value']}</td>
                        <td>{$p->symbol['name']}</td>
                        <td><img src='https://openweathermap.org/img/wn/{$p->symbol['var']}.png'></td>
                      </tr>";
            }
        }
    }
    echo '</table>';
}

$data = getData($xml_uk);
$twin_data = getData($xml_twin);
$forecast_uk = getForecast($xml_uk);
$forecast_twin = getForecast($xml_twin);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Twin Cities Maps & Weather</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <style>
        body { font-family: Arial; margin:20px; }
        .map { width:600px; height:400px; margin-bottom:20px; border:1px solid #000; }
        .weather { margin-bottom:40px; border:1px solid #000; padding:10px; }
        table { border-collapse: collapse; }
        td, th { border:1px solid #000; padding:5px; text-align:center; }
        button { padding:8px 14px; cursor:pointer; }
    </style>
</head>
<body>

<a href="comments.php"><button type="button">Go to Comments</button></a>

<h1>Twin Cities: Maps & Weather</h1>

<h2>Swindon Map</h2>
<div id="map1" class="map"></div>

<h2>Salzgitter Map</h2>
<div id="map2" class="map"></div>

<div class="weather">
<h2><?php echo $data['name']; ?> - Current Weather</h2>
<p>Temp: <?php echo $data['temp']; ?>°C</p>
<p>Condition: <?php echo $data['cloud']; ?></p>
<p>Wind: <?php echo $data['windSpeed']; ?> m/s from <?php echo $data['windDirection']; ?></p>
<p>Humidity: <?php echo $data['humidity']; ?>%</p>
<p>Pressure: <?php echo $data['pressure']; ?> hPa</p>
<p>Sunrise: <?php echo $data['sunrise'] !== '' ? substr($data['sunrise'],11) : 'Unavailable'; ?></p>
<p>Sunset: <?php echo $data['sunset'] !== '' ? substr($data['sunset'],11) : 'Unavailable'; ?></p>
<?php if ($data['icon'] !== ''): ?>
<img src="https://openweathermap.org/img/wn/<?php echo $data['icon']; ?>.png">
<?php endif; ?>
<h3>5-Day Forecast</h3>
<?php displayForecast($forecast_uk); ?>
</div>

<div class="weather">
<h2><?php echo $twin_data['name']; ?> - Current Weather</h2>
<p>Temp: <?php echo $twin_data['temp']; ?>°C</p>
<p>Condition: <?php echo $twin_data['cloud']; ?></p>
<p>Wind: <?php echo $twin_data['windSpeed']; ?> m/s from <?php echo $twin_data['windDirection']; ?></p>
<p>Humidity: <?php echo $twin_data['humidity']; ?>%</p>
<p>Pressure: <?php echo $twin_data['pressure']; ?> hPa</p>
<p>Sunrise: <?php echo $twin_data['sunrise'] !== '' ? substr($twin_data['sunrise'],11) : 'Unavailable'; ?></p>
<p>Sunset: <?php echo $twin_data['sunset'] !== '' ? substr($twin_data['sunset'],11) : 'Unavailable'; ?></p>
<?php if ($twin_data['icon'] !== ''): ?>
<img src="https://openweathermap.org/img/wn/<?php echo $twin_data['icon']; ?>.png">
<?php endif; ?>
<h3>5-Day Forecast</h3>
<?php displayForecast($forecast_twin); ?>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
var swindonPOIs = <?php echo json_encode($swindonPOIs); ?>;
var salzgitterPOIs = <?php echo json_encode($salzgitterPOIs); ?>;

var map1 = L.map('map1').setView([51.5558, -1.7797], 13);
L.tileLayer('<?php echo MAP_TILE_URL; ?>').addTo(map1);
swindonPOIs.forEach(function(poi) {
    L.marker([poi.latitude, poi.longitude]).addTo(map1)
     .bindPopup("<b>" + poi.name + "</b><br><a href='index3.php?id=" + poi.place_id + "'>View Details</a>");
});

var map2 = L.map('map2').setView([52.1500, 10.3333], 13);
L.tileLayer('<?php echo MAP_TILE_URL; ?>').addTo(map2);
salzgitterPOIs.forEach(function(poi) {
    L.marker([poi.latitude, poi.longitude]).addTo(map2)
     .bindPopup("<b>" + poi.name + "</b><br><a href='index3.php?id=" + poi.place_id + "'>View Details</a>");
});
</script>

</body>
</html>
