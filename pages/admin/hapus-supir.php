<?php
session_start();
require_once '../../config/database.php';
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    mysqli_query($conn, "DELETE FROM supir WHERE supir_id = '$id'");
}
header("Location: master-supir.php");
exit;