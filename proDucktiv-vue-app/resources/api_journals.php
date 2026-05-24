<?php
/*
 Table creation SQL for reference:
    
 CREATE TABLE pdt_journals (
    journal_id     INT          NOT NULL AUTO_INCREMENT,
    user_id        INT          NOT NULL,
    journal_name   VARCHAR(255) NOT NULL,
    journal_body   LONGTEXT,
    journal_date   DATE         NOT NULL,
    journal_status VARCHAR(10)  DEFAULT NULL,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (journal_id),
    CONSTRAINT fk_pdt_journals_user
        FOREIGN KEY (user_id) REFERENCES pdt_users(user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

*/

ob_start();
ini_set('display_errors', 0);
error_reporting(0);

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents('php://input'), true);

// *** Change to your credentials ***
$conn = mysqli_connect('feenix-mariadb.swin.edu.au', 's105341089', '080805', 's105341089_db');
// $conn = mysqli_connect('localhost', 'root', '', 'test');

// Discard any PHP HTML errors captured in the buffer before sending any output
ob_end_clean();
header('Content-Type: application/json');

if (!$conn) {
    http_response_code(500);
    echo json_encode(array('error' => 'DB connection failed: ' . mysqli_connect_error()));
    exit;
}

mysqli_set_charset($conn, 'utf8');

$table = 'pdt_journals';

$fld = preg_replace('/[^a-z0-9_]+/i', '', isset($_GET['fld']) ? $_GET['fld'] : '');
$key = isset($_GET['key']) ? $_GET['key'] : '';

switch ($method) {

    case 'GET':
        $where = $key ? " WHERE `$fld`='" . mysqli_real_escape_string($conn, $key) . "'" : '';
        $sql = "SELECT * FROM `$table`$where ORDER BY journal_date DESC";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            $rows = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            echo json_encode($rows);
        } else {
            http_response_code(500);
            echo json_encode(array('error' => mysqli_error($conn)));
        }
        break;

    case 'POST':
        $columns = array_map(function ($c) {
            return preg_replace('/[^a-z0-9_]+/i', '', $c);
        }, array_keys($input));
        $values  = array_map(function ($v) use ($conn) {
            if ($v === null) return null;
            return mysqli_real_escape_string($conn, (string)$v);
        }, array_values($input));

        $set = '';
        for ($i = 0; $i < count($columns); $i++) {
            $set .= ($i > 0 ? ',' : '') . '`' . $columns[$i] . '`=';
            $set .= ($values[$i] === null ? 'NULL' : '"' . $values[$i] . '"');
        }

        $result = mysqli_query($conn, "INSERT INTO `$table` SET $set");
        if ($result) {
            $newId = mysqli_insert_id($conn);
            $row   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$table` WHERE journal_id=" . intval($newId)));
            echo json_encode($row);
        } else {
            http_response_code(500);
            echo json_encode(array('error' => mysqli_error($conn)));
        }
        break;

    case 'PUT':
        $columns = array_map(function ($c) {
            return preg_replace('/[^a-z0-9_]+/i', '', $c);
        }, array_keys($input));
        $values  = array_map(function ($v) use ($conn) {
            if ($v === null) return null;
            return mysqli_real_escape_string($conn, (string)$v);
        }, array_values($input));

        $set = '';
        for ($i = 0; $i < count($columns); $i++) {
            $set .= ($i > 0 ? ',' : '') . '`' . $columns[$i] . '`=';
            $set .= ($values[$i] === null ? 'NULL' : '"' . $values[$i] . '"');
        }

        $safeKey = mysqli_real_escape_string($conn, $key);
        $result  = mysqli_query($conn, "UPDATE `$table` SET $set WHERE `$fld`='$safeKey'");
        if ($result) {
            $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$table` WHERE `$fld`='$safeKey'"));
            echo json_encode($row);
        } else {
            http_response_code(500);
            echo json_encode(array('error' => mysqli_error($conn)));
        }
        break;

    case 'DELETE':
        $safeKey = mysqli_real_escape_string($conn, $key);
        $result  = mysqli_query($conn, "DELETE FROM `$table` WHERE `$fld`='$safeKey'");
        if ($result) {
            echo json_encode(array('deleted' => mysqli_affected_rows($conn)));
        } else {
            http_response_code(500);
            echo json_encode(array('error' => mysqli_error($conn)));
        }
        break;
}


mysqli_close($conn);
