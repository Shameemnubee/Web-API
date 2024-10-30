<?php
// Include the necessary PHP files
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Main.php";

// Import the 'Main' class and alias it as 'Response' for convenience
use Main as Response;

// Create a 'User' class that extends 'Database'
class User extends Database
{
    private $DB;

    function __construct()
    {
        $this->DB = Database::__construct();
    }

    private function filter($data)
    {
        return htmlspecialchars(trim(htmlspecialchars_decode($data)), ENT_NOQUOTES);
    }

    // Create a new user
    public function create(string $name, string $email, string $password)
    {
        $name = $this->filter($name);
        $email = $this->filter($email);

        try {
            // Prepare and execute the SQL query to insert a new user
            $sql = "INSERT INTO `users` (`name`, `email`, `password`) VALUES (:name, :email, :password)";
            $stmt = $this->DB->prepare($sql);

            $stmt->bindParam(":name", $name, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":password", $password, PDO::PARAM_STR);

            $stmt->execute();

            // Retrieve the last inserted user ID and send a JSON response
            $last_id = $this->DB->lastInsertId();
            Response::json(1, 201, "User has been created successfully", "user_id", $last_id);

        } catch (PDOException $e) {
            // Handle any database errors and send an error response
            Response::json(0, 500, $e->getMessage());
        }
    }

    // Fetch all users or Get a single user through the user ID
    public function read($id = false, $return = false)
    {
        try {
            $sql = "SELECT * FROM `users`";
            // If user id is provided
            if ($id !== false) {
                // User id must be a number
                if (is_numeric($id)) {
                    $sql = "SELECT * FROM `users` WHERE `id`='$id'";
                } else {
                    Response::_404();
                }
            }
            $query = $this->DB->query($sql);
            if ($query->rowCount() > 0) {
                $allusers = $query->fetchAll(PDO::FETCH_ASSOC);
                // If ID is Provided, send a single user.
                if ($id !== false) {
                    // IF $return is true then return the single user
                    if ($return) return $allusers[0];
                    Response::json(1, 200, null, "user", $allusers[0]);
                }
                Response::json(1, 200, null, "users", $allusers);
            }
            // If the user id does not exist in the database
            if ($id !== false) {
                Response::_404();
            }
            // If there are no users in the database.
            Response::json(1, 200, "Please Insert Some User...", "users", []);
        } catch (PDOException $e) {
            // Handle database errors.
            Response::json(0, 500, $e->getMessage());
        }
    }

    // Update an existing user
    public function update(int $id, Object $data)
    {
        try {
            $sql = "SELECT * FROM `users` WHERE `id`='$id'";
            $query = $this->DB->query($sql);
            if ($query->rowCount() > 0) {
                $the_customer = $query->fetch(PDO::FETCH_OBJ);

                $name = (isset($data->name) && !empty(trim($data->name))) ? $this->filter($data->name) : $the_customer->name;
                $email = (isset($data->email) && !empty(trim($data->email))) ? $this->filter($data->email) : $the_customer->email;
                $password = (isset($data->password) && is_numeric($data->password)) ? $data->password : $the_customer->password;

                $update_sql = "UPDATE `users` SET `name`=:name, `email`=:email, `password`=:password, `updated_at`=NOW() WHERE `id`='$id'";

                $stmt = $this->DB->prepare($update_sql);
                $stmt->bindParam(":name", $name, PDO::PARAM_STR);
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                $stmt->bindParam(":password", $password, PDO::PARAM_STR);

                $stmt->execute();

                // Send a JSON response indicating successful user update
                Response::json(1, 200, "User Updated Successfully", "user", $this->read($id, true));
            }

            // Send a 404 response for an invalid User ID.
            Response::json(0, 404, "Invalid User ID.");

        } catch (PDOException $e) {
            // Handle any database errors and send an error response
            Response::json(0, 500, $e->getMessage());
        }
    }

    // Delete a User
    public function delete(int $id)
    {
        try {
            $sql =  "DELETE FROM `users` WHERE `id`='$id'";
            $query = $this->DB->query($sql);
            if ($query->rowCount() > 0) {
                // Send a JSON response indicating successful user deletion
                Response::json(1, 200, "User has been deleted successfully.");
            }
            // Send a 404 response for an invalid User ID.
            Response::json(0, 404, "Invalid User ID.");
        } catch (PDOException $e) {
            // Handle any database errors and send an error response
            Response::json(0, 500, $e->getMessage());
        }
    }
}
?>