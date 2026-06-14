<?php
session_start();
require_once '../../config/database.php';
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    mysqli_query($conn, "DELETE FROM mobil WHERE id = '$id'");
}
header("Location: master-mobil.php");
exit;