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
    <?php $pageName = '';
    require 'sidebar.php';
    require 'db-connection.php';

    $incidentTypes = $db->query("Select incidentType.incidentTypeDescription from incidentType")->fetch_all();
    $assettypes = $db->query("Select assetType.assetTypeDescription, assetTypeID from assetType")->fetch_all();
    $assets = $db->query("Select asset.assetDescription, assetType.assetTypeID
                                FROM assetType
                                JOIN asset on assetType.assetTypeID = asset.assetTypeID")->fetch_all();
    ?>
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

<body class="g-sidenav-show bg-gray-100">
<div class="main-content position-relative max-height-vh-100 h-100 border-radius-lg py-4">
    <div class="container-fluid py-2">
        <div class="d-flex justify-content-center">
            <h6 class="mb-0">Create New Ticket:</h6>
        </div>
    </div>
    <div class="p-3 d-flex justify-content-center">
        <form id="incidentForm" method="POST" class="w-sm-60">
            <ul class="list-group">
                <li class="mb-1">
                    <div class="form-group">
                        <label>Select incident type</label>
                        <select class="incident-select" required>
                            <option value="">Select incident type</option>
                            <?php foreach ($incidentTypes as $typeRow): ?>
                            <option value="<?php $typeRow[0]?>"><?=$typeRow[0]?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Example invalid custom select feedback</div>
                    </div>
                </li>
                <li>
                    <label>Incident Severity</label>
                    <div class="col-auto">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="radioFilterSeverity" id="filter-low" checked>
                            <label class="form-check-label" for="inlineRadioLow">Low</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="radioFilterSeverity" id="filter-medium">
                            <label class="form-check-label" for="inlineRadioMedium">Medium</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="radioFilterSeverity" id="filter-critical">
                            <label class="form-check-label" for="inlineRadioCritical">Critical</label>
                        </div>
                    </div>
                </li>
                <li class="mb-3">
                    <label for="incidentDescription" class="form-label">Describe the incident</label>
                    <textarea class="form-control" id="incidentDescriptionText" rows="3" required></textarea>
                </li>
                <li>
                <div class="form-group">
                    <label for="options">Select affected asset type (If applicable)</label>
                    <select id="options" class="assetType-select" onchange="toggleHidden()">
                        <option id="options" value="hide"">None</option>
                        <?php foreach ($assettypes as $assettypeRow): ?>
                            <option value="show"><?=$assettypeRow[0]?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Example invalid custom select feedback</div>
                </div>
                    <div id="hiddenInput" class="form-group" style="display:none">
                        <label>Select affected asset (If applicable)</label>
                        <select id="hiddenField" class="asset-select">
                            <option value="">None</option>
                            <?php foreach ($assets as $assetsRow): ?>
                                <option><?=$assetsRow[1]?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Example invalid custom select feedback</div>
                    </div>
                </li>
                <li>
                    <label for="exampleFormControlFile1">Upload evidence (if applicable)</label>
                <div class="py-2 card form-check max-width-300">
                    <input type="file" class="form-control-file" id="exampleFormControlFile1" accept="image/*, .pdf, .txt, .docx, .rtf, .odf, .doc, .pages">
                </div>
                </li>
                <li class="mt-3">
                    <button type="submit" class="btn mb-0 btn-primary">Submit</button>
                    <button type="reset" class="btn mb-0 btn-primary" onclick="resetForm()">Reset</button>
                    <a href="tickets.php" class="btn mb-0 btn-primary">Cancel</a>
                </li>
        </div>
    </ul>
    </form>
</div>
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