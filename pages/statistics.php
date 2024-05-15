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

<?php
$pageName = 'Statistics';
require 'db-connection.php';

if (empty($_SESSION)) {
    session_start();
}

if ($_SESSION['userType'] !== 'Administrator') {
    header("Location: dashboard.php");
    exit;
}

//fetches information about all users
$users = $db->query("SELECT userID, userName,email,firstName,lastName,userType FROM user")->fetch_all();
//fetches logs
$log = $db->execute_query("SELECT logID, userID, pageID, INET6_NTOA(log.ip) AS ips, browserID, timestamp FROM log")->fetch_all();

//fetches page count and logs for all users. Joins page on log in order ti display the correct pagename instead of the pageID
$countPageVisit = $db->execute_query("SELECT log.pageID, COUNT(*) AS log_count, GROUP_CONCAT(log.pageID), page.pageID, page.pageDescription AS pageIDs FROM log 
                                                                      JOIN page ON log.pageID = page.pageID
                                                                      GROUP BY log.pageID;")->fetch_all();
require 'sidebar.php';
?>

<head>
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
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
</head>

<body class="g-sidenav-show  bg-gray-100">

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6 class="d-inline-block">Users</h6>

                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Real Name</th>

                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">View Logs</th>

                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <?php foreach ($users as $row): ?>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div>
                                                <img src="../assets/img/profile.svg" class="avatar avatar-sm me-3" alt="user1">
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm"><?= $row[1] ?></h6>
                                                <p class="text-xs text-secondary mb-0"><?= $row[2] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?=  $row[2] . ' ' . $row[3] ?></p>
                                        <p class="text-xs text-secondary mb-0"><?= $row[4] ?></p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <a href=user-statistics.php?id=<?=$row[0]?>">View Logs</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 href=user-statistics.php?id=<?=$row[0]?>" class="modal-title" id="exampleModalLongTitle">View logs</h5>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <label for="logs">Choose a log:</label>
                        <select name="logs" id="logs" class="form-control">

                        </select>
                    </div>
                    <div class="modal-footer">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6 class="d-inline-block"></h6>

                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">pageID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Page Count</th>
                                    <th class="text-secondary opacity-7"></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <?php foreach ($countPageVisit as $row): ?>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div>
                                            </div>
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm"><?= $row[4] ?></h6>

                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?=  $row[1] ?></p>

                                    </td>
                                    <td class="align-middle text-center text-sm">
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require 'footer.php';
        ?>
    </div>

        </div>
    </div>
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 href=user-statistics.php?id=<?=$row[0]?>" class="modal-title" id="exampleModalLongTitle">View logs</h5>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <label for="logs">Choose a log:</label>
                        <select name="logs" id="logs" class="form-control">

                        </select>
                    </div>
                    <div class="modal-footer">
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

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
<!-- Github buttons -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
<!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../assets/js/soft-ui-dashboard.min.js?v=1.0.7"></script>
</body>

</html>