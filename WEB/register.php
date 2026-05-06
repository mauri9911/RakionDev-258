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

// Solo procesar si el formulario fue enviado por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Captura de datos con validación básica para evitar Warnings
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Verificar si el ID o Email ya existen
        $sql = "SELECT id, e_mail FROM user WHERE id = ? OR e_mail = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $id, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $error_message = ($row['id'] == $id) ? "User ID already exists." : "Email address already exists.";
        } else {
            // 1. Insertar el nuevo usuario
            $sql = "INSERT INTO user (id, password, e_mail, country, NoCountryUpdate, Authority) VALUES (?, ?, ?, 0, 0, 0)";
            $stmtInsert = $conn->prepare($sql);
            $stmtInsert->bind_param("sss", $id, $password, $email);

            if ($stmtInsert->execute()) {
                // 2. Insertar solo el Cash inicial (Tabla: cash)
                $sqlCash = "INSERT INTO cash (id, cash) VALUES (?, 500000)";
                $stmtCash = $conn->prepare($sqlCash);
                $stmtCash->bind_param("s", $id);
                $stmtCash->execute();
                
                $success_message = "Registration successful! Cash added.";
                if(isset($stmtCash)) $stmtCash->close();
            } else {
                $error_message = "Registration failed.";
            }
            $stmtInsert->close();
        }
        $stmt->close();
    }
}

// Cargar el formulario para mostrar mensajes de éxito o error
include 'register_form.php';
$conn->close();
?>