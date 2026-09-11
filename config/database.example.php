<?php
/**
 * Database Configuration Template
 * -----------------------------------
 * Copy this file to `database.php` and fill in your own values.
 *
 *   Windows:  copy database.example.php database.php
 *   Mac/Linux: cp database.example.php database.php
 */

$host     = "localhost";
$user     = "root";              // ← your MySQL username
$password = "";                  // ← your MySQL password (empty by default in XAMPP)
$database = "studenthub-db";     // ← your database name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}