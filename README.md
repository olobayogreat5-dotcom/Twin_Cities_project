# 🏙️ Twin Cities — Swindon & Salzgitter City Comparison App

A university group project web application that allows users to compare two twin cities — Swindon (UK) and Salzgitter (Germany) — across weather, maps, imagery, and community comments.

---

## 📋 Overview

Twin Cities is a PHP-powered web application that brings two cities together in one place. Users can explore and compare live weather forecasts, interactive maps, and city photos, as well as leave comments for others to read and engage with. Built with a RESTful API-driven architecture and a caching layer for optimised performance.

---

## ✨ Features

- 🌤️ **Live weather forecasts** for both cities via OpenWeatherMap API
- 🗺️ **Interactive maps** using OpenStreetMap tiles (Leaflet)
- 📸 **Dynamic city imagery** powered by the Pexels API
- 💬 **Community comment system** — users can leave and read comments
- ⚡ **API response caching** — reduces repeated requests and improves load times
- 🔄 Side-by-side city comparison layout

---

## 🛠️ Built With

- **PHP** — backend logic and API integration
- **MySQL** — database for storing user comments
- **OpenWeatherMap API** — live 5-day weather forecasts
- **OpenStreetMap + Leaflet** — interactive map tiles
- **Pexels API** — dynamic city photography
- **HTML & CSS** — frontend structure and styling

---

## ⚙️ Configuration

All app settings are managed in `config.php`:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'twin_cities');
define('WEATHER_API_BASE_URL', 'https://api.openweathermap.org/data/2.5/forecast');
define('MAP_TILE_URL', 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
define('SWINDON_QUERY', 'Swindon,GB');
define('SALZGITTER_QUERY', 'Salzgitter,DE');
define('CACHE_DIR', __DIR__ . '/cache/');
define('CACHE_DURATION', 3600); // 1 hour cache
```

> ⚠️ Never commit your real API keys to GitHub. Use environment variables or a `.env` file in production.

---

## 🚀 How to Run

**Requirements:**
- PHP 7.4 or later
- MySQL database
- A local server (e.g. XAMPP, WAMP, or Laravel Herd)
- OpenWeatherMap API key — [get one free here](https://openweathermap.org/api)
- Pexels API key — [get one free here](https://www.pexels.com/api/)

**Steps:**
1. Clone this repository into your local server's web root (e.g. `htdocs/`)
2. Create a MySQL database named `twin_cities`
3. Import the provided SQL schema
4. Open `config.php` and add your API keys:
   ```php
   define('WEATHER_API_KEY', 'your_openweathermap_key');
   define('PEXELS_API_KEY', 'your_pexels_key');
   ```
5. Visit `http://localhost/twin-cities/` in your browser

---

## 📁 Project Structure

```
twin-cities/
├── config.php                      # App configuration and API keys
├── index3.php                      # Main application entry point
├── comments.php                    # Comment display logic
├── comments_simple_with_button.php # Comment submission handler
└── cache/                          # API response cache directory
```

---

## 🌐 Cities Featured

| City       | Country | Query Used     |
|------------|---------|----------------|
| Swindon    | 🇬🇧 UK  | `Swindon,GB`   |
| Salzgitter | 🇩🇪 Germany | `Salzgitter,DE` |

---

## 👥 Team

Built collaboratively as a university group project at UWE Bristol.

**Adielgreat Olobayo** — Co-Developer  
BSc Software Engineering for Business — UWE Bristol  
[LinkedIn](https://www.linkedin.com/in/adielgreat-olobayo-913219359) | [GitHub](https://github.com/olobayogreat5-dotcom)
