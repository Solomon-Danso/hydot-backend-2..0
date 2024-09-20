<?php




function getRoadDistance() {
    // Google Maps API Key
    $apiKey = 'AIzaSyBLQDahpOTNXebv2C3yywMkp9fSvAuu2Xg';  // Replace with your actual API Key

    // Fixed location (your latitude and longitude)
    $fixedLat = 5.6830473;
    $fixedLon = -0.1080339;

    // User's address (hardcoded for this example)
    $userAddress = "Ghana, Greater Accra, East Legon, East Legon Hills Opposite Melcome on the nanakrom road";

    // URL to the Google Geocoding API to get coordinates from the user's address
    $geocodeUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($userAddress) . "&key=" . $apiKey;

    // Initialize cURL session for Geocoding API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $geocodeUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Execute the cURL request and get the response
    $geocodeResponse = curl_exec($ch);
    curl_close($ch);

    // Decode the Geocoding API JSON response
    $geocodeData = json_decode($geocodeResponse, true);

    // Check if the response contains valid location information
    if (isset($geocodeData['results'][0]['geometry']['location'])) {
        // Extract user's latitude and longitude
        $userLat = $geocodeData['results'][0]['geometry']['location']['lat'];
        $userLon = $geocodeData['results'][0]['geometry']['location']['lng'];
    } else {
        return "Unable to convert the user's address to coordinates.";
    }

    // Google Maps Distance Matrix API URL for driving distance
    $distanceUrl = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$userLat},{$userLon}&destinations={$fixedLat},{$fixedLon}&mode=driving&key=" . $apiKey;

    // Initialize cURL session for Distance Matrix API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $distanceUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // Execute the cURL request and get the response
    $distanceResponse = curl_exec($ch);
    curl_close($ch);

    // Decode the Distance Matrix API JSON response
    $distanceData = json_decode($distanceResponse, true);

    // Check if the response contains valid distance information
    if (isset($distanceData['rows'][0]['elements'][0]['distance']['value'])) {
        // Extract the distance (in meters)
        $distanceInMeters = $distanceData['rows'][0]['elements'][0]['distance']['value'];

        // Convert meters to kilometers
        $distanceInKm = $distanceInMeters / 1000;
        $finalDistance = round($distanceInKm, 2);

        // Return the result with proper concatenation in PHP
        return "The driving distance between {$userLat}, {$userLon} and the fixed location is: {$finalDistance} km.";
    } else {
        return "Unable to calculate road distance.";
    }
}

// Example usage
echo getRoadDistance();




// function getCoordinatesFromAddress($address, $apiKey) {
//     // URL to the Google Geocoding API
//     $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;

//     // Initialize cURL session
//     $ch = curl_init();

//     // Set the URL and options for cURL
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//     // Execute the cURL request and get the response
//     $response = curl_exec($ch);

//     // Close the cURL session
//     curl_close($ch);

//     // Decode the JSON response
//     $data = json_decode($response, true);

//     // Check if the response contains valid location information
//     if (isset($data['results'][0]['geometry']['location'])) {
//         $location = $data['results'][0]['geometry']['location'];
//         return array('lat' => $location['lat'], 'lng' => $location['lng']);
//     } else {
//         return false; // Return false if unable to find the location
//     }
// }

// function getRoadDistanceFromUserAddress($userAddress) {
//     // Google Maps API Key
//     $apiKey = 'AIzaSyBLQDahpOTNXebv2C3yywMkp9fSvAuu2Xg';  // Replace with your actual API Key

//     // Fixed location (your latitude and longitude)
//     $fixedLat = 5.63;
//     $fixedLon = -1.5;

//     // Convert the user's address to latitude and longitude using the Geocoding API
//     $userCoords = getCoordinatesFromAddress($userAddress, $apiKey);

//     // If the address conversion failed, return an error
//     if (!$userCoords) {
//         return "Unable to convert the user's address to coordinates.";
//     }

//     // Extract latitude and longitude from the user's coordinates
//     $userLat = $userCoords['lat'];
//     $userLon = $userCoords['lng'];

//     // Google Maps Distance Matrix API URL for driving distance
//     $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$userLat},{$userLon}&destinations={$fixedLat},{$fixedLon}&mode=driving&key={$apiKey}";

//     // Initialize cURL session
//     $ch = curl_init();

//     // Set the URL and options for cURL
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//     // Execute the cURL request and get the response
//     $response = curl_exec($ch);

//     // Close the cURL session
//     curl_close($ch);

//     // Decode the JSON response
//     $data = json_decode($response, true);

//     // Check if the response contains valid distance information
//     if (isset($data['rows'][0]['elements'][0]['distance']['value'])) {
//         // Extract the distance (in meters)
//         $distanceInMeters = $data['rows'][0]['elements'][0]['distance']['value'];

//         // Convert meters to kilometers
//         $distanceInKm = $distanceInMeters / 1000;

//         return round($distanceInKm, 2); // Return the distance in kilometers rounded to 2 decimal places
//     } else {
//         return "Unable to calculate road distance.";
//     }
// }

// // Example usage:
// $userAddress = "Ghana, Accra, Airport City";  // User's address input
// $distance = getRoadDistanceFromUserAddress($userAddress);
// echo "The driving distance between the user's location and the fixed location is: {$distance} km.";






// function getRoadDistance($lat1, $lon1, $lat2, $lon2) {
//     $apiKey = 'AIzaSyBLQDahpOTNXebv2C3yywMkp9fSvAuu2Xg';  // Your Google Maps API Key

//     // Google Maps Distance Matrix API URL for driving distance
//     $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins={$lat1},{$lon1}&destinations={$lat2},{$lon2}&mode=driving&key={$apiKey}";

//     // Initialize cURL session
//     $ch = curl_init();

//     // Set the URL and options for cURL
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//     // Execute the cURL request and get the response
//     $response = curl_exec($ch);

//     // Close the cURL session
//     curl_close($ch);

//     // Decode the JSON response
//     $data = json_decode($response, true);

//     // Check if the response contains valid distance information
//     if (isset($data['rows'][0]['elements'][0]['distance']['value'])) {
//         // Extract the distance (in meters)
//         $distanceInMeters = $data['rows'][0]['elements'][0]['distance']['value'];

//         // Convert meters to kilometers
//         $distanceInKm = $distanceInMeters / 1000;

//         return round($distanceInKm, 2); // Return the distance in kilometers rounded to 2 decimal places
//     } else {
//         return "Unable to calculate road distance.";
//     }
// }




// function getRoadDistanceFree($lat1, $lon1, $lat2, $lon2) {
//     // OSRM API URL for driving distance between two locations
//     $url = "http://router.project-osrm.org/route/v1/driving/{$lon1},{$lat1};{$lon2},{$lat2}?overview=false";

//     // Initialize cURL session
//     $ch = curl_init();

//     // Set the URL and options for cURL
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

//     // Execute the cURL request and get the response
//     $response = curl_exec($ch);

//     // Close the cURL session
//     curl_close($ch);

//     // Decode the JSON response
//     $data = json_decode($response, true);

//     // Check if the response contains routes
//     if (isset($data['routes']) && isset($data['routes'][0]['distance'])) {
//         // Extract the distance (in meters)
//         $distanceInMeters = $data['routes'][0]['distance'];

//         // Convert meters to kilometers
//         $distanceInKm = $distanceInMeters / 1000;

//         return round($distanceInKm, 2); // Return the distance in kilometers rounded to 2 decimal places
//     } else {
//         return "Unable to calculate road distance.";
//     }
// }

// function getDistance($lat1, $lon1, $lat2, $lon2) {
//     $earthRadius = 6371; // Earth's radius in kilometers

//     // Convert degrees to radians
//     $dLat = deg2rad($lat2 - $lat1);
//     $dLon = deg2rad($lon2 - $lon1);

//     // Apply the Haversine formula
//     $a = sin($dLat / 2) * sin($dLat / 2) +
//         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
//         sin($dLon / 2) * sin($dLon / 2);
//     $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

//     // Calculate the distance
//     $distance = $earthRadius * $c;
//     return $distance;
// }



// // Coordinates for User 1 and User 2
// $user1Lat = 5.63;
// $user1Lon = -1.5;
// $user2Lat = 5.68;
// $user2Lon =  -1.8;

// // Calculate the distance
// $distance = getRoadDistance($user1Lat, $user1Lon, $user2Lat, $user2Lon);
// $distanceHaver = getDistance($user1Lat, $user1Lon, $user2Lat, $user2Lon);
// $distanceFree = getRoadDistanceFree($user1Lat, $user1Lon, $user2Lat, $user2Lon);

// // Output the result
// // echo "Distance Google: " . round($distance, 2) . " km";
// // echo "\nDistance Formulae: " . round($distanceHaver, 2) . " km";
// // echo "\nDistance Free: " . round($distanceFree, 2) . " km";
