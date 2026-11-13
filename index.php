<?php



// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
include 'global.php';
include 'header.php';

// Create a MySQLi connection

$mysqli = new mysqli($dbhostname, $dbusername, $dbpassword, $dbdatabase);



// Check for connection errors

if ($mysqli->connect_error) {

    die("Connection failed: " . $mysqli->connect_error);

}

else{

    $query = "SELECT * FROM chores where choreid > 0";



$result = $mysqli->query($query);



if ($result->num_rows > 0) {

    //echo "<h2>Chores List:</h2>";

    //echo "<ul>";

    echo '<div class="todo-list"><form action="submitchores.php" method="post">';

    while ($row = $result->fetch_assoc()) {

        $choreid = $row['choreid'];

        $name = $row['name'];

        $val = $row['val'];
        //$val2 = money_format("%.2n", $number);
        $fmt = new NumberFormatter( 'en_US', NumberFormatter::CURRENCY );
        $val2 =  $fmt->formatCurrency($val, "NZD");
        $val2 = str_replace("NZ", "", $val2);

        //echo "<li>$name $$val";

        echo '<label class="todo">';
    echo "<input name=\"$choreid\" class=\"todo__state\" type=\"checkbox\" />";
    
    echo '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 200 25" class="todo__icon">';
    echo  '<use xlink:href="#todo__line" class="todo__line"></use>';
    echo  '<use xlink:href="#todo__box" class="todo__box"></use>';
    echo  '<use xlink:href="#todo__check" class="todo__check"></use>';
    echo  '<use xlink:href="#todo__circle" class="todo__circle"></use>';
    echo  '</svg>';
    echo "<div class=\"todo__text\">$name ($val2)</div></label>";


    }


    echo '<label class="todo">';
    echo "<input class=\"but\" type=\"submit\" value=\"Tick off chores\">";   
    echo "</input></label>";

    //echo "</ul>";
    echo "</form></div>";

} else {

    echo "No chores found.";

}

}

include 'footer.php';
?>