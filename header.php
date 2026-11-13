<!DOCTYPE html>
<html>
<head>
    <title>Family Chores Application</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#0FC0F5">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="icon" href="/favicon.ico">
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
          navigator.serviceWorker.register('/service-worker.js');
        });
      }
    </script>
    <link rel="stylesheet" href="chores.css">
</head>
<body>
    <header>
        <h1>Chore Tracker</h1>
        <?php
            $personname = "";
            if(isset($_SESSION['personname']) && isset($_SESSION['username'])){
                $personname = $_SESSION['personname'];
                $emailfromfrom = $_SESSION['username'];
                $parent = $_SESSION['parent'];
                //echo "<p>$personname ($emailfromfrom)</p>";
            }
        ?>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php if(isset($parent)){ if($parent == "Y"){ echo "<li><a href=\"approvals.php\">Approvals</a></li>"; }} ?>
                <li><a href="history.php">History</a></li>                
                <li><a href="changepassword.php">Change Password</a></li>   
                <li><form action="logout.php" method="post"><?php echo csrf_field(); ?><button type="submit">Logout (<?php echo h($personname); ?>)</button></form></li>
                <!-- Add other navigation links as needed -->
            </ul>
        </nav>
    </header>
    <main>


    <svg viewBox="0 0 0 0" style="position: absolute; z-index: -1; opacity: 0;">
  <defs>
    <linearGradient id="boxGradient" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="25" y2="25">
      <stop offset="0%"   stop-color="#27FDC7"/>
      <stop offset="100%" stop-color="#0FC0F5"/>
    </linearGradient>

    <linearGradient id="lineGradient">
      <stop offset="0%"    stop-color="#0FC0F5"/>
      <stop offset="100%"  stop-color="#27FDC7"/>
    </linearGradient>

    <path id="todo__line" stroke="url(#lineGradient)" d="M21 12.3h168v0.1z"></path>
    <path id="todo__box" stroke="url(#boxGradient)" d="M21 12.7v5c0 1.3-1 2.3-2.3 2.3H8.3C7 20 6 19 6 17.7V7.3C6 6 7 5 8.3 5h10.4C20 5 21 6 21 7.3v5.4"></path>
    <path id="todo__check" stroke="url(#boxGradient)" d="M10 13l2 2 5-5"></path>
    <circle id="todo__circle" cx="13.5" cy="12.5" r="10"></circle>
  </defs>
</svg>
