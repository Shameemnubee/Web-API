<?php
require __DIR__ . '/classes/JwtHandler.php';
$allow_method = "POST"; // Set the allowed request method to POST
require_once __DIR__ . "/config.php"; // Include the configuration file

use Main as Request;
use Main as Response;

class BlogAuth extends JwtHandler
{
    protected $db;
    protected $headers;
    protected $token;

    public function __construct($db, $headers)
    {
        parent::__construct(); // Call the constructor of the parent class (JwtHandler)
        $this->db = $db; // Initialize the database connection
        $this->headers = $headers; // Initialize the request headers
    }

    public function getBlog()
    {
        // Check if the Authorization header with a valid Bearer token exists
        if (array_key_exists('Authorization', $this->headers) && preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) {

            $data = $this->jwtDecodeData($matches[1]); // Decode the JWT token

            $blog = new blog(); // Create a Blog object
            if (isset($_GET['id'])) $blog->read(trim($_GET['id'])); // Read Blog data by ID

            if (
                isset($data['data']->user_id) &&
                $user = $this->fetchUser($data['data']->user_id)
            ) :
                return [
                    "success" => 1,
                    "user" =>  $blog->read() // Return Blog data
                ];
            else :
                return [
                    "success" => 0,
                    "message" => $data['message'],
                ];
            endif;
        } else {
            return [
                "success" => 0,
                "message" => "Token not found in request"
            ];
        }
    }

    public function InsertBlog()
    {
        // Check if the Authorization header with a valid Bearer token exists
        if (array_key_exists('Authorization', $this->headers) && preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) {
            $data = $this->jwtDecodeData($matches[1]); // Decode the JWT token
            $data1 = json_decode(file_get_contents("php://input")); // Parse JSON input data

            // Check for missing or empty fields
            if (
                !isset($data1->article_title) ||
                !isset($data1->article_topic) ||
                !isset($data1->article_info)
               
            ) :
                $fields = [
                    "article_title" => "Post title",
                    "article_topic" => "Post topic",
                    "article_info" => "Post content"

                ];
                Response::json(0, 400, "Please fill all the required fields", "fields", $fields);

            elseif (
                empty(trim($data1->article_title)) ||
                empty(trim($data1->article_topic)) ||
                empty(trim($data1->article_info))
                
            ) :
                $fields = [];
                foreach ($data1 as $key => $val) {
                    if (empty(trim($val))) array_push($fields, $key);
                }
                Response::json(0, 400, "Oops! empty field detected.", "empty_fields", $fields);

            else :
                $blog = new blog(); // Create a Blog object

                if (
                    isset($data['data']->user_id) &&
                    $user = $this->fetchUser($data['data']->user_id)
                ) :
                    return [
                        "success" => 1,
                        "user" =>  $blog->create($data1->article_title, $data1->article_topic, $data1->article_info)
                    ];
                else :
                    return [
                        "success" => 0,
                        "message" => $data['message'],
                    ];
                endif;
            endif;
        }
        else {
            return [
                "success" => 0,
                "message" => "Token not found in request"
            ];
        }
    }

    public function UpdateBlog()
    {
        // Check if the Authorization header with a valid Bearer token exists
        if (array_key_exists('Authorization', $this->headers) && preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) {
            $data = $this->jwtDecodeData($matches[1]); // Decode the JWT token
            $data1 = json_decode(file_get_contents("php://input")); // Parse JSON input data
            $blog = new blog(); // Create a Blog object

            $fields = [
                "id" => "Post ID (Required)",
                "article_title" => "Post title (Optional)",
                "article_topic" => "Post topic (Optional)",
                "article_info" => "Post content (Optional)"
            ];

            // Check for missing or invalid fields
            if (!isset($data1->id) || !is_numeric($data1->id)) {
                Response::json(0, 400, "Please provide the valid Post ID and at least one field.", "fields", $fields);
            }

            $isEmpty = true;
            $empty_fields =  [];

            foreach((array)$data1 as $key => $val){
                if (in_array($key, ["id","article_title","article_topic","article_info"])){
                    if(!empty(trim($val))){
                        $isEmpty = false;
                    }
                    else{
                        array_push($empty_fields, $key);
                    }
                }
            }

            if($isEmpty){
                $has_empty_fields = count($empty_fields);
                Response::json(0, 400,
                $has_empty_fields ? "Oops! empty field detected." : "Please provide the Post ID and at least one field.",
                $has_empty_fields ? "empty_fields" : "fields",
                $has_empty_fields ? $empty_fields : $fields);
            } else {

                if (
                    isset($data['data']->user_id) &&
                    $user = $this->fetchUser($data['data']->user_id)
                ) {
                    return [
                        "success" => 1,
                        "user" => $blog->update($data1->id, $data1)
                    ];
                } else {
                    return [
                        "success" => 0,
                        "message" => $data['message'],
                    ];
                }
            }
        } else {
            return [
                "success" => 0,
                "message" => "Token not found in request"
            ];
        }
    }

    public function DeleteBlog()
    {
        // Check if the Authorization header with a valid Bearer token exists
        if (array_key_exists('Authorization', $this->headers) && preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) {

            $data = $this->jwtDecodeData($matches[1]); // Decode the JWT token

            $blog = new blog(); // Create a Blog object
            $data1 = json_decode(file_get_contents("php://input")); // Parse JSON input data

            // Check for a valid Blog ID
            if (!isset($data1->id) || !is_numeric($data1->id)) :
                Response::json(0, 400, "Please provide the valid Post ID");
            endif;

            if (
                isset($data['data']->user_id) &&
                $user = $this->fetchUser($data['data']->user_id)
            ) :
                return [
                    "success" => 1,
                    "user" =>  $blog->delete($data1->id)
                ];
            else :
                return [
                    "success" => 0,
                    "message" => $data['message'],
                ];
            endif;
        } else {
            return [
                "success" => 0,
                "message" => "Token not found in request"
            ];
        }
    }

    // Helper function to fetch user data
    protected function fetchUser($user_id)
    {
        try {
            $fetch_user_by_id = "SELECT `name`,`email` FROM `users` WHERE `id`=:id";
            $query_stmt = $this->db->prepare($fetch_user_by_id);
            $query_stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
            $query_stmt->execute();

            if ($query_stmt->rowCount()) :
                return $query_stmt->fetch(PDO::FETCH_ASSOC);
            else :
                return false;
            endif;
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>