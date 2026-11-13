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

echo '<section class="page page--approvals">';
echo '<div class="page-heading"><p class="eyebrow">Parent area</p><h1>Approvals</h1><p>Review completed chores and choose whether to approve or reject them.</p></div>';



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
    
        echo '<div class="todo-list"><form class="todo-form" action="submitapproval.php" method="post">' . csrf_field();
        echo '<div class="approval-list">';
    
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
    
            echo '<label class="approval-card">';
            echo '<input name="' . (int)$choredoneid . '" value="1" class="todo__state approval-card__check" type="checkbox" />';
            echo '<span class="approval-card__body">';
            echo '<strong class="approval-card__chore">' . h($name) . '</strong>';
            echo '<span class="approval-card__person">Completed by ' . h($doneby) . '</span>';
            echo '<time class="approval-card__date" datetime="' . h($whendone) . '">Completed ' . h($whendonestring) . '</time>';
            echo '</span>';
            echo '</label>';
    
    
        }
    
    
        echo '</div>';
        echo '<div class="approval-actions">';
        echo '<input name="approve" class="button button--primary" type="submit" value="Approve selected chores">';
        echo '<input name="reject" class="button button--danger" type="submit" value="Reject selected chores">';
        echo '</div>';
    
        //echo "</ul>";
        echo "</form></div>";
    
    } else {
    
        echo '<div class="empty-state"><div><span class="empty-state__icon" aria-hidden="true">✓</span><strong>You are all caught up</strong><p>There are no chores waiting for approval.</p></div></div>';
    
    }
$mysqli->close();
}
echo '</section>';
include 'footer.php';
?>
