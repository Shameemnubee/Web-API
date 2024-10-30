<?php
// Set the allowed HTTP request method to POST
$allow_method = "POST";

// Include the configuration file
require_once __DIR__ . "/config.php";

// Import the Request and Response namespaces as aliases
use Main as Request;
use Main as Response;

// Get the HTTP request method (GET, POST, PUT, DELETE)
$requestMethod = $_SERVER["REQUEST_METHOD"];

// Require the 'blogs.php' file that contains the BlogAuth class
require __DIR__.'/blogs.php';

// Get all HTTP request headers
$allHeaders = getallheaders();

// Create a database connection
$db_connection = new Database();
$conn = $db_connection->dbConnection();

// Handle different request methods
switch($requestMethod) {
    case 'GET':
        // Create an instance of the BlogAuth class for GET requests
        $auth = new BlogAuth($conn, $allHeaders);
        
        // Echo the JSON response of the getBlog method
        echo json_encode($auth->getBlog());
        break;

    case 'POST':
        // Create an instance of the BlogAuth class for POST requests
        $auth1 = new BlogAuth($conn, $allHeaders);
        
        // Echo the JSON response of the InsertBlog method
        echo json_encode($auth1->InsertBlog());
        break;

    case 'PUT':
        // Create an instance of the BlogAuth class for PUT requests
        $auth3 = new BlogAuth($conn, $allHeaders);
        
        // Echo the JSON response of the UpdateBlog method
        echo json_encode($auth3->UpdateBlog());
        break;

    case 'DELETE':
        // Create an instance of the BlogAuth class for DELETE requests
        $auth2 = new BlogAuth($conn, $allHeaders);
        
        // Echo the JSON response of the DeleteBlog method
        echo json_encode($auth2->DeleteBlog());
        break;
}
?>