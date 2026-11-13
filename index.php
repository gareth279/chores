<?php



require_once 'global.php';
start_app_session();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
include 'header.php';

echo '<section class="page page--chores">';
echo '<div class="page-heading"><p class="eyebrow">Ready when you are</p><h1>Choose a chore</h1><p>Select the chores you have completed.</p></div>';

// Create a MySQLi connection

$mysqli = new mysqli($dbhostname, $dbusername, $dbpassword, $dbdatabase);



// Check for connection errors

if ($mysqli->connect_error) {

    error_log('Chores database connection failed: ' . $mysqli->connect_error); http_response_code(500); exit('The request could not be completed. Please try again later.');

}

else{

    $query = "SELECT * FROM chores where choreid > 0";



$result = $mysqli->query($query);



if ($result->num_rows > 0) {

    //echo "<h2>Chores List:</h2>";

    //echo "<ul>";

    echo '<div class="todo-list"><form class="todo-form" action="submitchores.php" method="post">' . csrf_field();
    echo '<div class="todo-list__items">';

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
        echo '<input name="' . (int)$choreid . '" value="1" class="todo__state" type="checkbox" />';
        echo '<span class="todo__content"><span class="todo__text">' . h($name) . '</span><span class="todo__value">' . h($val2) . '</span></span>';
        echo '</label>';


    }


    echo '</div>';
    echo '<div class="todo__actions"><input class="button button--primary" type="submit" value="Tick off chores"></div>';

    //echo "</ul>";
    echo "</form></div>";

} else {

    echo '<div class="empty-state"><div><span class="empty-state__icon" aria-hidden="true">✓</span><strong>No chores available right now</strong><p>Check back later for the next chore.</p></div></div>';

}

}

echo '</section>';

include 'footer.php';
?>
