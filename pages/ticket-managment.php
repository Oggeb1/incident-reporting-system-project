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
    <script> function resetForm() {
            document.getElementById("incidentForm").reset();
            document.getElementById("hiddenInput").style.display = "none"; // Hide the conditional select field
            document.getElementById("hiddenField").removeAttribute("required"); // Remove the required attribute
        }
    </script>
</head>
<?php
//Session is started
if (empty($_SESSION)) {
    session_start();
}
//pageName is set here
$pageName = 'Ticket-management';
require 'db-connection.php';

//Query to get summarized incident details
$ticketSummary = $db->execute_query("SELECT ticketID, incident.incidentID, ticketStatus, incident.incidentSeverity, userName, responderID, responseDescription, incident.incidentDescription ,incident.timestamp FROM ticket
    JOIN incident ON ticket.incidentID = incident.incidentID
    JOIN user ON incident.reporterID = user.userID
Where ticketID LIKE ?", [$_GET['id']])->fetch_assoc();

//Retrieve files for opened incident
$incidentFiles = $db->execute_query("SELECT file.incidentID, file.path FROM file
JOIN incident on file.incidentID = incident.incidentID
JOIN ticket on file.incidentID = ticket.incidentID
Where ticketID LIKE ?", [$_GET['id']])->fetch_all();

//Get responders
$responders = $db->query("SELECT userName, userID FROM user
Where userType LIKE 'Responder' OR userType LIKE 'Administrator'")->fetch_all();

//Get responders assigned to tickets
$assignedResponder = $db->execute_query("SELECT userName, userID FROM user
        JOIN ticket on user.userID = ticket.responderID
Where (user.userType LIKE 'Responder' OR userType LIKE 'Administrator') AND ticketID like ?", [$_GET['id']])->fetch_assoc();

//Get all previous ticket entries for incident
$ticketLog = $db->execute_query("SELECT ticket.incidentID, ticket.responderID, ticket.ticketStatus, ticket.timestamp, user.username, incident.reporterID, ticket.responseDescription
FROM ticket
         JOIN incident ON ticket.incidentID = incident.incidentID
       JOIN user ON ticket.responderID = user.userID
Where ticket.incidentID LIKE ? ORDER BY timestamp DESC", [$db->execute_query("SELECT ticket.incidentID from ticket
where ticket.ticketID LIKE ?", [$_GET['id']])->fetch_row()[0]])->fetch_all();

/*if (!empty($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = 'C:/Users/axell/PhpstormProjects' . $filename;
    if (!empty($filename) && file_exists($filepath)) {

        //Headers Defined here
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: binary");

        readfile($filepath);
        exit; */

//Form submission values are sent here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ticketResponder = $_POST['assignResponder'];
    $responseText = $_POST['responseText'];

    $ticketSummary['incidentID'] = htmlspecialchars($ticketSummary['incidentID']);
    $ticketResponder = htmlspecialchars($ticketResponder);
    $responseText = htmlspecialchars($responseText);

    //Checks if Post has been sent and declares variables from form and if 'archiveTicket' has been sent to POST
    if (isset($_POST['newResponseSubmit']) and !is_null($_POST['archiveTicket'])) {
        $db->execute_query("INSERT INTO ticket (incidentID, responderID, ticketStatus, responseDescription, timestamp)
                            Values ((?), (?),'Resolved', (?), UTC_TIMESTAMP)", [$ticketSummary['incidentID'], $ticketResponder, $responseText]);
        $db->execute_query("UPDATE incident
SET isDeleted = 1
WHERE incidentID LIKE ?",  [$db->execute_query("SELECT ticket.incidentID from ticket
where ticket.ticketID LIKE ?", [htmlspecialchars($_GET['id'])])->fetch_row()[0]]);
    }
    //Checks if Post has been sent and declares variables from form and if 'resolveTicket' has been sent to POST
    elseif (isset($_POST['newResponseSubmit']) and !is_null($_POST['resolveTicket'])) {
        $db->execute_query("INSERT INTO ticket (incidentID, responderID, ticketStatus, responseDescription, timestamp)
                            Values ((?), (?),'Resolved', (?), UTC_TIMESTAMP)", [$ticketSummary['incidentID'], $ticketResponder, $responseText]);
    }
    //Checks if Post has been sent and declares variables from form
    elseif (isset($_POST['newResponseSubmit'])) {
        $db->execute_query("INSERT INTO ticket (incidentID, responderID, ticketStatus, responseDescription, timestamp)
                            Values ((?), (?),'In Progress', (?), UTC_TIMESTAMP)", [$ticketSummary['incidentID'], $ticketResponder, $responseText]);
    }
    else
    {
        echo "ticket update Failed";
    }

    //return to tickets when finished
    header('Location: tickets.php', true, 303);
    exit();

}
require 'sidebar.php';

?>
<body class="g-sidenav-show bg-gray-100">
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg py-4">
    <div class="container-fluid">
        <div class="row py-4">
            <div class="col-12 col-xl-4">
                <div class="card h-100">
                    <div class="card-header pb-0 p-3">
                        <div class="container-fluid py-2">
                            <div class="d-flex justify-content-center">
                                <h6 class="mb-0">Viewing Ticket Number: <?= $ticketSummary['incidentID'] ?></h6>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <ul class="list-group">
                                <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">Sent in
                                        by:</strong> <?= $ticketSummary['userName'] ?></li>
                                <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Sent in
                                        on:</strong> <?= $ticketSummary['timestamp'] ?></li>
                                <li class="list-group-item border-0 ps-0 text-sm"><strong
                                            class="text-dark">Description:</strong> <?= $ticketSummary['incidentDescription'] ?>
                                </li>
                                <li class="list-group-item border-0 ps-0 text-sm"><strong
                                            class="text-dark">Severity:</strong> <?= $ticketSummary['incidentSeverity'] ?>
                                </li>
                                <?php if (isset($ticketSummary['responderID'])) { ?>
                                    <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Assigned
                                            to:</strong> <?= $assignedResponder['userName'] ?></li>
                                <?php } else { ?>
                                    <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Assigned
                                            to: Unassigned</strong></li>
                                <?php } ?>
                                <strong class="text-dark text-sm">Associated Files:</strong>
                                <?php foreach ($incidentFiles as $files): ?>
                                    <li class="list-group-item border-0 ps-0 text-sm"><?php
                                        $file = substr($files[1], 14); ?>
                                        <a href='download.php?name=<?=$file?>'><?=substr($files[1], 14);?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($_SESSION['userType'] == 'Administrator' || $_SESSION['userType'] == 'Responder'){ ?>
            <div class="col-12 col-xl-7">
                <div class="card h-100">
                    <div class="card-header pb-0 p-3">
                        <div class="container-fluid py-2">
                            <div class="d-flex justify-content-center">
                                <h6 class="mb-0">Edit Ticket:</h6>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <form id="editTicket" method="POST" class="w-sm-60">
                                <ul class="list-group">
                                    <li class="mb-1">
                                        <div class="form-group">
                                            <label for="assignResponder">Assign Responder to Ticket</label>
                                            <select class="responder-select" name="assignResponder" required>
                                                <?php if (is_null($assignedResponder['userID'])) { ?>
                                                    <option value="">Assign Responder</option>
                                                    <?php foreach ($responders as $responderRow): ?>
                                                        <option value="<?= $responderRow[1] ?>"><?= $responderRow[0] ?></option>
                                                    <?php endforeach; ?>
                                                <?php } else { ?>
                                                    <option value="<?= $assignedResponder['userID'] ?>">
                                                        Current: <?= $assignedResponder['userName'] ?></option>
                                                    <?php foreach ($responders as $responderRow): ?>
                                                        <option value="<?= $responderRow[1] ?>"><?= $responderRow[0] ?></option>
                                                    <?php endforeach;
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </li>
                                    <li class="mb-3">
                                        <label for="responseText" class="form-label">Update ticket response</label>
                                        <textarea class="form-control" name="responseText" id="responseTextID" rows="3"
                                                  required></textarea>
                                    </li>
                                    <li>
                                        <input type="checkbox" id="resolveTicket" name="resolveTicket" value="resolve">
                                        <label for="resolveTicket"> Resolve Ticket</label>
                                    </li>
                                    <?php } if ($_SESSION['userType'] == 'Administrator'){ ?>
                                    <li>
                                        <input type="checkbox" id="archiveTicket" name="archiveTicket" value="archive">
                                        <label for="archiveTicket"> Remove Ticket</label>
                                    </li>
                                    <li class="mt-3">
                                        <button type="submit" class="btn mb-0 btn-primary" name="newResponseSubmit">
                                            Submit
                                        </button>
                                        <button type="reset" class="btn mb-0 btn-primary" onclick="resetForm()">Reset
                                        </button>
                                        <a href="tickets.php" class="btn mb-0 btn-primary">Cancel</a>
                                    </li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            </form>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </div>
    <div class="card mb-4">
        <div class="card-header pb-0">
            <h6 class="d-inline-block mb-2">Ticket Response Log</h6>
    <div id="responseLog" class="card-body px-0 pt-0 pb-2 tab-pane">
        <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
                <thead>
                <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                        Updated By
                    </th>
                    <th class="text-uppercase text-secondary  text-xxs font-weight-bolder opacity-7 ps-2">
                        Response
                    </th>
                    <th class="text-uppercase text-secondary  text-xxs font-weight-bolder opacity-7 ps-2">
                        Submitted on
                    </th>
                    <th class="text-uppercase text-secondary  text-xxs font-weight-bolder opacity-7 ps-2">
                        Status
                    </th>
                </tr>
                </thead>
                <?php foreach ($ticketLog as $row): ?>
                <tbody>
                <tr>
                    <td>
                        <div class="d-flex px-2 py-1">
                            <div class="d-flex flex-column justify-content-center">
                                <h6 class="mb-0 text-sm"><?=$row[4]?></h6>
                            </div>
                        </div>
                    </td>
                    <td>
                        <p class="ellipsis text-xs max-width-300 font-weight-bold mb-0 mx-0" data-text="<?=$row[6]?>"><?=$row[6]?></p>
                    </td>
                    <td class="text-sm">
                        <p class="text-xs font-weight-bold mb-0"><?=$row[3]?></p>
                    </td>
                    <td>
                        <p class="text-xs font-weight-bold mb-0"><?=$row[2]?></p>
                    </td>
                </tr>
                </tbody>
                <?php endforeach; ?>
            </table>
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