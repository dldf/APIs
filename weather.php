<?php
declare(strict_types=1);

// Return JSON only (no HTML before this)
header('Content-Type: application/json; charset=utf-8');

// Optional for class demos from another origin:
header('Access-Control-Allow-Origin: *');

// load API key from file that's outside the web root. It's a php file that looks like:
// <?php return 'anapikeyfromopenweathermap'; //no closing php tag
// up two dirs from here: ../../secrets/owm-key.php;
$apiKey = require dirname(__DIR__, 2) . '/secrets/owm-key.php';
//echo "API key is $apiKey";


// Get inputs
$zip = isset($_GET['zip']) ? preg_replace('/\D/', '', $_GET['zip']) : '59718';
$country = $_GET['country'] ?? 'US';

$endpoint = 'https://api.openweathermap.org/data/2.5/weather';
$params = [
  'zip'   => "{$zip},{$country}",
  'units' => 'imperial',
  'appid' => $apiKey
];
$url = $endpoint . '?' . http_build_query($params);

// Fetch via cURL
$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 10,
  CURLOPT_FAILONERROR => false, // we’ll check HTTP code ourselves
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error    = curl_error($ch);
curl_close($ch);

// Handle upstream errors
if ($response === false || $httpCode >= 400) {
  http_response_code($httpCode ?: 502);
  echo json_encode([
    'error'  => $error ?: 'Upstream service error',
    'status' => $httpCode,
  ]);
  exit;
}

echo $response;