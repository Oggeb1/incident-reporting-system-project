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

    <!-- Scripts for hiding HTML based on its attribute and resetting a forms input values -->
    <script>
        function toggleHidden() {
            var selectBox = document.getElementById("options");
            var inputField = document.getElementById("hiddenInput");

            if (selectBox.value === "show") {
                inputField.style.display = "block";
                document.getElementById("hiddenField").setAttribute("required", "required");
            } else {
                inputField.style.display = "none";
                document.getElementById("hiddenField").removeAttribute("required");
            }
        }

        function resetForm() {
            document.getElementById("incidentForm").reset();
            document.getElementById("hiddenInput").style.display = "none"; // Hide the conditional select field
            document.getElementById("hiddenField").removeAttribute("required"); // Remove the required attribute
        }
    </script>
</head>
<?php
if (empty($_SESSION)) {
    session_start();
}

$pageName = 'Create-ticket';
require 'db-connection.php';

//Database queries to allow users to select from the different incident types, asset types and assets in teh database
$incidentTypes = $db->query("Select incidentType.incidentTypeDescription, incidentType.incidentTypeID from incidentType")->fetch_all();
$assettypes = $db->query("Select assetType.assetTypeDescription, assetTypeID from assetType")->fetch_all();
$assets = $db->query("Select asset.assetDescription, assetType.assetTypeID
                                FROM assetType
                                JOIN asset on assetType.assetTypeID = asset.assetTypeID")->fetch_all();

//Form submission values are sent here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //Checks if all values are available to send to the server
    if (isset($_POST['newTicketSubmit'])) {
        $type = $_POST['incidentType'];
        $severity = $_POST['severity'];
        $description = $_POST['incidentDescription'];
        $reporter = $db->execute_query("Select userID FROM user WHERE userName = ?", [$_SESSION['username']])->fetch_assoc();
        $asset = $db->execute_query("Select assetID FROM asset WHERE assetDescription = ?", [$_POST['asset-select']])->fetch_assoc();

        //Queries to insert new ticket into incident
        $db->execute_query("INSERT INTO incident 
                (reporterID, incidentTypeID, incidentSeverity, incidentDescription, timestamp) 
                values ((?), (?), (?), (?), UTC_TIMESTAMP)",
            [$reporter['userID'], $type, $severity, $description]);

        //gets last inserted Primary Key from Database, this command is client sided and can't be interfered by other users
        $ticketSubmitID = $db->execute_query("SELECT incidentID FROM incident WHERE reporterID = ? AND incident.timestamp = (SELECT MAX(incident.timestamp) FROM incident WHERE reporterID = ?)", [$reporter['userID'], $reporter['userID']] )->fetch_row()[0];
        //Inserts the new ticket into incident with appropriate values
        $db->execute_query("INSERT INTO ticket 
    (incidentID, ticketStatus, responseDescription, timestamp) 
            values ((?), 'Pending', (?), UTC_TIMESTAMP)",
            [$ticketSubmitID, $description]);

        // File upload
        for ($i=0; $i < count($_FILES['incidentFile']['name']); $i++) { // Loop for each uploaded file
            // Information about the file and allowed file-types
            $fileExtension = pathinfo($_FILES["incidentFile"]['name'][$i],PATHINFO_EXTENSION);
            //Rename file to random string but keep extension
            $targetFile = '../../uploads/' . bin2hex(openssl_random_pseudo_bytes(40)) . '.' . $fileExtension;
            $allowedFileTypes = ['video/mp4', 'video/mpeg', 'application/pdf', 'audio/mpeg', 'application/msword', 'audio/aac', 'text/plain', 'application/rtf', 'application/vnd.oasis.opendocument.text'];

            // Check if file is of the right size and type
            if ($_FILES["incidentFile"]["size"][$i] < 50000000 and (is_array(getimagesize($_FILES["incidentFile"]['tmp_name'][$i])) or in_array(mime_content_type($_FILES["incidentFile"]['tmp_name'][$i]), $allowedFileTypes))) {
                if (move_uploaded_file($_FILES['incidentFile']["tmp_name"][$i], $targetFile)) {
                    // if upload successful upload path to DB
                    $db->execute_query("INSERT INTO file (project.file.incidentID, project.file.path) VALUES ((?), (?))",[$ticketSubmitID, $targetFile] );
                }
            }
        }


        //Query to insert affected assets into incidentAsset
        if (isset($asset)) {
        $db->execute_query("INSERT INTO incidentAsset (assetID, incidentID) VALUES ((?), (?))",[$asset['assetID'], $ticketSubmitID] );
        }

        header('Location: tickets.php', true, 303);
        exit();
    }

}
require 'sidebar.php';

?>
<body class="g-sidenav-show bg-gray-100">
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg py-4">
    <div class="container-fluid py-2">
        <div class="d-flex justify-content-center">
            <h6 class="mb-0">Create New Ticket:</h6>
        </div>
    </div>
    <div class="p-3 d-flex justify-content-center">
        <form id="incidentForm" method="POST" class="w-sm-60" enctype="multipart/form-data">
            <ul class="list-group">
                <li class="mb-1">
                    <div class="form-group">
                        <label for="incidentType">Select incident type</label>
                            <select class="incident-select" name="incidentType" required>
                                <option value="">Select incident type</option>
                                <?php foreach ($incidentTypes as $typeRow): ?>
                                <option value="<?=$typeRow[1]?>"><?=$typeRow[0]?></option>
                                <?php endforeach; ?>
                            </select>
                        <div class="invalid-feedback">Example invalid custom select feedback</div>
                    </div>
                </li>
                <li>
                    <label>Incident Severity</label>
                    <div class="col-auto">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="severity" id="filter-low" value="Low" checked>
                            <label class="form-check-label" for="severity">Low</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="severity" id="filter-medium" value="Medium">
                            <label class="form-check-label" for="severity">Medium</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="severity" id="filter-high" value="High">
                            <label class="form-check-label" for="severity">High</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="severity" id="filter-critical" value="Critical">
                            <label class="form-check-label" for="severity">Critical</label>
                        </div>
                    </div>
                </li>
                <li class="mb-3">
                    <label for="incidentDescription" class="form-label">Describe the incident</label>
                    <textarea class="form-control" name="incidentDescription" id="incidentDescriptionText" rows="3" required></textarea>
                </li>
                <li>
                <!--<div class="form-group">
                    <label for="options">Select affected asset type (If applicable)</label>
                    <select id="options" class="assetType-select" onchange="toggleHidden()">
                        <option id="options" value="hide"">None</option>
                       <?php //foreach ($assettypes as $assettypeRow): ?>
                            <option value="show"><?= $assettypeRow[0]?></option>
                        <?php //endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Example invalid custom select feedback</div>
                </div> -->
                    <div class="form-group" style=""> <!-- id="hiddenInput" -->
                        <label for="asset-select">Select affected asset (If applicable)</label>
                        <select class="asset-select" name="asset-select">  <!-- id="hiddenField" -->
                            <option value="">None</option>
                            <?php foreach ($assets as $assetsRow): ?>
                                <option><?=$assetsRow[0]?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Example invalid custom select feedback</div>
                    </div>
                </li>
                <li>
                    <label for="incidentFile">Upload evidence (if applicable)</label>
                <div class="py-2 card form-check max-width-300">
                    <input type="file" class="form-control-file" name="incidentFile[]" id="incidentFile" multiple accept="image/*, audio/*, video/*, .pdf, .txt, .docx, .rtf, .odt, .doc">
                </div>
                </li>
                <li class="mt-3">
                    <button type="submit" class="btn mb-0 btn-primary" name="newTicketSubmit">Submit</button>
                    <button type="reset" class="btn mb-0 btn-primary" onclick="resetForm()">Reset</button>
                    <a href="tickets.php" class="btn mb-0 btn-primary">Cancel</a>
                </li>
        </div>
    </ul>
    </form>
</main>
</div>
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