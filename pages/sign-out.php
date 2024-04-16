<?php
// Import the session in order to destroy it
session_start();
session_destroy();

//Redirect to sign-in page
header("Location: sign-in.php");