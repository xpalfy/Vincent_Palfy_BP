<?php
require '/etc/e_learning/config.php';

function getDatabaseConnection($masterDBname = "learning")
{
    global $servername, $dbusername, $dbpassword;
    $conn = new mysqli($servername, $dbusername, $dbpassword, $masterDBname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
