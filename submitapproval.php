<?php

include 'global.php';

// Initialize the session
start_app_session();
 
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
    if (!csrf_valid()) { http_response_code(403); exit('Invalid request token. Reload and try again.'); }
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

    error_log('Chores database connection failed: ' . $mysqli->connect_error); http_response_code(500); exit('The request could not be completed. Please try again later.');

}

else{

    //$query = "insert into chores_done values (?, ?, ?, ?, ?)";
    $action = "not set";
    if(isset($_POST['approve'])) $action = "approve";
    if(isset($_POST['reject'])) $action = "reject";
    if ((isset($_POST['approve']) ? 1 : 0) + (isset($_POST['reject']) ? 1 : 0) !== 1) {
        http_response_code(400);
        exit('Choose either approve or reject.');
    }
    //echo $action;
    foreach ($_POST as $key => $value) {
        if (!ctype_digit((string)$key) || $value !== '1') { continue; }
        $choreid = (int)$key;
        //echo "<br/>$choreid  ||  $value";            
        $query = "UPDATE chores_done SET approved = 'Y' WHERE cdid = ? AND approved = 'N'";
        if(isset($_POST['reject'])){
            $query = "UPDATE chores_done SET approved = 'R' WHERE cdid = ? AND approved = 'N'";
        }
        if(isset($_POST['approve'])){
            $query = "UPDATE chores_done SET approved = 'Y' WHERE cdid = ? AND approved = 'N'";
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
