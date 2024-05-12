<?php
// Check if 'name' parameter is set and not empty
if(isset($_GET['name']) && !empty($_GET['name'])) {
    // Get the file name from the URL 'name' parameter
    $name = $_GET['name'];

    // Set headers for file download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($name) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize("../../uploads/" . $name));

    // Clear output buffer
    ob_clean();
    flush();

    // Read the file and output it to the browser
    readfile("../../uploads/" . $name);
    exit;
} else {
    // If 'name' parameter is not set or empty, display an error message
    echo "File not found.";
}
