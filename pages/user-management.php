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
// Set pageName to correctly display it as "active" in the sidebar
$pageName = "User-management";

// Import DB connection
require 'db-connection.php';

// Start session if not already started
if (empty($_SESSION)) {
    session_start();
}

// Only admins should have access to this page
if ($_SESSION['userType'] !== 'Administrator') {
    header("Location: dashboard.php"); // Redirect to dashboard
}

if (isset($_SESSION['newPasswd'])) { // Wonky way to display an alert after page refresh
    $tmpPass = $_SESSION['newPasswd']; // Can't use super global inline
    echo "<script type='text/javascript'>alert('Please send this password to the new user, REMIND THEM TO CHANGE IT IN THE SETTINGS: $tmpPass');</script>"; // Alert to show new password for user
    unset($_SESSION['newPasswd']); // Unset the super-global to combat password leaks
}

// Get the users and information to display
$users = $db->query("SELECT userName,email,firstName,lastName,userType FROM user")->fetch_all();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['newSubmit'])) { // Runs every time new user created
        // Set variables from the filled-in form
        $username = $_POST['newUsername'];
        $firstName = $_POST['newFirstName'];
        $lastName = $_POST['newLastName'];
        $email = $_POST['newEmail'];
        $role = $_POST['role'];

        if (isset($password)) {
            unset($password);
        }

        $usernameList = [];
        foreach ($users as $user) { // Iterate over each user from DB
            $usernameList[] = $user[0]; // Save the userName in array
        }

        if (in_array($username, $usernameList)) { // If the user already exists, don't try to create it
            echo "<script type='text/javascript'>alert('User already exists');</script>";
        } else {
            $password = bin2hex(openssl_random_pseudo_bytes(16)); // Generate a "random" password for user
            $_SESSION['newPasswd'] = $password; // Store in super-global to show in an alert after page refresh
            $password = password_hash($password, PASSWORD_DEFAULT); // Hash the "random" generated password

            // Insert the new user into database
            $db->execute_query("INSERT INTO user (userName, firstName, lastName, email, userType, password) VALUES ((?), (?), (?), (?), (?), (?))", [$username, $firstName, $lastName, $email, $role, $password]);

            // Redirect to self using Post/Redirect/Get
            header('Location: user-management.php', true, 303);
            exit();
        }
    }

    if (isset($_POST['editSubmit'])) { // Runs every time a user is edited
        // Set variables from form
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
            if ($_POST['resetPassword'] === 'on') { // If reset-password checkbox is ticked
                $password = bin2hex(openssl_random_pseudo_bytes(16)); // Generate new "random" password
                $_SESSION['newPasswd'] = $password; // Save the new password in a super-global to display after page refresh
                $password = password_hash($password, PASSWORD_DEFAULT); // Hash the new password
            }
        }

        if (isset($password)) { // Update the user accordingly, separate query if password was changed or not
            $db->execute_query("UPDATE user SET userName = (?), firstName = (?), lastName = (?), email = (?), userType = (?), password = (?) WHERE userName = (?)", [$username, $firstName, $lastName, $email, $role, $password, $oldUsername]);
        } else {
            $db->execute_query("UPDATE user SET userName = (?), firstName = (?), lastName = (?), email = (?), userType = (?) WHERE userName = (?)", [$username, $firstName, $lastName, $email, $role, $oldUsername]);
        }
        // Use Post/Redirect/Get to redirect to self
        header('Location: user-management.php', true, 303);
        exit();
    }

    if (isset($_POST['deleteSubmit'])) { // Runs every time a user is deleted
        // Set username to delete from form
        $username = $_POST['oldUsername'];

        if ($username !== $_SESSION['username']) { // Do not allow to delete the logged-in user
            $db->execute_query("DELETE FROM user WHERE userName = (?)", [$username]); // Delete user
            // Use Post/Redirect/Get to redirect to self
            header('Location: user-management.php', true, 303);
            exit();
        } else { // The username to delete is the same as logged-in, show error
            echo "<script type='text/javascript'>alert('Cannot delete current user');</script>";
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
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Last Seen (UTC)</th>
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
                          <?php
                          //Get the timestamp for the most recent log for each user on every row
                          $latestLog = $db->execute_query("SELECT MAX(log.timestamp) FROM log JOIN user ON log.userID=user.userID WHERE userName LIKE (?)", [$row[0]])->fetch_all();

                          if ($latestLog[0][0] == null) { //If no log is found, Last seen should be "never"
                              $isUserOnline = false;
                              $latestLog = [['Never']]  ;
                          } elseif ((strtotime($latestLog[0][0]) + 600) < time()) { //Converts the fetched time to epoch, check log time + 10 min vs current time
                              $isUserOnline = false; //If the most recent log is older than 10 minutes
                          } else {
                              $isUserOnline = true; //Log must be newer than 10 minutes
                          }

                          if ($isUserOnline) { //If online show the green online box else the gray offline one
                              echo '<span class="badge badge-sm bg-gradient-success">Online</span>';
                          } else {
                              echo '<span class="badge badge-sm bg-gradient-secondary">Offline</span>';
                          }

                          echo '</td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">' . $latestLog[0][0] . '</span>
                      </td>' ?>
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
                        <input type="text" name="newUsername"  id="newUsername" class="form-control" required><br>
                        <label for="newFirstName">First name:</label>
                        <input type="text" id="newFirstName" name="newFirstName" class="form-control" required><br>
                        <label for="newLastName">Last name:</label>
                        <input type="text" id="newLastName" name="newLastName" class="form-control" required><br>
                        <label for="newEmail">Email:</label>
                        <input type="email" id="newEmail" name="newEmail" class="form-control" required><br>
                        <label for="newRoleReporter">Reporter:</label>
                        <input type="radio" id="newRoleReporter" value="Reporter" name="role" required>
                        <label for="newRoleResponder">Responder:</label>
                        <input type="radio" id="newRoleResponder" value="Responder" name="role" required>
                        <label for="newRoleAdministrator">Administrator:</label>
                        <input type="radio" id="newRoleAdministrator" value="Administrator" name="role" required><br>
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
                    <input type="text" class="form-control" name="username" id="userName" required><br>
                    <label for="firstName">First name:</label>
                    <input type="text" id="firstName" class="form-control" name="firstName" required><br>
                    <label for="lastName">Last name:</label>
                    <input type="text" id="lastName" class="form-control" name="lastName" required><br>
                    <label for="email">Email:</label>
                    <input type="email" id="email" class="form-control" name="email" required><br>
                    <label for="roleReporter">Reporter:</label>
                    <input type="radio" id="roleReporter" value="Reporter" name="role" required>
                    <label for="roleResponder">Responder:</label>
                    <input type="radio" id="roleResponder" value="Responder" name="role" required>
                    <label for="roleAdministrator">Administrator:</label>
                    <input type="radio" id="roleAdministrator" value="Administrator" name="role" required><br>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="resetPassword" name="resetPassword">
                        <label class="form-check-label" for="resetPassword">Reset Password</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary align-content-start" style="margin-right: auto" name="deleteSubmit">Delete User</button>
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal">Close</button>
                    <button type="submit" class="btn btn-primary" name="editSubmit">Save changes</button>
                </div>
                </form>
            </div>
        </div>
    </div>
  </main>
  <script>
      // Pre-fil user information in the edit modal
      $('#editUserModal').on('show.bs.modal', function (event) { // When showed
          var button = $(event.relatedTarget) // Button that triggered the modal
          var editIndex = button.data('index') // Extract info from data-* attributes
          // Set variables from the data-index attribute
          $('#oldUsername').attr('value', editIndex[0])
          $('#userName').val(editIndex[0])
          $('#firstName').val(editIndex[2])
          $('#lastName').val(editIndex[3])
          $('#email').val(editIndex[1])
          // Pre fil-radio buttons from index-attribute
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