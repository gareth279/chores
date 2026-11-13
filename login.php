<?php




require_once 'global.php';
start_app_session();

// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}
$loginerrormessage = "";
// Include config file
//require_once "config.php";
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = $login_err = "";
 

 if (isset($_POST['login'])) { 
// Create a MySQLi connection

$mysqli = new mysqli($dbhostname, $dbusername, $dbpassword, $dbdatabase);

    // Connect to the database 
//    $mysqli = new mysqli("localhost", "username", "password", "login_system"); 
    
    // Check for errors 
    if ($mysqli->connect_error) { error_log('Chores database connection failed: ' . $mysqli->connect_error); http_response_code(500); exit('The request could not be completed. Please try again later.'); }
    // Prepare and bind the SQL statement 
    $stmt = $mysqli->prepare("SELECT peopleid, name, password, parent FROM people WHERE email = ?"); 
    
    
    // Get the form data 
    $emailfromfrom = $_POST['username']; 
    
    $userpassword = $_POST['password']; 
    
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
    
    // Rotate the session identifier after authentication.
    session_regenerate_id(true);
    // Set the session variables 
    $_SESSION['loggedin'] = true; 
    $_SESSION['personid'] = $id; 
    $_SESSION['personname'] = $personname; 
    $_SESSION['username'] = $emailfromfrom; 
    $_SESSION['parent'] = $parent; 
    
    // Redirect to the user's dashboard 
    //PHPSESSID
    //session_set_cookie_params(3600);
    //$params = session_get_cookie_params();
    //setcookie(session_name(), $params["value"], time() + 42000,
     //   $params["path"], $params["domain"],
     //   $params["secure"], $params["httponly"]
    //);

    header("Location: index.php"); 

    exit; 
} else { $loginerrormessage = "Incorrect password!"; } } else { $loginerrormessage = "User not found!"; } 
    
    // Close the connection 
    $stmt->close(); $mysqli->close(); }

//////////////////////////

include 'header.php';
?>
<section class="page page--narrow">
    <div class="page-heading">
        <p class="eyebrow">Family chores</p>
        <h1>Welcome back</h1>
        <p>Sign in to see the chores for your family.</p>
    </div>
    <div class="surface-card form-card">
        <form action="/login.php" method="post">
            <div class="validation-message form-message" role="status" aria-live="polite"><?php echo h($loginerrormessage); ?></div>
            <div class="form-field">
                <label for="username">Email address</label>
                <input id="username" name="username" required type="text" autocomplete="username">
            </div>
            <div class="form-field">
                <label for="password">Password</label>
                <input id="password" name="password" required type="password" autocomplete="current-password">
            </div>
            <div class="form-actions">
                <input name="login" class="button button--primary" type="submit" value="Login">
            </div>
        </form>
    </div>
</section>
<?php

include 'footer.php';
?>
