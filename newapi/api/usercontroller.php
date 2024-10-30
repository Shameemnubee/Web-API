<?php
// Define the allowed HTTP request method.
$allow_method = "POST";
// Include the configuration file.
require_once __DIR__ . "/config.php";

// Create aliases for Request and Response classes (not typical usage).
use Main as Request;
use Main as Response;

// Get the current HTTP request method.
$requestMethod = $_SERVER["REQUEST_METHOD"];

// Handle different request methods.
switch($requestMethod) {
    case 'GET':
        // Check if the request is a GET request using the Request::check() function.
        if (Request::check("GET")) {
            // Instantiate a User object and read user data based on the provided ID.
            $user = new User();
            if (isset($_GET['id'])) $user->read(trim($_GET['id']));
            $user->read();
        }
        break;
   
    case 'POST':
        // Check if the request is a POST request using the Request::check() function.
        if (Request::check("POST")) {
            // Establish a database connection.
            $db_connection = new Database();
            $conn = $db_connection->dbConnection();
            
            // Define a function to format and return JSON response messages.
            function msg($success, $status, $message, $extra = [])
            {
                return array_merge([
                    'success' => $success,
                    'status' => $status,
                    'message' => $message
                ], $extra);
            }
            
            // Retrieve and parse data from the request body.
            $data = json_decode(file_get_contents("php://input"));
            $returnData = [];
            
            // Check if the HTTP method is not POST.
            if ($_SERVER["REQUEST_METHOD"] != "POST") :
                $returnData = msg(0, 404, 'Page Not Found!');
            // Check for missing or empty fields in the submitted data.
            elseif (
                !isset($data->name)
                || !isset($data->email)
                || !isset($data->password)
                || empty(trim($data->name))
                || empty(trim($data->email))
                || empty(trim($data->password))
            ) :
                $fields = ['fields' => ['name', 'email', 'password']];
                $returnData = msg(0, 422, 'Please Fill in all Required Fields!', $fields);
            // If there are no missing fields, proceed with user registration.
            else :
                $name = trim($data->name);
                $email = trim($data->email);
                $password = trim($data->password);
                
                // Validate the email format.
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) :
                    $returnData = msg(0, 422, 'Invalid Email Address!');
                // Check if the password length is less than 8 characters.
                elseif (strlen($password) < 8) :
                    $returnData = msg(0, 422, 'Your password must be at least 8 characters long!');
                // Check if the name length is less than 3 characters.
                elseif (strlen($name) < 3) :
                    $returnData = msg(0, 422, 'Your name must be at least 3 characters long!');
                else :
                    try {
                        // Check if the provided email is already in use.
                        $check_email = "SELECT `id` FROM `users` WHERE `email`=:email";
                        $check_email_stmt = $conn->prepare($check_email);
                        $check_email_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                        $check_email_stmt->execute();
        
                        // If the email is already in use, return an error message.
                        if ($check_email_stmt->rowCount()) :
                            $returnData = msg(0, 422, 'This E-mail already in use!');
                        // If the email is not in use, insert the new user into the database.
                        else :
                            $insert_query = "INSERT INTO `users`(`name`,`email`, `password`) VALUES(:name,:email,:password)";
        
                            $insert_stmt = $conn->prepare($insert_query);
        
                            // Data binding for insertion.
                            $insert_stmt->bindValue(':name', htmlspecialchars(strip_tags($name)), PDO::PARAM_STR);
                            $insert_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                            $insert_stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                        
                            $insert_stmt->execute();
        
                            $returnData = msg(1, 201, 'You have successfully registered.');
                        endif;
                    } catch (PDOException $e) {
                        $returnData = msg(0, 500, $e->getMessage());
                    }
                endif;
            endif;
        
            // Return the JSON response to the client.
            echo json_encode($returnData);
        }
        break;
    
    case 'PUT':
        // Check if the request is a PUT request using the Request::check() function.
        if (Request::check("PUT")) {
            // Retrieve and parse data from the request body.
            $data = json_decode(file_get_contents("php://input"));
            
            // Define fields and their descriptions for validation.
            $fields = [
                "id" => "user ID (Required)",
                "name" => "user name (Optional)",
                "email" => "user email (Optional)",
                "password" => "Customer password (Optional)",
            ];
            
            // Check if a valid user ID is provided.
            if (!isset($data->id) || !is_numeric($data->id)) :
                Response::json(0, 400, "Please provide the valid User ID and at least one field.", "fields", $fields);
            endif;
            
            // Check for empty fields in the submitted data.
            $isEmpty = true;
            $empty_fields =  [];
            
            foreach((array)$data as $key => $val){
                if (in_array($key, ["id","name","email","password"])){
                    if(!empty(trim($val))){
                        $isEmpty = false;
                    }
                    else{
                        array_push($empty_fields, $key);
                    }
                }
            }
            
            // If there are empty fields, return an error message.
            if($isEmpty){
                $has_empty_fields = count($empty_fields);
                Response::json(0, 400,
                $has_empty_fields ? "Oops! empty field detected." : "Please provide the User ID and at least one field.",
                $has_empty_fields ? "empty_fields" : "fields",
                $has_empty_fields ? $empty_fields : $fields);
            }
            
            // Instantiate a User object and update the user data.
            $user = new User();
            $user->update($data->id, $data);
        }
        break;
    
    case 'DELETE':
        // Check if the request is a DELETE request using the Request::check() function.
        if (Request::check("DELETE")) {
            // Retrieve and parse data from the request body.
            $data = json_decode(file_get_contents("php://input"));
            
            // Check if a valid user ID is provided.
            if (!isset($data->id) || !is_numeric($data->id)) :
                Response::json(0, 400, "Please provide the valid User ID");
            endif;
            
            // Instantiate a User object and delete the user data.
            $user = new User();
            $user->delete($data->id);
        }
        break;
    
    default:
        // Return a "Method Not Allowed" response if the HTTP method is not handled.
        http_response_code(405);
        $response = array('message' => 'Method not allowed');
        break;
}

//echo json_encode($response->fetchAll(PDO::FETCH_ASSOC));