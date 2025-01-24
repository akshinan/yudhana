<?php
session_start();

ini_set('session.cookie_secure', true);
ini_set('session.cookie_httponly', true);
ini_set('session.use_strict_mode', true);
session_regenerate_id(true);

$config = require 'config.php';
$conn = new mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Service unavailable. Please try again later.");
}

function logUserActivity($conn) {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $deviceInfo = getDeviceInfo();
    $geolocation = getGeolocation($ipAddress);
    $referralSource = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Direct';
    $browserInfo = $_SERVER['HTTP_USER_AGENT'];
    $screenResolution = isset($_POST['screen_resolution']) ? $_POST['screen_resolution'] : 'Unknown';
    $operatingSystem = php_uname('s');
    $languagePreferences = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

    $clickPatterns = 'To be captured via JavaScript';
    $scrollDepth = 'To be captured via JavaScript';
    $conversionData = 'To be defined';
    $utmParameters = isset($_POST['utm_parameters']) ? $_POST['utm_parameters'] : 'None';
    $heatmapData = 'To be captured via tools like Hotjar';
    $sessionReplay = 'To be captured via tools like Hotjar';

    $stmt = $conn->prepare("INSERT INTO user_activity_logs (
        ip_address, device_info, geolocation, referral_source, browser_info,
        screen_resolution, operating_system, language_preferences, click_patterns,
        scroll_depth, conversion_data, utm_parameters, heatmap_data, session_replay
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssssssssssssss", $ipAddress, $deviceInfo, $geolocation, $referralSource, $browserInfo, $screenResolution, $operatingSystem, $languagePreferences, $clickPatterns, $scrollDepth, $conversionData, $utmParameters, $heatmapData, $sessionReplay);
    $stmt->execute();
    $stmt->close();
}

function getDeviceInfo() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    if (preg_match('/mobile/i', $userAgent)) {
        return 'Mobile';
    } elseif (preg_match('/tablet/i', $userAgent)) {
        return 'Tablet';
    } else {
        return 'Desktop';
    }
}

function getGeolocation($ipAddress) {
    $apiUrl = "http://ip-api.com/json/$ipAddress";
    $response = file_get_contents($apiUrl);
    $data = json_decode($response, true);

    if ($data['status'] === 'success') {
        return $data['city'] . ', ' . $data['country'];
    }
    return 'Unknown';
}

logUserActivity($conn);

$conn->close();
?>

<html>
<head>
    <meta charset="UTF-8">
    <title>Yudhana Nanayakkara</title>
	<link rel="icon" type="image/jpg" href="https://files.catbox.moe/ryx58i.png">

    <style>
	body { 
	  line-height: 1.4;
	    font-size: 16px;
	    padding: 0 10px;
	    margin: 50px auto;
	    max-width: 650px;
	}

	#maincontent
	{
	max-width:42em;margin:15 auto;

	}
</style>	
</head>


<body>
<audio id="backgroundAudio" autoplay loop preload="auto">
    <source src="aline.mp3" type="audio/mpeg">
</audio>

<div id="maincontent" style="margin-top:70px">
	</div>
  <h2>Yudhana Nanayakkara</h2>
  
  <br>
  </body>
  <footer>
    <a href="contact.html">Kontact</a>
    <a href="YAN/">Login</a>
    </footer>
	<script>
    document.addEventListener("DOMContentLoaded", function() {
        var audio = new Audio();
        audio.src = "path_to_your_audio_file.mp3";
        audio.preload = "auto";
        audio.play(); 
    });
</script>
	<script>
const screenResolution = `${window.screen.width}x${window.screen.height}`;

fetch('log_activity.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        screen_resolution: screenResolution,
        utm_parameters: new URLSearchParams(window.location.search).toString()
    })
});
	</script>	
</html>
