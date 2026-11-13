<?php

include 'global.php';

// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}


//include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
   // echo "Post data";
   // foreach ($_POST as $key => $value) {        
   //     $choreid = intval($key);
    //    echo "<br/>$choreid  ||  $value";        
   // }
   //if(isset($_POST['1'])){
    //       echo "1";
   //}
// Create a MySQLi connection

$mysqli = new mysqli($dbhostname, $dbusername, $dbpassword, $dbdatabase);



// Check for connection errors

if ($mysqli->connect_error) {

    die("Connection failed: " . $mysqli->connect_error);

}

else{

    //$query = "insert into chores_done values (?, ?, ?, ?, ?)";
    foreach ($_POST as $key => $value) {        
        $choreid = intval($key);
        $choreval = 0;
        $stmt = $mysqli->prepare("SELECT val FROM chores WHERE choreid = ?"); 
        $stmt->bind_param("i", $choreid); 
        $stmt->execute(); 
        $stmt->store_result(); 
        if ($stmt->num_rows > 0) {                     
            $stmt->bind_result($choreval);                         
            $stmt->fetch(); 
            $stmt->close();             
            $query = "INSERT INTO chores_done (peopleid, choreid, approved, val) VALUES (?, ?, \"N\", ?);";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("iid", $_SESSION['personid'], $choreid, $choreval);             
            $stmt->execute(); 
        }
        $stmt->close(); 

    }
    
    $mysqli->close(); 


}
    header("Location: history.php"); exit;
}
else{
    echo "Not a post";
}





include 'footer.php';
?>