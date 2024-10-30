<?php
class Database{
    
    // CHANGE THE DB INFO ACCORDING TO YOUR DATABASE
    private $db_host = 'localhost';     // Database host ('localhost')
    private $db_name = 'newapi';       // Database name
    private $db_username = 'root';      // Database username
    private $db_password = '';          // Database password
    
    // Establish a database connection and return the connection object
    public function dbConnection(){
        
        try{
            // Create a PDO database connection
            $conn = new PDO('mysql:host='.$this->db_host.';dbname='.$this->db_name,$this->db_username,$this->db_password);
            
            // Set PDO attributes for error handling
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Return the database connection object
            return $conn;
        }
        catch(PDOException $e){
            // Handle any connection errors and display an error message
            echo "Connection error ".$e->getMessage(); 
            exit;
        }
    }

    // Constructor method to create a database connection
    function __construct()
    {
        try {
            // Define the Data Source Name (DSN) for the database connection
            $dsn = "mysql:host={$this->db_host};dbname={$this->db_name};charset=utf8";
            
            // Create a PDO database connection
            $db_connection = new PDO($dsn, $this->db_username, $this->db_password);
            
            // Set PDO attributes for error handling
            $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Return the database connection object
            return $db_connection;
        } catch (PDOException $e) {
            // Handle any connection errors and display an error message
            echo "Connection error " . $e->getMessage();
            exit;
        }
    }
}
?>