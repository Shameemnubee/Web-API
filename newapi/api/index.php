<?php
// Include the configuration file and necessary class files
include_once 'config.php';
include_once 'classes/Main.php';
include_once 'classes/Database.php';

// Get the request URI
$request = $_SERVER['REQUEST_URI'];

// Use a substring of the request, removing '/newapi/api' at the beginning
$endpoint = substr($request, strlen('/newapi/api'));

// Split the endpoint into its components using '/' as the delimiter
$endpoint_parts = explode('/', $endpoint);

// Determine the endpoint and include the corresponding controller
switch ($endpoint_parts[1]) {
    case 'log':
        // Include the login controller
        include_once 'login.php';
        break;
    case 'users':
        // Include the user controller
        include_once 'usercontroller.php';
        break;
    case 'blog':
        // Include the blogs controller
        include_once 'blogscontroller.php';
        break;

    default:
        // If no matching endpoint is found, set a 404 response code and return a JSON error message
        http_response_code(404);
        echo json_encode(
            array("message" => "No route found.")
        );
        break;
}
?>