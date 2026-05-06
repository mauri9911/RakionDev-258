<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "1234567";
$dbname = "rakion";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$id = $_POST['id'];
$password = $_POST['password'];
$email = $_POST['email'];

// Cantidades iniciales de regalo
$initial_cash = 500000;
$initial_gold = 10000;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_message = "Invalid email format.";
    include 'register_form.php';
    exit;
}

// Check if ID already exists
$sql = "SELECT id, e_mail FROM user WHERE id = ? OR e_mail = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $error_message = ($row['id'] == $id) ? "User ID already exists." : "Email address already exists.";
    include 'register_form.php';
    exit;
}

// INICIO DEL REGISTRO
$conn->begin_transaction();

try {
    // 1. Insertar en tabla user
    $sql1 = "INSERT INTO user (id, password, e_mail, country, NoCountryUpdate, Authority) VALUES (?, ?, ?, 0, 0, 0)";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("sss", $id, $password, $email);
    $stmt1->execute();

    // 2. Insertar Cash inicial (Tabla cash)
    // Nota: Se asume que la columna es 'id' y 'cash'
    $sql2 = "INSERT INTO cash (id, cash) VALUES (?, ?)";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("si", $id, $initial_cash);
    $stmt2->execute();


    // Si todo salió bien, confirmamos los cambios
    $conn->commit();
    $success_message = "Registration successful! You received Cash and Gold.";

} catch (Exception $e) {
    // Si algo falla (ej: la tabla no existe), deshacemos todo
    $conn->rollback();
    $error_message = "Registration failed: " . $e->getMessage();
}

include 'register_form.php';

// Close connections
if(isset($stmt1)) $stmt1->close();
$conn->close();
?>