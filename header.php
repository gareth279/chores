<?php
$personname = (string)($_SESSION['personname'] ?? '');
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$parent = (string)($_SESSION['parent'] ?? 'N');
$activeRoute = basename((string)($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
$isChoresPage = $activeRoute === 'index.php';
$isApprovalsPage = $activeRoute === 'approvals.php';
$isHistoryPage = in_array($activeRoute, ['history.php', 'payout.php'], true);
?>
<!DOCTYPE html>
<html lang="en-NZ">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Chores | Family Chores Application</title>
    <meta name="theme-color" content="#147c72">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Chores">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-touch-icon.png">
    <link rel="stylesheet" href="/chores.css">
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
          navigator.serviceWorker.register('/service-worker.js');
        });
      }
    </script>
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <header class="app-header">
        <div class="app-header__inner">
            <a class="brand" href="/index.php" aria-label="Chores home">
                <span class="brand__mark" aria-hidden="true">✓</span>
                <span>Chores</span>
            </a>
            <?php if ($isLoggedIn): ?>
            <nav class="primary-nav" aria-label="Main navigation">
                <ul>
                    <li>
                        <a class="nav-link" href="/index.php"<?php echo $isChoresPage ? ' aria-current="page"' : ''; ?>>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4H19v16H5.5A1.5 1.5 0 0 1 4 18.5z"/><path d="M4 17h15M8 8h7M8 11h7"/></svg>
                            <span>Chores</span>
                        </a>
                    </li>
                    <?php if ($parent === 'Y'): ?>
                    <li>
                        <a class="nav-link" href="/approvals.php"<?php echo $isApprovalsPage ? ' aria-current="page"' : ''; ?>>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M7 4.5h10A1.5 1.5 0 0 1 18.5 6v14l-6.5-3.5L5.5 20V6A1.5 1.5 0 0 1 7 4.5Z"/><path d="m9 10 2 2 4-4"/></svg>
                            <span>Approvals</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a class="nav-link" href="/history.php"<?php echo $isHistoryPage ? ' aria-current="page"' : ''; ?>>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 5.5h16M4 10h16M4 14.5h10M4 19h8"/><circle cx="18" cy="17.5" r="3.5"/><path d="M18 15.5v2l1.2.8"/></svg>
                            <span>History</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <details class="account-menu">
                <summary><span class="account-menu__avatar" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.25"/><path d="M5.5 20a6.5 6.5 0 0 1 13 0"/></svg></span><span>Account</span></summary>
                <div class="account-menu__panel">
                    <a href="/changepassword.php">Change password</a>
                    <form action="/logout.php" method="post">
                        <?php echo csrf_field(); ?>
                        <button type="submit">Log out<?php echo $personname !== '' ? ' (' . h($personname) . ')' : ''; ?></button>
                    </form>
                </div>
            </details>
            <?php endif; ?>
        </div>
    </header>
    <main id="main-content">
