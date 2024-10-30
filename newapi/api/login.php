<?php
$allow_method = "POST"; // Set the allowed request method to POST
require_once __DIR__ . "/config.php"; // Include the configuration file

use Main as Request;
use Main as Response;

$requestMethod = $_SERVER["REQUEST_METHOD"]; // Get the request method (GET, POST, PUT, DELETE, etc.)

// Handle different request methods
switch($requestMethod) {
    case 'GET':
        require __DIR__.'/AuthMiddleware.php'; // Include authentication middleware
        $allHeaders = getallheaders(); // Get all request headers
        $db_connection = new Database(); // Create a database connection
        $conn = $db_connection->dbConnection(); // Get the database connection
        $auth = new Auth($conn, $allHeaders); // Create an authentication object

        echo json_encode($auth->isValid()); // Check if the user is valid and return JSON response

        echo json_encode($auth->isValid1()); // Check if the customer is valid and return JSON response

        break;
    case 'POST':

        if (Request::check("POST")) {
            require __DIR__.'/classes/JwtHandler.php'; // Include JWT handling class

            function msg($success, $status, $message, $extra = [])
            {
                return array_merge([
                    'success' => $success,
                    'status' => $status,
                    'message' => $message
                ], $extra);
            }

            $db_connection = new Database(); // Create a database connection
            $conn = $db_connection->dbConnection(); // Get the database connection

            $data = json_decode(file_get_contents("php://input")); // Get JSON data from the request body
            $returnData = []; // Initialize an empty array for the response data

            // If request method is not equal to POST
            if ($_SERVER["REQUEST_METHOD"] != "POST") :
                $returnData = msg(0, 404, 'Page Not Found!');
            else :
                $email = trim($data->email); // Get and trim the email from the data
                $password = trim($data->password); // Get and trim the password from the data

                // Check the email format (if invalid format)
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) :
                    $returnData = msg(0, 422, 'Invalid Email Address!');
                // If password is less than 8 characters, show an error
                elseif (strlen($password) < 8) :
                    $returnData = msg(0, 422, 'Your password must be at least 8 characters long!');
                // The user is able to perform the login action
                else :
                    try {
                        // Fetch user data by email from the database
                        $fetch_user_by_email = "SELECT * FROM `users` WHERE `email`=:email";
                        $query_stmt = $conn->prepare($fetch_user_by_email);
                        $query_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                        $query_stmt->execute();

                        // If the user is found by email
                        if ($query_stmt->rowCount()) :
                            $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                            $check_password = password_verify($password, $row['password']);

                            // Verify the password (is correct or not?)
                            // If the password is correct, then send the login token
                            if ($check_password) :

                                $jwt = new JwtHandler();
                                $token = $jwt->jwtEncodeData(
                                    'http://localhost/php_auth_api/',
                                    array("user_id" => $row['id'])
                                );

                                $returnData = [
                                    'success' => 1,
                                    'message' => 'You have successfully logged in.',
                                    'token' => $token
                                ];

                            // If invalid password
                            else :
                                $returnData = msg(0, 422, 'Invalid Password!');
                            endif;

                        // If the user is not found by email
                        else :
                            $returnData = msg(0, 422, 'Invalid Email Address!');
                        endif;
                    } catch (PDOException $e) {
                        $returnData = msg(0, 500, $e->getMessage());
                    }
                endif;
            endif;

            // Print the JSON-encoded response data
            echo json_encode($returnData);
        }
        break;
    default:
        http_response_code(405); // Set HTTP response code to 405 (Method Not Allowed)
        $response = array('message' => 'Method not allowed');
        break;
}

//echo json_encode($response->fetchAll(PDO::FETCH_ASSOC));
?>