<?php


function getRoadDistance($lat1, $lon1, $lat2, $lon2) {
    $apiKey = 'AIzaSyBLQDahpOTNXebv2C3yywMkp9fSvAuu2Xg';  // Your Google Maps API Key

    // Google Maps Distance Matrix API URL for driving distance
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$lat1},{$lon1}&destinations={$lat2},{$lon2}&mode=driving&key={$apiKey}";

    // Initialize cURL session
    $ch = curl_init();

    // Set the URL and options for cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Execute the cURL request and get the response
    $response = curl_exec($ch);

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    // Check if the response contains valid distance information
    if (isset($data['rows'][0]['elements'][0]['distance']['value'])) {
        // Extract the distance (in meters)
        $distanceInMeters = $data['rows'][0]['elements'][0]['distance']['value'];

        // Convert meters to kilometers
        $distanceInKm = $distanceInMeters / 1000;

        return round($distanceInKm, 2); // Return the distance in kilometers rounded to 2 decimal places
    } else {
        return "Unable to calculate road distance.";
    }
}




function getRoadDistanceFree($lat1, $lon1, $lat2, $lon2) {
    // OSRM API URL for driving distance between two locations
    $url = "http://router.project-osrm.org/route/v1/driving/{$lon1},{$lat1};{$lon2},{$lat2}?overview=false";

    // Initialize cURL session
    $ch = curl_init();

    // Set the URL and options for cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Execute the cURL request and get the response
    $response = curl_exec($ch);

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    // Check if the response contains routes
    if (isset($data['routes']) && isset($data['routes'][0]['distance'])) {
        // Extract the distance (in meters)
        $distanceInMeters = $data['routes'][0]['distance'];

        // Convert meters to kilometers
        $distanceInKm = $distanceInMeters / 1000;

        return round($distanceInKm, 2); // Return the distance in kilometers rounded to 2 decimal places
    } else {
        return "Unable to calculate road distance.";
    }
}

function getDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth's radius in kilometers

    // Convert degrees to radians
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    // Apply the Haversine formula
    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    // Calculate the distance
    $distance = $earthRadius * $c;
    return $distance;
}



// Coordinates for User 1 and User 2
$user1Lat = 5.63;
$user1Lon = -1.5;
$user2Lat = 5.68;
$user2Lon =  -1.8;

// Calculate the distance
$distance = getRoadDistance($user1Lat, $user1Lon, $user2Lat, $user2Lon);
$distanceHaver = getDistance($user1Lat, $user1Lon, $user2Lat, $user2Lon);
$distanceFree = getRoadDistanceFree($user1Lat, $user1Lon, $user2Lat, $user2Lon);

// Output the result
echo "Distance Google: " . round($distance, 2) . " km";
echo "\nDistance Formulae: " . round($distanceHaver, 2) . " km";
echo "\nDistance Free: " . round($distanceFree, 2) . " km";
