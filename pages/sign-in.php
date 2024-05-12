<!--
=========================================================
* Soft UI Dashboard - v1.0.7
=========================================================

* Product Page: https://www.creative-tim.com/product/soft-ui-dashboard
* Copyright 2023 Creative Tim (https://www.creative-tim.com)
* Licensed under MIT (https://www.creative-tim.com/license)
* Coded by Creative Tim

=========================================================

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
-->
<?php
// Start a session
session_start();

// Import DB connection and tracking
require "db-connection.php";
include 'tracking.php';

// Skip login if remember me has been used before
if(isset($_COOKIE['token'])) {
    // Delete expired tokens in DB
    $db->query("DELETE FROM userTokens WHERE expiry < UTC_TIMESTAMP")->fetch_all();

    // Selector is in the first 12 characters in the cookie, rest is a hashed validator
    $selector = substr($_COOKIE['token'], 0, 12);
        $validator = substr($_COOKIE['token'], 12); // Because stored in cookie, another value than id should be used to not leak number of users

    // Get user info and validator based on selector
    $dbToken = $db->execute_query("SELECT userName, userType, validator, expiry FROM userTokens JOIN user ON userTokens.userID LIKE user.userID WHERE selector = ?", [$selector])->fetch_assoc();

    // Check if validator expired and same as in DB
    if (time() < strtotime($dbToken['expiry']) and password_verify($validator, $dbToken['validator'])) {
        // Set user variable
        $_SESSION['username'] = $dbToken['userName'];
        $_SESSION['Logged-in'] = true;
        $_SESSION['userType'] = $dbToken['userType'];

        header("Location: dashboard.php");
        exit;
    } else {
        // Something is wrong with the cookie, delete it
        setcookie('token', "", 1);
        unset($validator);
        unset($selector);
    }
}

// Runs if sign in form is submitted
if (isset($_POST['login'])) {

    // Get data from form, set into variable
    $username = $_POST['username']; $password = $_POST['password'];

    // Get the password for the $username from DB
    $dbUser = $db->execute_query("SELECT password, userType, userID FROM user WHERE userName = ?", [$username])->fetch_assoc();

    if ($dbUser != null) { // If the query worked
        // Define variables from query
        $dbUserPassword = $dbUser['password'];
        $dbUserType = $dbUser['userType'];
        $dbUserID = $dbUser['userID'];

        // Check if query exists, if not show error
        if ($dbUserPassword != null && $dbUserType != null) {
            // Check if DB passwd and user input password matches
            if (password_verify($password, $dbUserPassword)) {
                // Prevent session fixation attack
                session_regenerate_id();

                // Set session variables
                $_SESSION['username'] = $username;
                $_SESSION['Logged-in'] = true;
                $_SESSION['userType'] = $dbUserType;

                // Remind me
                if ($_POST['rememberMe'] === 'on') { // Check if the remind-me checkbox is ticked
                    // Generate a "random" selector and validator
                    $selector = substr(bin2hex(openssl_random_pseudo_bytes(50)), 0, 12);
                    $validator = bin2hex(openssl_random_pseudo_bytes(30));
                    // A hashed version of validator to protect against DB-leak
                    $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);

                    // Set expiry time in seconds, then convert to "real"-date
                    $expiryEpoch = time() + 60 * 60 * 24 * 30;
                    $expiry = date('Y-m-d H:i:s', $expiryEpoch);

                    // Add the selector and hashed validator to DB
                    $db->execute_query("INSERT INTO userTokens(userID, selector, validator, expiry) VALUES ((?), (?), (?), (?))", [$dbUserID, $selector, $hashedValidator, $expiry]);
                    $token = $selector . $validator; // Concatenate selector and validator to be able to store it as one cookie
                    setcookie("token", $token, $expiryEpoch, secure: true); // Set the cookie
                }
                // Log-in successful, redirect to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                // Show alert
                echo "<script type='text/javascript'>alert('Login failed');</script>";
            }
        } else {
            echo "<script type='text/javascript'>alert('Login failed');</script>";
        }
    } else {
        echo "<script type='text/javascript'>alert('Login failed');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <title>
        Incident Report portal
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/soft-ui-dashboard.css?v=1.0.7" rel="stylesheet" />
    <!-- Nepcha Analytics (nepcha.com) -->
    <!-- Nepcha is an easy-to-use web analytics. No cookies and fully compliant with GDPR, CCPA and PECR. -->
    <script defer data-site="YOUR_DOMAIN_HERE" src="https://api.nepcha.com/js/nepcha-analytics.js"></script>
</head>

<body class="">
<main class="main-content  mt-0">
    <section>
        <div class="page-header min-vh-75">
            <div class="container">
                <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
                    <div class="card card-plain mt-4">
                        <div class="card-header pb-0 text-center bg-transparent">
                            <h3 class="font-weight-bolder text-primary text-gradient">Incident Portal</h3>
                            <p class="mb-0">Enter your email and password to sign in</p>
                        </div>
                        <div class="card-body">
                            <form method="POST" role="form">
                                <label>Username</label>
                                <div class="mb-3">
                                    <input name="username" type="text" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="username-addon" required>
                                </div>
                                <label>Password</label>
                                <div class="mb-3">
                                    <input name="password" type="password" class="form-control" placeholder="Password" aria-label="Password" aria-describedby="password-addon" required>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe" checked="">
                                    <label class="form-check-label" for="rememberMe">Remember me</label>
                                </div>
                                <div class="text-center">
                                    <button type="submit" name="login" class="btn bg-gradient-primary w-100 mt-4 mb-0">Sign in</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<!-- -------- START FOOTER 3 w/ COMPANY DESCRIPTION WITH LINKS & SOCIAL ICONS & COPYRIGHT ------- -->
<footer class="footer py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-4 mt-2">
                <a href="https://github.com/Oggeb1/incident-reporting-system-project" target="_blank" class="text-secondary">
                    <span class="text-lg fab fa-github"></span>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-8 mx-auto text-center mt-1">
                <p class="mb-0 text-secondary">
                    Copyright © <script>
                        document.write(new Date().getFullYear())
                    </script> Soft by Creative Tim.
                </p>
            </div>
        </div>
    </div>
</footer>
<!-- -------- END FOOTER 3 w/ COMPANY DESCRIPTION WITH LINKS & SOCIAL ICONS & COPYRIGHT ------- -->
<!--   Core JS Files   -->
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = {
            damping: '0.5'
        }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
</script>
<!-- GitHub buttons -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
<!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/soft-ui-dashboard.min.js?v=1.0.7"></script>
</body>

</html>