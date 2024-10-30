<?php
class Main
{
    // Checking the Request Method
    static function check($req)
    {
        // Check if the current HTTP request method matches the provided $req
        if ($_SERVER["REQUEST_METHOD"] === $req) {
            return true; // Method matches, return true
        }

        // If the method does not match, send a JSON response with a 405 status code (Method Not Allowed)
        static::json(0, 405, "Invalid Request Method. HTTP method should be $req");
    }

    // Returns the response in JSON format
    static function json(int $ok, $status, $msg, $key = false, $value = false)
    {
        // Create an array to build the JSON response
        $res = ["ok" => $ok];

        // Set the HTTP response code based on the provided $status
        if ($status !== null) {
            http_response_code($status);
            $res["status"] = $status;
        }

        // Add a message to the response if provided
        if ($msg !== null) {
            $res["message"] = $msg;
        }

        // Add additional data to the response if both $key and $value are provided
        if ($value) {
            if ($key) {
                $res[$key] = $value;
            } else {
                $res["data"] = $value;
            }
        }

        // Encode the response array as JSON and send it as the HTTP response
        echo json_encode($res);

        // Exit the script
        exit;
    }

    // Returns the 404 Not found
    static function _404()
    {
        // Send a JSON response with a 404 status code (Not Found) and a "Not Found!" message
        static::json(0, 404, "Not Found!");
    }
}
