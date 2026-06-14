<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    mysqli_query($conn, "DELETE FROM supir WHERE supir_id = '$id'");
}
header("Location: master-supir.php");
exit;