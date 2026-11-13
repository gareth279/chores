<?php
require_once 'global.php';
start_app_session();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

include 'header.php';

$mysqli = new mysqli($dbhostname, $dbusername, $dbpassword, $dbdatabase);
if ($mysqli->connect_error) {
    error_log('Chores database connection failed: ' . $mysqli->connect_error);
    http_response_code(500);
    exit('The request could not be completed. Please try again later.');
}

function chore_money_display($value): string
{
    $formatter = new NumberFormatter('en_US', NumberFormatter::CURRENCY);
    return str_replace('NZ', '', $formatter->formatCurrency((float)$value, 'NZD'));
}

function chore_date_display($value): string
{
    $date = date_create((string)$value);
    return $date ? date_format($date, 'd M Y, g:i a') : (string)$value;
}
?>
<section class="page page--history">
    <div class="page-heading">
        <p class="eyebrow">Your family ledger</p>
        <h1>History &amp; balances</h1>
        <p>See current balances and the chores behind them.</p>
    </div>
    <div class="history-stack">
        <section class="history-section" aria-labelledby="balances-heading">
            <div class="history-section__heading">
                <h2 id="balances-heading">Current balances</h2>
                <p>Approved chore values, less any recorded payouts.</p>
            </div>
            <?php
            $stmt = $mysqli->prepare("select * from people_money order by peoplename");
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0):
            ?>
            <div class="table-scroll">
                <table class="history-table history-table--balances">
                    <thead><tr><th scope="col">Person</th><th scope="col">Balance</th><?php if (($parent ?? 'N') === 'Y'): ?><th scope="col">Action</th><?php endif; ?></tr></thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()):
                        $peoplepayid = $row['peopleid'];
                        $peoplename = $row['peoplename'];
                        $total = chore_money_display($row['total']);
                    ?>
                        <tr>
                            <td data-label="Person" class="history-table__person"><?php echo h($peoplename); ?></td>
                            <td data-label="Balance" class="history-table__amount"><?php echo h($total); ?></td>
                            <?php if (($parent ?? 'N') === 'Y'): ?>
                            <td data-label="Action" class="history-table__action"><a class="button button--secondary button--small" href="/payout.php?peopleid=<?php echo (int)$peoplepayid; ?>">Record payout</a></td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="history-empty">No balances to show yet.</p>
            <?php endif; $stmt->close(); ?>
        </section>

        <section class="history-section" aria-labelledby="pending-heading">
            <div class="history-section__heading">
                <h2 id="pending-heading">Awaiting approval</h2>
                <p>Chores submitted and waiting for a parent.</p>
            </div>
            <?php
            $stmt = $mysqli->prepare("select * from chores_history where approved = 'N'");
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0):
            ?>
            <div class="table-scroll">
                <table class="history-table history-table--activity">
                    <thead><tr><th scope="col">Person</th><th scope="col">Chore</th><th scope="col">Date</th><th scope="col">Value</th></tr></thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()):
                        $doneby = $row['peoplename'];
                        $chorename = $row['chorename'];
                        $whendone = $row['whendone'];
                        $val = chore_money_display($row['val']);
                    ?>
                        <tr>
                            <td data-label="Person" class="history-table__person"><?php echo h($doneby); ?></td>
                            <td data-label="Chore"><?php echo h($chorename); ?></td>
                            <td data-label="Date" class="history-table__date"><time datetime="<?php echo h($whendone); ?>"><?php echo h(chore_date_display($whendone)); ?></time></td>
                            <td data-label="Value" class="history-table__amount"><?php echo h($val); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="history-empty">No chores are waiting for approval.</p>
            <?php endif; $stmt->close(); ?>
        </section>

        <section class="history-section" aria-labelledby="completed-heading">
            <div class="history-section__heading">
                <h2 id="completed-heading">Completed chores</h2>
                <p>Approved chores and recorded payouts.</p>
            </div>
            <?php
            $stmt = $mysqli->prepare("select * from chores_history where approved = 'Y' order by whendone desc");
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0):
            ?>
            <div class="table-scroll">
                <table class="history-table history-table--activity">
                    <thead><tr><th scope="col">Person</th><th scope="col">Chore</th><th scope="col">Date</th><th scope="col">Value</th></tr></thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()):
                        $doneby = $row['peoplename'];
                        $chorename = $row['chorename'];
                        $whendone = $row['whendone'];
                        $val = chore_money_display($row['val']);
                    ?>
                        <tr>
                            <td data-label="Person" class="history-table__person"><?php echo h($doneby); ?></td>
                            <td data-label="Chore"><?php echo h($chorename); ?></td>
                            <td data-label="Date" class="history-table__date"><time datetime="<?php echo h($whendone); ?>"><?php echo h(chore_date_display($whendone)); ?></time></td>
                            <td data-label="Value" class="history-table__amount"><?php echo h($val); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="history-empty">No completed chores yet.</p>
            <?php endif; $stmt->close(); ?>
        </section>
    </div>
</section>
<?php
$mysqli->close();
include 'footer.php';
?>
