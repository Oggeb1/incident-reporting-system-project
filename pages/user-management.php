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
<?php
if (empty($_SESSION)) {
    session_start();
}

if ($_SESSION['userType'] !== 'Administrator') {
    header("Location: dashboard.php");
}

$pageName = 'user-management';


// Import DB connection
require 'db-connection.php';
$users = $db->query("SELECT userName,email,firstName,lastName,userType FROM user")->fetch_all();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['newSubmit'])) {
        $username = $_POST['newUsername'];
        $firstName = $_POST['newFirstName'];
        $lastName = $_POST['newLastName'];
        $email = $_POST['newEmail'];
        $role = $_POST['role'];

        if (isset($password)) {
            unset($password);
        }

        $password = bin2hex(openssl_random_pseudo_bytes(16));

        $db->execute_query("INSERT INTO user (userName, firstName, lastName, email, userType, password) VALUES ((?), (?), (?), (?), (?), (?))", [$username, $firstName, $lastName, $email, $role, $password]);
        header('Location: user-management.php', true, 303);
        exit();
    }

    if (isset($_POST['editSubmit'])) {

        $oldUsername = $_POST['oldUsername'];
        $username = $_POST['username'];
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $email = $_POST['email'];
        $role = $_POST['role'];

        if (isset($password)) {
            unset($password);
        }

        if (isset($_POST['resetPassword'])) {
            if ($_POST['resetPassword'] === 'on') {
                $password = bin2hex(openssl_random_pseudo_bytes(16));
            }
        }

        if (isset($password)) {
            $db->execute_query("UPDATE user SET userName = (?), firstName = (?), lastName = (?), email = (?), userType = (?), password = (?) WHERE userName = (?)", [$username, $firstName, $lastName, $email, $role, $password, $oldUsername]);
        } else {
            $db->execute_query("UPDATE user SET userName = (?), firstName = (?), lastName = (?), email = (?), userType = (?) WHERE userName = (?)", [$username, $firstName, $lastName, $email, $role, $oldUsername]);
        }

        header('Location: user-management.php', true, 303);
        exit();
    }

    if (isset($_POST['deleteSubmit'])) {
        $username = $_POST['oldUsername'];

        if ($username !== $_SESSION['username']) {
            $db->execute_query("DELETE FROM user WHERE userName = (?)", [$username]);
            header('Location: user-management.php', true, 303);
            exit();
        } else {
            echo "<script type='text/javascript'>alert('Can not delete current user');</script>";
        }
    }
}

require 'sidebar.php';
?>
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card mb-4">
            <div class="card-header pb-0">
              <h6 class="d-inline-block">Users</h6>
                <button type="button" class="btn mb-0 btn-primary icon-move-right d-inline-block float-end" data-bs-toggle="modal" data-bs-target="#newUserModal">New User</button>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Real Name</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Last Seen</th>
                      <th class="text-secondary opacity-7"></th>
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
                            <h6 class="mb-0 text-sm"><?= $row[0] ?></h6>
                            <p class="text-xs text-secondary mb-0"><?= $row[1] ?></p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0"><?=  $row[2] . ' ' . $row[3] ?></p>
                        <p class="text-xs text-secondary mb-0"><?= $row[4] ?></p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="badge badge-sm bg-gradient-success">Online</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">23/04/18</span>
                      </td>
                      <td class="align-middle">
                        <a type="button" class="text-secondary font-weight-bold text-xs" data-bs-toggle="modal" data-bs-target="#editUserModal" data-index='<?= json_encode($row) ?>'>
                          Edit
                        </a>
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
        <?php require 'footer.php';
        ?>
    </div>
    <div class="modal fade" id="newUserModal" tabindex="-1" role="dialog" aria-labelledby="newUserModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Create new user</h5>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <label for="newUsername">Username:</label>
                        <input type="text" name="newUsername" id="newUsername"><br>
                        <label for="newFirstName">First name:</label>
                        <input type="text" id="newFirstName" name="newFirstName"><br>
                        <label for="newLastName">Last name:</label>
                        <input type="text" id="newLastName" name="newLastName"><br>
                        <label for="newEmail">Email:</label>
                        <input type="text" id="newEmail" name="newEmail"><br>
                        <label for="newRoleReporter">Reporter:</label>
                        <input type="radio" id="newRoleReporter" value="Reporter" name="role">
                        <label for="newRoleResponder">Responder:</label>
                        <input type="radio" id="newRoleResponder" value="Responder" name="role">
                        <label for="newRoleAdministrator">Administrator:</label>
                        <input type="radio" id="newRoleAdministrator" value="Administrator" name="role"><br>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-toggle="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="newSubmit">Create user</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle">Edit user</h5>
                </div>
                <form method="POST">
                <div class="modal-body">
                        <input type="hidden" id="oldUsername" name="oldUsername" value="">
                        <label for="userName">Username:</label>
                        <input type="text" name="username" id="userName"><br>
                        <label for="firstName">First name:</label>
                        <input type="text" id="firstName" name="firstName"><br>
                        <label for="lastName">Last name:</label>
                        <input type="text" id="lastName" name="lastName"><br>
                        <label for="email">Email:</label>
                        <input type="text" id="email" name="email"><br>
                        <label for="roleReporter">Reporter:</label>
                        <input type="radio" id="roleReporter" value="Reporter" name="role">
                        <label for="roleResponder">Responder:</label>
                        <input type="radio" id="roleResponder" value="Responder" name="role">
                        <label for="roleAdministrator">Administrator:</label>
                        <input type="radio" id="roleAdministrator" value="Administrator" name="role"><br>
                        <label for="resetPassword">Reset Password:</label>
                        <input type="checkbox" id="resetPassword" name="resetPassword"><br>
                        <button type="submit" class="btn btn-primary" name="deleteSubmit">Delete User<a>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="editSubmit">Save changes</button>
                </div>
                </form>
            </div>
        </div>
    </div>
  </main>
  <script>
      $('#editUserModal').on('show.bs.modal', function (event) {
          var button = $(event.relatedTarget) // Button that triggered the modal
          var editIndex = button.data('index') // Extract info from data-* attributes
          $('#oldUsername').attr('value', editIndex[0])
          $('#userName').val(editIndex[0])
          $('#firstName').val(editIndex[2])
          $('#lastName').val(editIndex[3])
          $('#email').val(editIndex[1])
          if (editIndex[4] === 'Reporter') {
              $('#roleReporter').attr('checked', true)
          } else if (editIndex[4] === 'Responder') {
              $('#roleResponder').attr('checked', true)
          } else {
              $('#roleAdministrator').attr('checked', true)
          }
      })
  </script>
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