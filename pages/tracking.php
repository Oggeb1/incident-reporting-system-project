<?php
//Not all pages includes to db at the beginning of the file
if (empty($db)) {
    include 'db-connection.php';
}

//Use browscap.ini to match user-agent information, not reliable, can easily be spoofed by client
$browser = get_browser(null,true);
$browserName = $browser['browser'];

//As the $pageName is in the sidebar, the variable is not set at sign-in
if (empty($pageName)) {
    $pageName = 'Sign-in';
    $userID = [[null]];
} else { // If the sidebar is showing ($pageName is defined), get the ID for logged-in user
    $userID = $db->execute_query("SELECT userID FROM user WHERE userName LIKE ?", [$_SESSION['username']])->fetch_all();
}
//Match the name of the page and browser with the corresponding ID
$browserID = $db->execute_query("SELECT browserID FROM browser WHERE browserDescription LIKE ?", [$browserName])->fetch_all();
$pageID = $db->execute_query("SELECT pageID FROM page WHERE page.pageDescription LIKE ?", [$pageName])->fetch_all();

$browserID[0][0] = htmlspecialchars($browserID[0][0]);
$pageID[0][0] = htmlspecialchars($pageID[0][0]);

if ($userID[0][0] != "") {
    $userID[0][0] = htmlspecialchars($userID[0][0]);
}

//Finally insert the log with all the IDs
$db->execute_query("INSERT INTO log (userID, pageID, ip, browserID) VALUES ((?), (?), INET6_ATON((?)), (?))", [$userID[0][0], $pageID[0][0], htmlspecialchars($_SERVER['REMOTE_ADDR']), $browserID[0][0]]);