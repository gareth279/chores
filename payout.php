<?php
require_once 'global.php';
start_app_session();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
if (($_SESSION['parent'] ?? 'N') !== 'Y') {
    http_response_code(403);
    exit('You are not authorized to record payouts.');
}

$peoplepayid = filter_input(INPUT_POST, 'peopleid', FILTER_VALIDATE_INT);
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $peoplepayid = filter_input(INPUT_GET, 'peopleid', FILTER_VALIDATE_INT);
}
if (!$peoplepayid || $peoplepayid < 1) {
    http_response_code(400);
    exit('Invalid account.');
}

require_database_config();
$mysqli = new mysqli($dbhostname, $dbusername, $dbpassword, $dbdatabase);
if ($mysqli->connect_error) {
    error_log('Chores database connection failed: ' . $mysqli->connect_error);
    http_response_code(500);
    exit('The request could not be completed. Please try again later.');
}
$mysqli->set_charset('utf8mb4');

$stmt = $mysqli->prepare('SELECT peoplename, total FROM people_money WHERE peopleid = ?');
$stmt->bind_param('i', $peoplepayid);
$stmt->execute();
$stmt->bind_result($peoplename, $paymenttotal);
$accountExists = $stmt->fetch();
$stmt->close();

if (!$accountExists) {
    $mysqli->close();
    http_response_code(404);
    exit('Account not found.');
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $mysqli->close();
        http_response_code(403);
        exit('Invalid request token. Reload and try again.');
    }

    // Keep the existing ledger convention: a payout is recorded as the negative balance.
    $payoutValue = (float)$paymenttotal * -1;
    $stmt = $mysqli->prepare("INSERT INTO chores_done (peopleid, choreid, approved, val) VALUES (?, 0, 'Y', ?)");
    $stmt->bind_param('id', $peoplepayid, $payoutValue);
    if ($stmt->execute()) {
        unset($_SESSION['csrf_token']);
        $stmt->close();
        $mysqli->close();
        header('Location: history.php');
        exit;
    }
    error_log('Chores payout insert failed: ' . $stmt->error);
    $message = 'The payout could not be recorded. Please try again.';
    $stmt->close();
}
$mysqli->close();

include 'header.php';
?>
<section class="page page--narrow page--payout">
    <div class="page-heading">
        <p class="eyebrow">Parent area</p>
        <h1>Record payout</h1>
        <p>Review the amount before recording it.</p>
    </div>
    <div class="surface-card payout-summary">
        <p class="form-message" role="status" aria-live="polite"><?php echo h($message); ?></p>
        <p class="payout-summary__label">Payout amount</p>
        <p class="payout-summary__amount">$<?php echo h(number_format((float)$paymenttotal, 2)); ?></p>
        <p class="payout-summary__person">For <?php echo h($peoplename); ?></p>
        <form class="payout-summary__actions" method="post" action="payout.php">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="peopleid" value="<?php echo (int)$peoplepayid; ?>">
            <button type="submit">Record payout</button>
            <a class="button button--secondary" href="/history.php">Cancel</a>
        </form>
    </div>
</section>
<?php include 'footer.php'; ?>
