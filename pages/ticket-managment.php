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
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <title>
        Soft UI Dashboard by Creative Tim
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet"/>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="/resources/demos/style.css">
    <!-- Nucleo Icons -->
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet"/>
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet"/>
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet"/>
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/soft-ui-dashboard.css?v=1.0.7" rel="stylesheet"/>
    <!-- Nepcha Analytics (nepcha.com) -->
    <!-- Nepcha is a easy-to-use web analytics. No cookies and fully compliant with GDPR, CCPA and PECR. -->
    <script defer data-site="YOUR_DOMAIN_HERE" src="https://api.nepcha.com/js/nepcha-analytics.js"></script>
</head>
<?php
if (empty($_SESSION)) {
    session_start();
}

if ($_SESSION['userType'] == 'Reporter')
{
    header('Location: dashboard.php', true, 303);
    exit();
}

$pageName = 'Ticket-management';
require 'db-connection.php';

$ticketSummary = $db->execute_query("SELECT ticketID, incident.incidentID, ticketStatus, userName, responderID, responseDescription, incident.timestamp FROM ticket
    JOIN incident ON ticket.incidentID = incident.incidentID
    JOIN user ON incident.reporterID = user.userID
Where ticketID LIKE ?", [$_GET['id']])->fetch_assoc();

echo $_GET['id'];

//Form submission values are sent here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        header('Location: tickets.php', true, 303);
        exit();

}
require 'sidebar.php';

?>
<body class="g-sidenav-show bg-gray-100">
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg py-4">
    <div class="container-fluid py-2">
        <div class="d-flex justify-content-center">
            <h6 class="mb-0">Viewing Ticket Number: <?=$ticketSummary['incidentID']?></h6>
        </div>
    </div>
    <div class="p-3 d-flex justify-content-center">
            <ul class="list-group">
                <li class="mb-1">
                    <p>Sent in by: <?=$ticketSummary['userName']?></p>
                </li>
                <li class="mb-3">
                    <p>Sent in on: <?=$ticketSummary['timestamp']?></p>
                </li>
                <li>
                    <p>Description: <?=$ticketSummary['responseDescription']?></p>
                </li>
                <li>
                    <p>Assigned to: <?= if (isset($ticketSummary['']))['responderID']?></p>
                </li>
                <li class="mt-3">
                    <button type="submit" class="btn mb-0 btn-primary" name="newTicketSubmit">Submit</button>
                    <button type="reset" class="btn mb-0 btn-primary" onclick="resetForm()">Reset</button>
                    <a href="tickets.php" class="btn mb-0 btn-primary">Cancel</a>
                </li>
            </ul>
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