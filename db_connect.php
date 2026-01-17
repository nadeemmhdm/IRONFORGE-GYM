<?php
$servername = "sql305.infinityfree.com";
$username = "if0_40614083";
$password = "p1gSgzSdNIWFR";
$dbname = "if0_40614083_mygym";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>