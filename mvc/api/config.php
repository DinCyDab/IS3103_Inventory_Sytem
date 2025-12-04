<?php 
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //NO NEED TO CHANGE ANYTHING FROM HERE
    //Just call config from a model to connect to the database
    class Config{
        private $conn;
        //Put these on (dot)env later
        public function __construct($port = 3306,
                                    $hostname = "localhost", 
                                    $username = "root",
                                    $password = "",
                                    $database = "inventory_system"){
            $this->conn = new mysqli($hostname, $username, $password, $database, $port);
            if($this->conn->connect_errno){
                die("Error Connection" . $this->conn->connect_error);
            }
        }

        //Use query() to insert, update, or delete
        //After insertion, update, or delete
        //Return the inserted id or
        //Return the updated row or
        //Return the false if update, insert, or delete failed.
        public function query($sql){
            $result = $this->conn->query($sql);
            if($result){
                if($this->conn->insert_id){
                    return $this->conn->insert_id;
                }
                
                return $this->conn->affected_rows;
            }
            return false;
        }

        //Use read to fetch data from the database
        //Returns the array of fetched data
        public function read($sql){
            $result = $this->conn->query($sql);
            if($result && $result->num_rows > 0){
                return $result->fetch_all(MYSQLI_ASSOC);
            }
            return [];
        }

<<<<<<< Updated upstream
=======
        public function readOne($sql) {
             $result = $this->conn->query($sql);
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();  // return single row
            }
            return null;
        }


        // Added prepare function
        public function prepare($sql){
            return $this->conn->prepare($sql);
        }

>>>>>>> Stashed changes
        //Don't forget to close the connection
        //close() function closes the connection
        public function close(){
            $this->conn->close();
        }
    }
?>