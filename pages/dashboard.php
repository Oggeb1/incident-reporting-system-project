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
<!DOCTYPE html>
<html lang="en">

<head>
    <?php


    require "db-connection.php";

// Counts the current amount of tickets that are actively 'Pending' (not old tickets that have changed status)
    $curTickets = $db->execute_query("SELECT count(ticketID) FROM ticket
WHERE incidentID NOT IN (SELECT incidentID FROM ticket WHERE ticketStatus NOT LIKE 'Pending')")->fetch_assoc();

    // Counts the current amount of tickets that are actively 'In Progress (not old tickets that have changed status)

    $progressTickets = $db->execute_query("SELECT count(ticketID) FROM (
         SELECT ticket.ticketID
         FROM ticket
         WHERE ticketStatus LIKE 'In progress'
           AND ticket.incidentID NOT IN (SELECT incidentID FROM ticket WHERE ticketStatus LIKE 'Resolved')
     ) AS sub")->fetch_assoc();

    // Counts the current amount of tickets that are actively 'Resolved' (not old tickets that have changed status)
    $closedTickets = $db->execute_query("SELECT count(ticket.ticketID) FROM ticket
JOIN incident ON ticket.incidentID = incident.incidentID
WHERE ticket.ticketStatus LIKE 'Resolved' AND incident.isDeleted NOT LIKE 1")->fetch_assoc();
    $ticketTimestamp = $db->execute_query("SELECT TIMEDIFF(UTC_TIMESTAMP(), MAX(timestamp )) AS time_difference FROM ticket WHERE ticketStatus = 'Pending';")->fetch_assoc();
    $tickTimestamp = implode($ticketTimestamp);
    $userinfo = $db->execute_query("SELECT userName, userID FROM user ORDER BY RAND()");

 //fetches all user that have the usertype 'Administrator' or 'Responder'
    $users = $db->query("SELECT userID,userName,email,firstName,lastName,userType FROM user where userType = 'Responder' OR userType = 'Administrator'")->fetch_all();

 //Count the daily amount of Tickets that has been changed to status 'Resolved'
    $dailyCompletedTickets = $db->query("SELECT user.userName, COUNT(*) as userCount
FROM user
         JOIN ticket ON user.userID = ticket.responderID
WHERE ticket.ticketStatus LIKE 'Resolved'
  AND DATE(ticket.timestamp) = UTC_DATE
GROUP BY user.userName;")->fetch_all();

    $countUsers = $db->query("SELECT user.userID, user.userName
    FROM user JOIN incident  ON user.userID = incident.reporterID JOIN ticket ON incident.incidentID = ticket.incidentID
    WHERE user.userType = 'Responder'
    AND (
    (ticket.ticketStatus = 'Resolved' AND ticket.timestamp >= NOW() - INTERVAL 1 DAY)
        OR (ticket.ticketStatus = 'Pending' AND ticket.timestamp >= NOW() - INTERVAL 1 DAY));")->fetch_all();


   //The four queries below counts the amount of 'Low','Medium','High','Critical' incidents that have been recieved in the last 30 days
    $countSeverity = $db->query("SELECT incidentSeverity, timestamp
    FROM incident
    WHERE timestamp BETWEEN NOW() - INTERVAL 30 DAY AND NOW() AND incidentSeverity = 'Low';")->fetch_all();

    $countSeverity2 = $db->query("SELECT incidentSeverity, timestamp
    FROM incident
    WHERE timestamp BETWEEN NOW() - INTERVAL 30 DAY AND NOW() AND incidentSeverity = 'Medium';")->fetch_all();

    $countSeverity3 = $db->query("SELECT incidentSeverity, timestamp
    FROM incident
    WHERE timestamp BETWEEN NOW() - INTERVAL 30 DAY AND NOW() AND incidentSeverity = 'High';")->fetch_all();

    $countSeverity4 = $db->query("SELECT incidentSeverity, timestamp
    FROM incident
    WHERE timestamp BETWEEN NOW() - INTERVAL 30 DAY AND NOW() AND incidentSeverity = 'Critical';")->fetch_all();

    //Counts the amount of tickets that have been received between now and 7 days ago
        $countTickets = $db->query("SELECT ticketID, timestamp  FROM ticket WHERE timestamp BETWEEN NOW() - INTERVAL 7 DAY AND NOW();
    ")->fetch_all();
//Counts the amount of tickets that have been received between 7 days ago and 14 days ago
    $countTickets2 = $db->query ("SELECT ticketID, timestamp
    FROM ticket
    WHERE timestamp BETWEEN NOW() - INTERVAL 14 DAY AND NOW() - INTERVAL 8 DAY
    ")->fetch_all();
    //Counts the amount of tickets that have been received between 15 days ago and 23 days ag
    $countTickets3 = $db->query("SELECT ticketID, timestamp
    FROM ticket
    WHERE timestamp BETWEEN NOW() - INTERVAL 22 DAY AND NOW() - INTERVAL 15 DAY
    ")->fetch_all();
    //Counts the amount of tickets that have been received between 23 days ago and 30 days ago
    $countTickets4 = $db->query("SELECT ticketID, timestamp
    FROM ticket
    WHERE timestamp BETWEEN NOW() - INTERVAL 30 DAY AND NOW() - INTERVAL 23 DAY;
    ")->fetch_all();

  //fetches all 'Resolved' tickets between the current date and 7 days ago
    $resolvedTickets = $db->query("SELECT ticketID, timestamp, COUNT(ticketStatus)
    FROM ticket
    WHERE timestamp BETWEEN NOW() - INTERVAL 7 DAY AND NOW() AND ticketStatus = 'Resolved';
    ")->fetch_all();
    //fetches all 'Resolved' tickets between the 8 days ago and 14 days ago
    $resolvedTickets2 = $db->query("SELECT ticketID, timestamp, ticketStatus
    FROM ticket
    WHERE timestamp BETWEEN NOW() - INTERVAL 14 DAY AND NOW() - INTERVAL 8 DAY AND ticketStatus = 'Resolved';
    ")->fetch_all();
    //fetches all 'Resolved' tickets between the 15 days ago and 22 days ago
    $resolvedTickets3 = $db->query("SELECT ticketID, timestamp, ticketStatus
    FROM ticket
    WHERE timestamp BETWEEN NOW() - INTERVAL 22 DAY AND NOW() - INTERVAL 15 DAY AND ticketStatus = 'Resolved';
    ")->fetch_all();
    //fetches all 'Resolved' tickets between the 23 days ago and 30 days ago
    $resolvedTickets4 = $db->query("SELECT ticketID, timestamp, ticketStatus
    FROM ticket
    WHERE timestamp BETWEEN NOW() - INTERVAL 30 DAY AND NOW() - INTERVAL 23 DAY AND ticketStatus = 'Resolved';
    ")->fetch_all();

    //Counts the total amount of tickets
   $allTickets = $db->query("SELECT COUNT(ticketID) FROM ticket")->fetch_assoc();
    //Counts the total amount of logID's
   $countTracker = $db->query("SELECT COUNT(logID) FROM log")->fetch_assoc();
    //Counts the total amount of users
   $countUser = $db->query("SELECT COUNT(userID) FROM user")->fetch_assoc();
   //Counts the total amount of distinct IP connections
   $countIP = $db->query("SELECT COUNT(DISTINCT ip) FROM log")->fetch_assoc();


    ?>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <title>
        Soft UI Dashboard by Creative Tim
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
    <!-- Nepcha is a easy-to-use web analytics. No cookies and fully compliant with GDPR, CCPA and PECR. -->
    <script defer data-site="YOUR_DOMAIN_HERE" src="https://api.nepcha.com/js/nepcha-analytics.js"></script>
</head>

<body class="g-sidenav-show  bg-gray-100">

<?php $pageName = 'Dashboard'; require 'sidebar.php';?>


<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg "
    <!-- Navbar -->
    <?php
    // ONly visible for administrator or responder
    if ($_SESSION["userType"] === 'Administrator' || ($_SESSION["userType"] === 'Responder')) {
        ?>,
          <div class="container-fluid py-4">
            <div class="row">
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-capitalize font-weight-bold">Current pending tickets</p>
                                        <h5 class="font-weight-bolder mb-0"> <?= $curTickets['count(ticketID)'] ?></h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <a href="tickets.php">
                                    <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                        <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-capitalize font-weight-bold">Current ongoing tickets</p>
                                        <h5 class="font-weight-bolder mb-0"><?= ($progressTickets ["count(ticketID)"])?></h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <a href="tickets.php">
                                    <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                        <i class="ni ni-world text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-capitalize font-weight-bold">Resolved <br>tickets</p>
                                        <h5 class="font-weight-bolder mb-0"><?= ($closedTickets ["count(ticket.ticketID)"])?></h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <a href="tickets.php">
                                    <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                        <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-capitalize font-weight-bold">Longest ticket pending time</p>
                                        <h5 class="font-weight-bolder mb-0"><?= $tickTimestamp ?></h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <a href="tickets.php">
                                    <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                        <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
   <?php
    }
    ?>
    <?php
    if ($_SESSION["userType"] === 'Administrator' || ($_SESSION["userType"] === 'Responder' || ($_SESSION["userType"] === 'Reporter'))) {
    ?>

    <div class="row mt-4">
            <div class="col-lg-5">
                <div class="card h-100 p-3">
                    <div class="overflow-hidden position-relative border-radius-lg bg-cover h-100" style="background-image: url('../assets/img/ivancik.jpg');">
                        <span class="mask bg-gradient-dark"></span>
                        <div class="card-body position-relative z-index-1 d-flex flex-column h-100 p-3">
                            <h5 class="text-white font-weight-bolder mb-4 pt-2">My Tickets</h5>
                            <p class="text-white">Look at my tickets.</p>
                            <a class="text-white text-sm font-weight-bold mb-0 icon-move-right mt-auto" href="javascript:">
                                <a class="text-white" href="tickets.php">Click here</a>
                                <i class="fas fa-arrow-right text-sm ms-1" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card h-100 p-3">
                    <div class="overflow-hidden position-relative border-radius-lg bg-cover h-100" style="background-image: url('../assets/img/ivancik.jpg');">
                        <span class="mask bg-gradient-dark"></span>
                        <div class="card-body position-relative z-index-1 d-flex flex-column h-100 p-3">
                            <h5 class="text-white font-weight-bolder mb-4 pt-2">Open Tickets</h5>
                            <p class="text-white">Look at open tickets.</p>
                            <a class="text-white text-sm font-weight-bold mb-0 icon-move-right mt-auto" href="javascript:">
                                <a class="text-white" href="tickets.php">Click here</a>
                                <i class="fas fa-arrow-right text-sm ms-1" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card h-100 p-3">
                    <div class="overflow-hidden position-relative border-radius-lg bg-cover h-100" style="background-image: url('../assets/img/ivancik.jpg');">
                        <span class="mask bg-gradient-dark"></span>
                        <div class="card-body position-relative z-index-1 d-flex flex-column h-100 p-3">
                            <h5 class="text-white font-weight-bolder mb-4 pt-2">Closed Tickets</h5>
                            <p class="text-white">Look at closed tickets.</p>
                            <a class="text-white text-sm font-weight-bold mb-0 icon-move-right mt-auto" href="javascript:">
                                <a class="text-white" href="tickets.php">Click here</a>
                                <i class="fas fa-arrow-right text-sm ms-1" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
        </div>
        <?php

    if ($_SESSION["userType"] === 'Administrator' || ($_SESSION["userType"] === 'Responder')) {
    ?>

        <div class="row mt-4">
            <div class="col-lg-13 mb-lg-0 mb-4">
                <div class="card h-100 z-index-2">
                    <div class="card-body p-3">
                        <h6 class="text-sm">
                            Incidents by Severity Last 30 Days
                        </h6>
                        <div class="bg-gradient-dark border-radius-lg py-3 pe-1 mb-3">
                            <div class="chart">
                                <canvas id="chart-bars" class="chart-canvas" height="170"></canvas>
                            </div>
                        </div>
                        <div class="container border-radius-lg">
                            <div class="row">
                                <div class="col-3 py-3 ps-0">
                                    <div class="d-flex mb-2">
                                        <div class="icon icon-shape icon-xxs shadow border-radius-sm bg-gradient-primary text-center me-2 d-flex align-items-center justify-content-center">
                                            <svg width="10px" height="10px" viewBox="0 0 40 44" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                <title>document</title>
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <g transform="translate(-1870.000000, -591.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                                        <g transform="translate(1716.000000, 291.000000)">
                                                            <g transform="translate(154.000000, 300.000000)">
                                                                <path class="color-background" d="M40,40 L36.3636364,40 L36.3636364,3.63636364 L5.45454545,3.63636364 L5.45454545,0 L38.1818182,0 C39.1854545,0 40,0.814545455 40,1.81818182 L40,40 Z" opacity="0.603585379"></path>
                                                                <path class="color-background" d="M30.9090909,7.27272727 L1.81818182,7.27272727 C0.814545455,7.27272727 0,8.08727273 0,9.09090909 L0,41.8181818 C0,42.8218182 0.814545455,43.6363636 1.81818182,43.6363636 L30.9090909,43.6363636 C31.9127273,43.6363636 32.7272727,42.8218182 32.7272727,41.8181818 L32.7272727,9.09090909 C32.7272727,8.08727273 31.9127273,7.27272727 30.9090909,7.27272727 Z M18.1818182,34.5454545 L7.27272727,34.5454545 L7.27272727,30.9090909 L18.1818182,30.9090909 L18.1818182,34.5454545 Z M25.4545455,27.2727273 L7.27272727,27.2727273 L7.27272727,23.6363636 L25.4545455,23.6363636 L25.4545455,27.2727273 Z M25.4545455,20 L7.27272727,20 L7.27272727,16.3636364 L25.4545455,16.3636364 L25.4545455,20 Z"></path>
                                                            </g>
                                                        </g>
                                                    </g>
                                                </g>
                                            </svg>
                                        </div>
                                        <a href="user-management.php"  <p class="text-xs mt-1 mb-0 font-weight-bold">Total users</p> </a>
                                    </div>
                                    <h4 class="font-weight-bolder"><?= ($countUser ["COUNT(userID)"]) ?> </h4>
                                    <div class="progress w-75">
                                        <div class="progress-bar bg-dark w-60" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="col-3 py-3 ps-0">
                                    <div class="d-flex mb-2">
                                        <div class="icon icon-shape icon-xxs shadow border-radius-sm bg-gradient-info text-center me-2 d-flex align-items-center justify-content-center">
                                            <svg width="10px" height="10px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                <title>spaceship</title>
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <g transform="translate(-1720.000000, -592.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                                        <g transform="translate(1716.000000, 291.000000)">
                                                            <g transform="translate(4.000000, 301.000000)">
                                                                <path class="color-background" d="M39.3,0.706666667 C38.9660984,0.370464027 38.5048767,0.192278529 38.0316667,0.216666667 C14.6516667,1.43666667 6.015,22.2633333 5.93166667,22.4733333 C5.68236407,23.0926189 5.82664679,23.8009159 6.29833333,24.2733333 L15.7266667,33.7016667 C16.2013871,34.1756798 16.9140329,34.3188658 17.535,34.065 C17.7433333,33.98 38.4583333,25.2466667 39.7816667,1.97666667 C39.8087196,1.50414529 39.6335979,1.04240574 39.3,0.706666667 Z M25.69,19.0233333 C24.7367525,19.9768687 23.3029475,20.2622391 22.0572426,19.7463614 C20.8115377,19.2304837 19.9992882,18.0149658 19.9992882,16.6666667 C19.9992882,15.3183676 20.8115377,14.1028496 22.0572426,13.5869719 C23.3029475,13.0710943 24.7367525,13.3564646 25.69,14.31 C26.9912731,15.6116662 26.9912731,17.7216672 25.69,19.0233333 L25.69,19.0233333 Z"></path>
                                                                <path class="color-background" d="M1.855,31.4066667 C3.05106558,30.2024182 4.79973884,29.7296005 6.43969145,30.1670277 C8.07964407,30.6044549 9.36054508,31.8853559 9.7979723,33.5253085 C10.2353995,35.1652612 9.76258177,36.9139344 8.55833333,38.11 C6.70666667,39.9616667 0,40 0,40 C0,40 0,33.2566667 1.855,31.4066667 Z"></path>
                                                                <path class="color-background" d="M17.2616667,3.90166667 C12.4943643,3.07192755 7.62174065,4.61673894 4.20333333,8.04166667 C3.31200265,8.94126033 2.53706177,9.94913142 1.89666667,11.0416667 C1.5109569,11.6966059 1.61721591,12.5295394 2.155,13.0666667 L5.47,16.3833333 C8.55036617,11.4946947 12.5559074,7.25476565 17.2616667,3.90166667 L17.2616667,3.90166667 Z" opacity="0.598539807"></path>
                                                                <path class="color-background" d="M36.0983333,22.7383333 C36.9280725,27.5056357 35.3832611,32.3782594 31.9583333,35.7966667 C31.0587397,36.6879974 30.0508686,37.4629382 28.9583333,38.1033333 C28.3033941,38.4890431 27.4704606,38.3827841 26.9333333,37.845 L23.6166667,34.53 C28.5053053,31.4496338 32.7452344,27.4440926 36.0983333,22.7383333 L36.0983333,22.7383333 Z" opacity="0.598539807"></path>
                                                            </g>
                                                        </g>
                                                    </g>
                                                </g>
                                            </svg>
                                        </div>
                                        <a href="tickets.php"  <p class="text-xs mt-1 mb-0 font-weight-bold">  Total Tickets</p> </a>
                                    </div>
                                    <h4 class="font-weight-bolder"><?= ($allTickets["COUNT(ticketID)"])?> </h4>
                                    <div class="progress w-75">
                                        <div class="progress-bar bg-dark w-90" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="col-3 py-3 ps-0">
                                    <div class="d-flex mb-2">
                                        <div class="icon icon-shape icon-xxs shadow border-radius-sm bg-gradient-info text-center me-2 d-flex align-items-center justify-content-center">
                                            <svg width="10px" height="10px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                <title>spaceship</title>
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <g transform="translate(-1720.000000, -592.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                                        <g transform="translate(1716.000000, 291.000000)">
                                                            <g transform="translate(4.000000, 301.000000)">
                                                                <path class="color-background" d="M39.3,0.706666667 C38.9660984,0.370464027 38.5048767,0.192278529 38.0316667,0.216666667 C14.6516667,1.43666667 6.015,22.2633333 5.93166667,22.4733333 C5.68236407,23.0926189 5.82664679,23.8009159 6.29833333,24.2733333 L15.7266667,33.7016667 C16.2013871,34.1756798 16.9140329,34.3188658 17.535,34.065 C17.7433333,33.98 38.4583333,25.2466667 39.7816667,1.97666667 C39.8087196,1.50414529 39.6335979,1.04240574 39.3,0.706666667 Z M25.69,19.0233333 C24.7367525,19.9768687 23.3029475,20.2622391 22.0572426,19.7463614 C20.8115377,19.2304837 19.9992882,18.0149658 19.9992882,16.6666667 C19.9992882,15.3183676 20.8115377,14.1028496 22.0572426,13.5869719 C23.3029475,13.0710943 24.7367525,13.3564646 25.69,14.31 C26.9912731,15.6116662 26.9912731,17.7216672 25.69,19.0233333 L25.69,19.0233333 Z"></path>
                                                                <path class="color-background" d="M1.855,31.4066667 C3.05106558,30.2024182 4.79973884,29.7296005 6.43969145,30.1670277 C8.07964407,30.6044549 9.36054508,31.8853559 9.7979723,33.5253085 C10.2353995,35.1652612 9.76258177,36.9139344 8.55833333,38.11 C6.70666667,39.9616667 0,40 0,40 C0,40 0,33.2566667 1.855,31.4066667 Z"></path>
                                                                <path class="color-background" d="M17.2616667,3.90166667 C12.4943643,3.07192755 7.62174065,4.61673894 4.20333333,8.04166667 C3.31200265,8.94126033 2.53706177,9.94913142 1.89666667,11.0416667 C1.5109569,11.6966059 1.61721591,12.5295394 2.155,13.0666667 L5.47,16.3833333 C8.55036617,11.4946947 12.5559074,7.25476565 17.2616667,3.90166667 L17.2616667,3.90166667 Z" opacity="0.598539807"></path>
                                                                <path class="color-background" d="M36.0983333,22.7383333 C36.9280725,27.5056357 35.3832611,32.3782594 31.9583333,35.7966667 C31.0587397,36.6879974 30.0508686,37.4629382 28.9583333,38.1033333 C28.3033941,38.4890431 27.4704606,38.3827841 26.9333333,37.845 L23.6166667,34.53 C28.5053053,31.4496338 32.7452344,27.4440926 36.0983333,22.7383333 L36.0983333,22.7383333 Z" opacity="0.598539807"></path>
                                                            </g>
                                                        </g>
                                                    </g>
                                                </g>
                                            </svg>
                                        </div>
                                        <a href="user-management.php"  <p class="text-xs mt-1 mb-0 font-weight-bold">Total visits</p> </a>

                                    </div>
                                    <h4 class="font-weight-bolder"><?= ($countTracker["COUNT(logID)"])?></h4>
                                    <div class="progress w-75">
                                        <div class="progress-bar bg-dark w-90" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <div class="col-3 py-3 ps-0">
                                    <div class="d-flex mb-2">
                                        <div class="icon icon-shape icon-xxs shadow border-radius-sm bg-gradient-danger text-center me-2 d-flex align-items-center justify-content-center">
                                            <svg width="10px" height="10px" viewBox="0 0 40 40" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                                <title>settings</title>
                                                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                    <g transform="translate(-2020.000000, -442.000000)" fill="#FFFFFF" fill-rule="nonzero">
                                                        <g transform="translate(1716.000000, 291.000000)">
                                                            <g transform="translate(304.000000, 151.000000)">
                                                                <polygon class="color-background" opacity="0.596981957" points="18.0883333 15.7316667 11.1783333 8.82166667 13.3333333 6.66666667 6.66666667 0 0 6.66666667 6.66666667 13.3333333 8.82166667 11.1783333 15.315 17.6716667"></polygon>
                                                                <path class="color-background" d="M31.5666667,23.2333333 C31.0516667,23.2933333 30.53,23.3333333 30,23.3333333 C29.4916667,23.3333333 28.9866667,23.3033333 28.48,23.245 L22.4116667,30.7433333 L29.9416667,38.2733333 C32.2433333,40.575 35.9733333,40.575 38.275,38.2733333 L38.275,38.2733333 C40.5766667,35.9716667 40.5766667,32.2416667 38.275,29.94 L31.5666667,23.2333333 Z" opacity="0.596981957"></path>
                                                                <path class="color-background" d="M33.785,11.285 L28.715,6.215 L34.0616667,0.868333333 C32.82,0.315 31.4483333,0 30,0 C24.4766667,0 20,4.47666667 20,10 C20,10.99 20.1483333,11.9433333 20.4166667,12.8466667 L2.435,27.3966667 C0.95,28.7083333 0.0633333333,30.595 0.00333333333,32.5733333 C-0.0583333333,34.5533333 0.71,36.4916667 2.11,37.89 C3.47,39.2516667 5.27833333,40 7.20166667,40 C9.26666667,40 11.2366667,39.1133333 12.6033333,37.565 L27.1533333,19.5833333 C28.0566667,19.8516667 29.01,20 30,20 C35.5233333,20 40,15.5233333 40,10 C40,8.55166667 39.685,7.18 39.1316667,5.93666667 L33.785,11.285 Z"></path>
                                                            </g>
                                                        </g>
                                                    </g>
                                                </g>
                                            </svg>
                                        </div>
                                        <a href="tickets.php"  <p class="text-xs mt-1 mb-0 font-weight-bold">Unique Visitors</p> </a>
                                    </div>
                                    <h4 class="font-weight-bolder"><?= ($countIP["COUNT(DISTINCT ip)"]) ?></h4>
                                    <div class="progress w-75">
                                        <div class="progress-bar bg-dark w-50" role="progressbar" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-13">
                <div class="card z-index-2">
                    <div class="card-header pb-0">
                        <h6>Statistics</h6>
                        <p class="text-sm">
                            Statistics of incoming and closed tickets during the last 30 days
                        </p>
                    </div>
                    <div class="card-body p-3">
                        <div class="chart">
                            <canvas id="chart-line" class="chart-canvas" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            }
            ?>
        </div>
    <?php
    if ($_SESSION['userType'] === 'Administrator' || $_SESSION["userType"] === 'Responder'):
        ?>
        <div class="row my-4">
            <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="row">
                            <div class="col-lg-6 col-7">
                                <h6>Status tracker</h6>
                                <p class="text-sm mb-0">
                                    <i class="fa fa-check text-info" aria-hidden="true"></i>
                                    Daily tracker of responders
                                </p>
                            </div>
                            <div class="col-lg-6 col-5 my-auto text-end">
                                <div class="dropdown float-lg-end pe-4">
                                    <a class="cursor-pointer" id="dropdownTable" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v text-secondary"></i>
                                    </a>
                                    <ul class="dropdown-menu px-2 py-3 ms-sm-n4 ms-n5" aria-labelledby="dropdownTable">
                                        <li><a class="dropdown-item border-radius-md" href="javascript:">All User profiles</a></li>
                                        <li><a class="dropdown-item border-radius-md" href="javascript:">User permissions</a></li>
                                        <li><a class="dropdown-item border-radius-md" href="javascript:">Something else here</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Responders</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Completed tickets</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Expected completed Tickets</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Daily goal</th>
                                </tr>
                                </thead>
                                <tbody>

                                <?php foreach ($dailyCompletedTickets as $row): ?>

                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div>
                                                    <img src="../assets/img/profile.svg" class="avatar avatar-sm me-3" alt="xd">
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm"><?= $row[0] ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class=" text-sm">
                                            <?php
                                            // Display the number of completed tickets for the current user
                                            if (ISSET($dailyCompletedTickets)) {
                                                echo ($row[1]);
                                            } else {
                                                echo 'No completed tickets';
                                            }
                                            ?>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <?php
                                            // Display the number of completed tickets for the current user
                                            if (!empty($dailyCompletedTickets)) {
                                                echo 20 ;
                                            } else {
                                                echo 'Daily goal reached!';
                                            }
                                            ?>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <?php
                                            // Display the number of completed tickets out of the daily goal for the current user
                                            if (!empty($dailyCompletedTickets)) {
                                                echo ($row[1]), "/20";
                                            } else {
                                                echo 'No completed tickets';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                                </tr>
                                </tbody>
                            </table>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    <?php require 'footer.php'; ?>
</main>
<!--   Core JS Files   -->
<script src="../assets/js/core/popper.min.js"></script>
<script src="../assets/js/core/bootstrap.min.js"></script>
<script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="../assets/js/plugins/chartjs.min.js"></script>
<script>
    var ctx = document.getElementById("chart-bars").getContext("2d");

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["Low", "Medium", "High", "Critical",],
            datasets: [{
                label: "Number of accidents",
                tension: 0.4,
                borderWidth: 0,
                borderRadius: 6,
                borderSkipped: false,
                backgroundColor: "#fff",
                data: [<?= COUNT($countSeverity)?>, <?= COUNT($countSeverity2)?>, <?= COUNT($countSeverity3)?>, <?= COUNT($countSeverity4)?>, ],
                maxBarThickness: 10
            }, ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
            scales: {
                y: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false,
                    },
                    ticks: {
                        suggestedMin: 0,
                        suggestedMax: 500,
                        beginAtZero: true,
                        padding: 15,
                        font: {
                            size: 14,
                            family: "Open Sans",
                            style: 'normal',
                            lineHeight: 2
                        },
                        color: "#fff"
                    },
                },
                x: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false
                    },
                    ticks: {
                        display: false
                    },
                },
            },
        },
    });


    var ctx2 = document.getElementById("chart-line").getContext("2d");

    var gradientStroke1 = ctx2.createLinearGradient(0, 230, 0, 50);

    gradientStroke1.addColorStop(1, 'rgba(203,12,159,0.2)');
    gradientStroke1.addColorStop(0.2, 'rgba(72,72,176,0.0)');
    gradientStroke1.addColorStop(0, 'rgba(203,12,159,0)'); //purple colors

    var gradientStroke2 = ctx2.createLinearGradient(0, 230, 0, 50);

    gradientStroke2.addColorStop(1, 'rgba(20,23,39,0.2)');
    gradientStroke2.addColorStop(0.2, 'rgba(72,72,176,0.0)');
    gradientStroke2.addColorStop(0, 'rgba(20,23,39,0)'); //purple colors

    new Chart(ctx2, {
        type: "line",
        data: {
            labels: ["Week 4", "Week 3", "Week 2", "Week 1",],
            datasets: [{
                label: "Received tickets",
                tension: 0.4,
                borderWidth: 0,
                pointRadius: 0,
                borderColor: "#cb0c9f",
                borderWidth: 3,
                backgroundColor: gradientStroke1,
                fill: true,
                data: [<?php echo count($countTickets4)?>,<?php echo count($countTickets3)?>, <?php echo count($countTickets2)?>, <?php echo count($countTickets)?>],
                maxBarThickness: 6

            },
                {
                    label: "Completed tickets",
                    tension: 0.4,
                    borderWidth: 0,
                    pointRadius: 0,
                    borderColor: "#3A416F",
                    borderWidth: 3,
                    backgroundColor: gradientStroke2,
                    fill: true,
                    data: [<?= COUNT($resolvedTickets4)?>, <?= COUNT($resolvedTickets3)?>, <?= COUNT($resolvedTickets2)?>, <?= COUNT($resolvedTickets)?>, ],
                    maxBarThickness: 6
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
            scales: {
                y: {
                    grid: {
                        drawBorder: false,
                        display: true,
                        drawOnChartArea: true,
                        drawTicks: false,
                        borderDash: [5, 5]
                    },
                    ticks: {
                        display: true,
                        padding: 10,
                        color: '#b2b9bf',
                        font: {
                            size: 11,
                            family: "Open Sans",
                            style: 'normal',
                            lineHeight: 2
                        },
                    }
                },
                x: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false,
                        borderDash: [5, 5]
                    },
                    ticks: {
                        display: true,
                        color: '#b2b9bf',
                        padding: 20,
                        font: {
                            size: 11,
                            family: "Open Sans",
                            style: 'normal',
                            lineHeight: 2
                        },
                    }
                },
            },
        },
    });
</script>
<script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = {
            damping: '0.5'
        }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
</script>
<!-- Github buttons -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
<!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/soft-ui-dashboard.min.js?v=1.0.7"></script>
</body>
</html>