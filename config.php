<?php
// ================================================================
// Database Connection (procedural mysqli)
// ================================================================
$conn = mysqli_connect('localhost', 'root', '', 'medicine_shop');
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
