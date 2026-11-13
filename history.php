<?php

include 'global.php';

// Initialize the session
start_app_session();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
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

    $stmt = $mysqli->prepare("select * from people_money order by peoplename"); 
    $stmt->execute();     
    $result = $stmt->get_result();
    echo "<p>Chore Balances</p><table>";
    while ($row = $result->fetch_assoc()) {
        $peoplepayid = $row["peopleid"];
        $peoplename = $row["peoplename"];
        $total = $row["total"];
        $fmt = new NumberFormatter( 'en_US', NumberFormatter::CURRENCY );
        $total2 =  $fmt->formatCurrency($total, "NZD");
        $total2 = str_replace("NZ", "", $total2);
        echo "<tr>";        
        echo '<td>' . h($peoplename) . '</td>';
        echo '<td>' . h($total2) . '</td>';
        if(isset($parent)){ if($parent == "Y"){ echo '<td><a href="payout.php?peopleid=' . (int)$peoplepayid . '">Payout</a></td>'; }}
        echo "</tr>";
    }
    echo "</table>";

    $stmt = $mysqli->prepare("select * from chores_history where approved = 'N'"); 
    $peopleid = $_SESSION['personid'];
    //$stmt->bind_param("i", $peopleid); 
    // Execute the SQL statement 
    $stmt->execute();     
    $result = $stmt->get_result();
    echo "<p>Awaiting approval</p><table>";
    while ($row = $result->fetch_assoc()) {
        $parent = $row["parent"];
        $chorename = $row["chorename"];
        $doneby = $row["peoplename"];
        $val = $row["val"];
        $fmt = new NumberFormatter( 'en_US', NumberFormatter::CURRENCY );
        $val2 =  $fmt->formatCurrency($val, "NZD");
        $val2 = str_replace("NZ", "", $val2);
        $whendone = $row["whendone"];
        $approved = $row["approved"];
        echo "<tr>";        
        echo '<td>' . h($doneby) . '</td>';
        echo '<td>' . h($chorename) . '</td>';
        echo '<td>' . h($whendone) . '</td>';
        echo '<td>' . h($val2) . '</td>';
        echo "</tr>";
    }
    echo "</table>";


    $stmt = $mysqli->prepare("select * from chores_history where approved = 'Y' order by whendone desc"); 
    $peopleid = $_SESSION['personid'];
    //$stmt->bind_param("i", $peopleid); 
    // Execute the SQL statement 
    $stmt->execute();     
    $result = $stmt->get_result();
    echo "<p>Completed Chores</p><table>";
    while ($row = $result->fetch_assoc()) {
        $parent = $row["parent"];
        $chorename = $row["chorename"];
        $doneby = $row["peoplename"];
        $val = $row["val"];
        $fmt = new NumberFormatter( 'en_US', NumberFormatter::CURRENCY );
        $val2 =  $fmt->formatCurrency($val, "NZD");
        $val2 = str_replace("NZ", "", $val2);
        $whendone = $row["whendone"];
        $approved = $row["approved"];
        echo "<tr>";
        echo '<td>' . h($doneby) . '</td>';
        echo '<td>' . h($chorename) . '</td>';
        echo '<td>' . h($whendone) . '</td>';
        echo '<td>' . h($val2) . '</td>';
        echo "</tr>";
    }
    echo "</table>";

    /*
    $stmt->store_result(); 
    $stmt->next_result();
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
*/
$stmt->close();
$mysqli->close();
}
include 'footer.php';
?>
