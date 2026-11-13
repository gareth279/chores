<?php




require_once 'global.php';
start_app_session();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
$loginerrormessage = "";
// Include config file
//require_once "config.php";
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";
 
$peopleid = $_SESSION['personid'];
$emailfromfrom = $_SESSION['username'];

 if (isset($_POST['submit'])) { 

if (!csrf_valid()) { http_response_code(403); exit('Invalid request token. Reload and try again.'); }

$userpassword = $_POST['currentPassword']; 
$newpassword = $_POST['newPassword']; 
$confirmpassword = $_POST['confirmPassword']; 
    if($newpassword != $confirmpassword){    
        $loginerrormessage = "New password is not the same.";
    }
    else{
// Create a MySQLi connection

$mysqli = new mysqli($dbhostname, $dbusername, $dbpassword, $dbdatabase);

    // Connect to the database 
//    $mysqli = new mysqli("localhost", "username", "password", "login_system"); 
    
    // Check for errors 
    if ($mysqli->connect_error) { error_log('Chores database connection failed: ' . $mysqli->connect_error); http_response_code(500); exit('The request could not be completed. Please try again later.'); }
    // Prepare and bind the SQL statement 
    $stmt = $mysqli->prepare("SELECT peopleid, name, password, parent FROM people WHERE email = ?"); 
    
    
    // Get the form data 
    //$emailfromfrom = $_POST['username']; 
    
    
    
    $stmt->bind_param("s", $emailfromfrom); 
    // Execute the SQL statement 
    $stmt->execute(); 
    $stmt->store_result(); 
    
    // Check if the user exists 
    if ($stmt->num_rows > 0) { 
    
    // Bind the result to variables 
    $stmt->bind_result($id, $personname, $hashed_password, $parent); 
    
    // Fetch the result 
    $stmt->fetch(); 
    
    // Verify the password 
    if (password_verify($userpassword, $hashed_password)) { 
    
    // Set the session variables 
    /*
    $_SESSION['loggedin'] = true; 
    $_SESSION['personid'] = $id; 
    $_SESSION['personname'] = $personname; 
    $_SESSION['username'] = $emailfromfrom; 
    $_SESSION['parent'] = $parent; 
    */
    
    // Redirect to the user's dashboard 
    //header("Location: changepassword.php"); exit; 
        $stmt->close(); 
        $query = "UPDATE people SET password = ? WHERE email = ?";
        $stmt = $mysqli->prepare($query);      
        $newpasswordhash = password_hash($newpassword, PASSWORD_DEFAULT);  
        $stmt->bind_param("ss", $newpasswordhash, $emailfromfrom); 
        // Execute the SQL statement 
        $stmt->execute(); 
        //$stmt->close(); 
        $loginerrormessage = "Password changed.";
    } else { $loginerrormessage = "Incorrect password!"; } } else { $loginerrormessage = "User not found!"; } 
    
    // Close the connection 
    $stmt->close(); 
    $mysqli->close(); }
}
//////////////////////////

include 'header.php';
//echo $loginerrormessage
?>
<div class="chores">
<!--form action="login.php" method="post">
  <label for="username">Username:</label>
  <input id="username" name="username" required="" type="text" />
  <label for="password">Password:</label> <input id="password" name="password" required="" type="password" />
  <input name="login" type="submit" value="Login" />
</form-->


<form name="changepassword.php" method="post">
            <?php echo csrf_field(); ?>

            <div class="validation-message text-center"><?php if(isset($loginerrormessage)) { echo h($loginerrormessage); } ?></div>
            <h2 class="text-center">Change Password</h2>
            <div>
                <div class="row">
                    <label class="inline-block">Current Password</label>
                    <span id="currentPassword"
                        class="validation-message"></span> <input
                        type="password" name="currentPassword"
                        class="full-width">

                </div>
                <div class="row">
                    <label class="inline-block">New Password</label> <span
                        id="newPassword" class="validation-message"></span><input
                        type="password" name="newPassword"
                        class="full-width">

                </div>
                <div class="row">
                    <label class="inline-block">Confirm Password</label>
                    <span id="confirmPassword"
                        class="validation-message"></span><input
                        type="password" name="confirmPassword"
                        class="full-width">

                </div>
                <div class="row">
                    <input type="submit" name="submit" value="Submit"
                        class="full-width">
                </div>
            </div>

        </form>

    </div>
<?php

include 'footer.php';
?>
