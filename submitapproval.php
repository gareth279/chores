<?php

include 'global.php';

// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
$peopleid = $_SESSION['personid'];
$parent = $_SESSION['parent'];
if($parent == "N"){
    header("location: index.php");
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
    $action = "not set";
    if(isset($_POST['approve'])) $action = "approve";
    if(isset($_POST['reject'])) $action = "reject";
    //echo $action;
    foreach ($_POST as $key => $value) {        
        $choreid = intval($key);
        //echo "<br/>$choreid  ||  $value";            
        $query = "UPDATE chores_done SET approved = \"Y\" WHERE cdid = ?";
        if(isset($_POST['reject'])){
            $query = "UPDATE chores_done SET approved = \"R\" WHERE cdid = ?";
        }
        if(isset($_POST['approve'])){
            $query = "UPDATE chores_done SET approved = \"Y\" WHERE cdid = ?";
        }        
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $choreid); 
        // Execute the SQL statement 
        $stmt->execute(); 
        //$stmt->store_result(); 

        //$result = $mysqli->query($query);


        $stmt->close(); 
    }
    
    $mysqli->close(); 


}
    
    header("Location: approvals.php"); exit;
}
else{
    echo "Not a post";
}





include 'footer.php';
?>