<?php
@define('S_include', "freeclient");
@include_once("config.php");

$userx = @anti_injection($_GET['user']);
$passx = @anti_injection($_GET['pass']);

// Create the MySQLi connection
$mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_NAME);

if ($mysqli->connect_error) {
    die("[Error]: Database connection failed: " . $mysqli->connect_error);
}

if ($userx != "" && $passx != "" && SERVER_ON == true) {
    $string_h = strtolower(hexToStr($passx));
    $string_pass = @anti_injection($string_h);
   
    // Prepare the SQL query to prevent SQL injection
    $stmt = $mysqli->prepare("SELECT * FROM user WHERE id=? AND password=?");
    $stmt->bind_param('ss', $userx, $string_pass);
   
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
       
        if ($row != "") {
            if (strtolower($row['id']) == strtolower($userx) && strtolower($row['password']) == $string_pass) {
                $get_client = sha1($userx . CLIENT_AUTH . $passx);

                if (USER_GCVAR) {
                    $stmt_q1 = $mysqli->prepare("SELECT * FROM logingold WHERE id=?");
                    $stmt_q1->bind_param('s', $userx);
                    $stmt_q1->execute();
                    $result_q1 = $stmt_q1->get_result();
                    $row_q1 = $result_q1->fetch_assoc();

                    date_default_timezone_set("Etc/GMT+5");
                    $date_hoy = date('Y-m-d H:i:s');
                    $expiredate = date('Y-m-d H:i:s', time() + 3600 * 24);

                    if ($row_q1 != "") {
                        $dat_ht = strtotime($date_hoy);
                        $date_db = $row_q1['date'];
                        $dat_db = strtotime($date_db);

                        if ($dat_db <= $dat_ht) {
                            // Update gold
                            $stmt_q2 = $mysqli->prepare("UPDATE usergameinfo SET gold=gold+? WHERE name=?");
                            $stmt_q2->bind_param('is', $USER_GOLDL, $userx);
                            $stmt_q2->execute();

                            $stmt_q3 = $mysqli->prepare("REPLACE INTO logingold(id, date) VALUES (?, ?)");
                            $stmt_q3->bind_param('ss', $userx, $expiredate);
                            $stmt_q3->execute();
                        }
                    }
                }

                if (USER_CCVAR) {
                    $stmt_q1 = $mysqli->prepare("SELECT * FROM logincash WHERE id=?");
                    $stmt_q1->bind_param('s', $userx);
                    $stmt_q1->execute();
                    $result_q1 = $stmt_q1->get_result();
                    $row_q1 = $result_q1->fetch_assoc();

                    date_default_timezone_set("Etc/GMT+5");
                    $date_hoy = date('Y-m-d H:i:s');
                    $expiredate = date('Y-m-d H:i:s', time() + 3600 * 24);

                    if ($row_q1 != "") {
                        $dat_ht = strtotime($date_hoy);
                        $date_db = $row_q1['date'];
                        $dat_db = strtotime($date_db);

                        if ($dat_db <= $dat_ht) {
                            // Update cash
                            $stmt_q2 = $mysqli->prepare("UPDATE cash SET cash=cash+? WHERE id=?");
                            $stmt_q2->bind_param('is', $USER_CASHL, $userx);
                            $stmt_q2->execute();

                            $stmt_q3 = $mysqli->prepare("REPLACE INTO logincash(id, date) VALUES (?, ?)");
                            $stmt_q3->bind_param('ss', $userx, $expiredate);
                            $stmt_q3->execute();
                        }
                    }
                }

                echo $get_client;
            } else {
                echo "[Error]: 1";
            }
        } else {
            $stmt = $mysqli->prepare("SELECT COUNT(id) FROM user WHERE id=?");
            $stmt->bind_param('s', $userx);
            $stmt->execute();
            $result2 = $stmt->get_result();
            $row2 = $result2->fetch_array();

            if ($row2[0] == "1") {
                echo "Password incorrecto!";
            } else {
                echo "Cuenta No Existe!";
            }
        }
    } else {
        echo "[Error]: 2 - Query execution failed.";
    }
} elseif (SERVER_ON == false) {
    echo SERVER_MSJ_M;
}

function anti_injection($sql) {
    $sql = preg_replace("/(from|select|insert|delete|where|drop table|show tables|#|\*|--|\\\\)/i", "", $sql);
    $sql = trim($sql);
    $sql = strip_tags($sql);
    $sql = addslashes($sql);
    return $sql;
}

function hexToStr($hex) {
    $string = '';
    for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
        $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
    }
    return $string;
}

function strToHex($string) {
    $hex = '';
    for ($i = 0; $i < strlen($string); $i++) {
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;
}
?>