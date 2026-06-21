<?php
// dashboard.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];

// API Integration - Fetching weather info (Hyderabad, India - Latitude: 17.3850, Longitude: 78.4867)
$weather_info = null;
$weather_error = '';

try {
    $api_url = "https://api.open-meteo.com/v1/forecast?latitude=17.3850&longitude=78.4867&current_weather=true";
    
    // Set connection timeout to 3 seconds to avoid blocking page load
    $opts = [
        'http' => [
            'method' => 'GET',
            'timeout' => 3
        ]
    ];
    $context = stream_context_create($opts);
    $response = @file_get_contents($api_url, false, $context);
    
    if ($response === false) {
        throw new Exception("Unable to fetch weather data from API.");
    }
    
    $data = json_decode($response, true);
    if (isset($data['current_weather'])) {
        $weather_info = $data['current_weather'];
    } else {
        throw new Exception("Invalid response structure from Weather API.");
    }
} catch (Exception $e) {
    $weather_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Advanced PHP CRUD</title>
    <title>Dashboard - Advanced PHP CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Dashboard</h1>
        <p>Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong>! (Role: <span class="badge" style="background: #e2e8f0; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: bold;"><?php echo htmlspecialchars($user_role); ?></span>)</p>
        
        <h2>Navigation</h2>
        <ul class="nav-menu">
            <li><a href="profile.php">My Profile</a></li>
            <?php if ($user_role === 'admin'): ?>
                <li><a href="manage_users.php">Manage Users</a></li>
            <?php endif; ?>
            <li><a href="logout.php" style="color: var(--error-color)">Logout</a></li>
        </ul>
        
        <h2>Weather Integration (External API)</h2>
        <p>Real-time weather query for Hyderabad, India:</p>
        
        <?php if ($weather_info): ?>
            <div class="weather-card">
                <div class="weather-grid">
                    <div class="weather-item">
                        <div class="weather-label">Temperature</div>
                        <div class="weather-val"><?php echo htmlspecialchars($weather_info['temperature']); ?> °C</div>
                    </div>
                    <div class="weather-item">
                        <div class="weather-label">Wind Speed</div>
                        <div class="weather-val"><?php echo htmlspecialchars($weather_info['windspeed']); ?> km/h</div>
                    </div>
                    <div class="weather-item">
                        <div class="weather-label">Weather Code</div>
                        <div class="weather-val"><?php echo htmlspecialchars($weather_info['weathercode']); ?></div>
                    </div>
                    <div class="weather-item">
                        <div class="weather-label">Time Fetched</div>
                        <div class="weather-val" style="font-size: 0.9rem; font-weight: normal; margin-top: 8px;"><?php echo htmlspecialchars($weather_info['time']); ?></div>
                    </div>
                </div>
                <p style="margin-top: 15px; font-size: 0.85rem; color: var(--text-secondary); text-align: center;"><small>Powered by Open-Meteo public API</small></p>
            </div>
        <?php else: ?>
            <div class="alert alert-danger" style="margin-top: 15px;">
                Weather Information currently unavailable. Error: <?php echo htmlspecialchars($weather_error); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
