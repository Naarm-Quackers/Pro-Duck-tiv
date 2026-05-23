<?php
/*
  Required table — run once in your database:

  CREATE TABLE pdt_journals (
    journal_id   CHAR(36)     NOT NULL,
    user_id      INT          NOT NULL,
    journal_name VARCHAR(255) NOT NULL DEFAULT 'Untitled',
    journal_body LONGTEXT,
    journal_date DATE         NOT NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (journal_id),
    KEY idx_user_id (user_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
*/

$method  = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$input   = json_decode(file_get_contents('php://input'), true);

// *** Change to your credentials ***
$conn = mysqli_connect('feenix-mariadb.swin.edu.au', 's105341089', '080805', 's105341089_db');
// $conn = mysqli_connect('localhost', 'root', '', 'test');

mysqli_set_charset($conn, 'utf8');

$table = 'pdt_journals';

$fld = preg_replace('/[^a-z0-9_]+/i', '', array_shift($request));
$key = array_shift($request);

header('Content-Type: application/json');

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
        $uuid = generateUUID();
        $input['journal_id'] = $uuid;

        $columns = array_map(function($c) { return preg_replace('/[^a-z0-9_]+/i', '', $c); }, array_keys($input));
        $values  = array_map(function($v) use ($conn) {
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
            $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM `$table` WHERE journal_id='" . mysqli_real_escape_string($conn, $uuid) . "'"));
            echo json_encode($row);
        } else {
            http_response_code(500);
            echo json_encode(array('error' => mysqli_error($conn)));
        }
        break;

    case 'PUT':
        $columns = array_map(function($c) { return preg_replace('/[^a-z0-9_]+/i', '', $c); }, array_keys($input));
        $values  = array_map(function($v) use ($conn) {
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

function generateUUID() {
    $data    = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

mysqli_close($conn);
?>