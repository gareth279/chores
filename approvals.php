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

include 'header.php';



//select * from chores_history where peopleid = 1
$mysqli = new mysqli($dbhostname, $dbusername, $dbpassword, $dbdatabase);



// Check for connection errors

if ($mysqli->connect_error) {

    error_log('Chores database connection failed: ' . $mysqli->connect_error); http_response_code(500); exit('The request could not be completed. Please try again later.');

}

else{

    $peopleid = $_SESSION['personid'];
    $query = "select * from chores_history where approved = 'N' order by cid";



    $result = $mysqli->query($query);
    
    
    
    if ($result->num_rows > 0) {
    
        //echo "<h2>Chores List:</h2>";
    
        //echo "<ul>";
    
        echo '<div class="todo-list"><form action="submitapproval.php" method="post">' . csrf_field();
    
        while ($row = $result->fetch_assoc()) {
    
            $choredoneid = $row['cid'];
    
            $name = $row['chorename'];
            $doneby = $row['peoplename'];
            $whendone = $row['whendone'];
            $whendonestring = date_format(date_create($whendone), "d/m/Y");
            $val = $row['val'];
            //$val2 = money_format("%.2n", $number);
            $fmt = new NumberFormatter( 'en_US', NumberFormatter::CURRENCY );
            $val2 =  $fmt->formatCurrency($val, "NZD");
            $val2 = str_replace("NZ", "", $val2);
    
            //echo "<li>$name $$val";
    
            echo '<label class="todo">';
        echo '<input name="' . (int)$choredoneid . '" value="1" class="todo__state" type="checkbox" />';
        
        echo '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 200 25" class="todo__icon">';
        echo  '<use xlink:href="#todo__line" class="todo__line"></use>';
        echo  '<use xlink:href="#todo__box" class="todo__box"></use>';
        echo  '<use xlink:href="#todo__check" class="todo__check"></use>';
        echo  '<use xlink:href="#todo__circle" class="todo__circle"></use>';
        echo  '</svg>';
        echo '<div class="todo__text">' . h($whendonestring) . ' || ' . h($name) . ' (' . h($doneby) . ')</div></label>';
    
    
        }
    
    
        echo '<label class="todo">';
        echo "<input name=\"approve\" class=\"but\" type=\"submit\" value=\"Approve selected chores\">";   
        echo "</input></label>";
        echo '<label class="todo">';
        echo "<input name=\"reject\" class=\"but\" type=\"submit\" value=\"Reject selected chores\">";   
        echo "</input></label>";
    
        //echo "</ul>";
        echo "</form></div>";
    
    } else {
    
        echo "No chores found.";
    
    }
$mysqli->close();
}
include 'footer.php';
?>
