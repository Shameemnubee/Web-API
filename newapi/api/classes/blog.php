<?php
// Include the necessary PHP files
require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Main.php";

// Import the 'Main' class and alias it as 'Response' for convenience
use Main as Response;

// Create a 'Blog' class that extends 'Database'
class Blog extends Database
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

    // Create a new Post
    public function create(string $article_title, string $article_topic, string $article_info)
    {
        $article_title = $this->filter($article_title);
        $article_topic = $this->filter($article_topic);
        $article_info = $this->filter($article_info);

        try {
            // Prepare and execute the SQL query to insert a new post
            $sql = "INSERT INTO `blog` (`article_title`, `article_topic`, `article_info`) VALUES (:article_title, :article_topic, :article_info)";
            $stmt = $this->DB->prepare($sql);

            $stmt->bindParam(":article_title", $article_title, PDO::PARAM_STR);
            $stmt->bindParam(":article_topic", $article_topic, PDO::PARAM_STR);
            $stmt->bindParam(":article_info", $article_info, PDO::PARAM_STR);
           

            $stmt->execute();

            // Retrieve the last inserted Post ID and send a JSON response
            $last_id = $this->DB->lastInsertId();
            Response::json(1, 201, "Post has been created successfully", "id", $last_id);

        } catch (PDOException $e) {
            // Handle any database errors and send an error response
            Response::json(0, 500, $e->getMessage());
        }
    }

    // Fetch all post or Get a single post through the post ID
    public function read($id = false, $return = false)
    {
        try {
            $sql = "SELECT * FROM `blog`";
            // If post id is provided
            if ($id !== false) {
                // post id must be a number
                if (is_numeric($id)) {
                    $sql = "SELECT * FROM `blog` WHERE `id`='$id'";
                } else {
                    Response::_404();
                }
            }
            $query = $this->DB->query($sql);
            if ($query->rowCount() > 0) {
                $allpost = $query->fetchAll(PDO::FETCH_ASSOC);
                // If ID is Provided, send a single post.
                if ($id !== false) {
                    // IF $return is true then return the single post
                    if ($return) return $allpost[0];
                    Response::json(1, 200, null, "blog", $allpost[0]);
                }
                Response::json(1, 200, null, "blogs", $allpost);
            }
            // If the post id does not exist in the database
            if ($id !== false) {
                Response::_404();
            }
            // If there are no post in the database.
            Response::json(1, 200, "Please Insert Some post...", "posts", []);
        } catch (PDOException $e) {
            // Handle database errors.
            Response::json(0, 500, $e->getMessage());
        }
    }

    // Update an existing post
    public function update(int $id, Object $data)
    {
        try {
            $sql = "SELECT * FROM `blog` WHERE `id`='$id'";
            $query = $this->DB->query($sql);
            if ($query->rowCount() > 0) {
                $the_post = $query->fetch(PDO::FETCH_OBJ);

                $article_title = (isset($data->article_title) && !empty(trim($data->article_title))) ? $this->filter($data->article_title) : $the_post->article_title;
                $article_topic = (isset($data->article_topic) && !empty(trim($data->article_topic))) ? $this->filter($data->article_topic) : $the_post->article_topic;
                $article_info = (isset($data->article_info) && !empty(trim($data->article_info))) ? $this->filter($data->article_info) : $the_post->article_info;

                $update_sql = "UPDATE `blog` SET `article_title`=:article_title, `article_topic`=:article_topic, `article_info`=:article_info,`updated_at`=NOW() WHERE `id`='$id'";

                $stmt = $this->DB->prepare($update_sql);
                $stmt->bindParam(":article_title", $article_title, PDO::PARAM_STR);
                $stmt->bindParam(":article_topic", $article_topic, PDO::PARAM_STR);
                $stmt->bindParam(":article_info", $article_info, PDO::PARAM_STR);
            

                $stmt->execute();

                // Send a JSON response indicating successful post update
                Response::json(1, 200, "Post Updated Successfully", "post", $this->read($id, true));
            }

            // Send a 404 response for an invalid Post ID.
            Response::json(0, 404, "Invalid Post ID.");

        } catch (PDOException $e) {
            // Handle any database errors and send an error response
            Response::json(0, 500, $e->getMessage());
        }
    }

    // Delete a Post
    public function delete(int $id)
    {
        try {
            $sql =  "DELETE FROM `blog` WHERE `id`='$id'";
            $query = $this->DB->query($sql);
            if ($query->rowCount() > 0) {
                // Send a JSON response indicating successful Post deletion
                Response::json(1, 200, "Post has been deleted successfully.");
            }
            // Send a 404 response for an invalid Post ID.
            Response::json(0, 404, "Invalid Post ID.");
        } catch (PDOException $e) {
            // Handle any database errors and send an error response
            Response::json(0, 500, $e->getMessage());
        }
    }
}
?>