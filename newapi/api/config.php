<?php
// Set the required headers for handling CORS and JSON responses
if (!isset($allow_method)) $allow_method = "GET";
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: $allow_method");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

// Include the Main class file that contains common functions for handling responses
require_once __DIR__ . "/classes/Main.php";

// Include the user-related class file (assuming it handles user operations)
require_once __DIR__ . "/classes/user.php";

// Include the blog-related class file (assuming it handles blog operations)
require_once __DIR__ . "/classes/blog.php";


?>