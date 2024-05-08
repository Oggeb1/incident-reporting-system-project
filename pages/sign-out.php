<?php
// Import the session in order to destroy it
session_start();
session_destroy();

if (isset($_COOKIE['token'])) {
    setcookie('token', "", 1);
}
//Redirect to sign-in page
header("Location: sign-in.php");