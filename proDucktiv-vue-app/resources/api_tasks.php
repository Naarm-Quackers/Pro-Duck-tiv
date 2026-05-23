<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
// api_tasks.php
// purpose:
// - provide CRUD operations for the pdt_tasks mysql table.
// - GET    -> read all tasks or read one task.
// - POST   -> create a task.
// - PUT    -> update a task.
// - DELETE -> delete a task.

header('Content-Type: application/json');

// allow local vite development to call this php api.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// browser sends OPTIONS before PUT/DELETE when using CORS.
// this exits early so the real request can continue afterwards.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// database connection
// original mercury / swinburne feenix database.
// comment out for local xampp testing.

$conn = mysqli_connect('feenix-mariadb.swin.edu.au', 's105341089', '080805', 's105341089_db');

// local xampp database connection.
//$host = "localhost";
//$user = "root";
//$password = "";
//$database = "task"; 

//$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    echo json_encode([
        'success' => false,
        'error' => 'database connection failed: ' . mysqli_connect_error()
    ]);
    exit;
}

mysqli_set_charset($conn, 'utf8');

// table used by this api.
$table = 'pdt_tasks';

// request method and json body.
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    $input = [];
}

// helper: read a text field safely
// trims the value and escapes it before placing it in sql.
function ReadTextField($conn, $input, $field, $fallback = '') {
    if (!isset($input[$field]) || $input[$field] === null) {
        return $fallback;
    }

    return mysqli_real_escape_string($conn, trim((string)$input[$field]));
}

// helper: convert date/time to sql-safe values
// empty date/time should become NULL, not an empty string.
// this avoids mysql errors with DATE and TIME columns.
function ToSqlNullableValue($conn, $value) {
    if (!isset($value) || trim((string)$value) === '') {
        return 'NULL';
    }

    return "'" . mysqli_real_escape_string($conn, trim((string)$value)) . "'";
}

// helper: convert mysql row to vue-friendly object
// the frontend uses capitalised field names to match TaskEditor.
function FormatTaskRow($row) {
    return [
        'Id' => (int)$row['task_id'],
        'Title' => $row['task_title'],
        'Type' => $row['task_type'],
        'Date' => $row['task_date'],
        'Time' => $row['task_time'],
        'Location' => $row['task_location'],
        'Description' => $row['task_description'],
        'Completed' => (bool)$row['task_completed'],
        'CreatedAt' => $row['created_at'],
        'UpdatedAt' => $row['updated_at']
    ];
}

// task id from url
// examples:
// api_tasks.php
// api_tasks.php?TaskId=3
$TaskId = isset($_GET['TaskId']) ? (int)$_GET['TaskId'] : 0;


// GET: read tasks
if ($method === 'GET') {
    if ($TaskId > 0) {
        $sql = "SELECT * FROM `$table` WHERE task_id = $TaskId LIMIT 1";
    } else {
        $sql = "SELECT * FROM `$table` ORDER BY task_completed ASC, task_date ASC, task_time ASC, task_id DESC";
    }

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        echo json_encode([
            'success' => false,
            'error' => mysqli_error($conn)
        ]);
        exit;
    }

    $rows = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = FormatTaskRow($row);
    }

    echo json_encode($rows);
    exit;
}


// POST: create task
if ($method === 'POST') {
    $Title = ReadTextField($conn, $input, 'Title');
    $Type = ReadTextField($conn, $input, 'Type');
    $Date = ToSqlNullableValue($conn, $input['Date'] ?? null);
    $Time = ToSqlNullableValue($conn, $input['Time'] ?? null);
    $Location = ReadTextField($conn, $input, 'Location');
    $Description = ReadTextField($conn, $input, 'Description');
    $Completed = !empty($input['Completed']) ? 1 : 0;

    if ($Title === '' || $Type === '') {
        echo json_encode([
            'success' => false,
            'error' => 'title and type are required'
        ]);
        exit;
    }

    $sql = "INSERT INTO `$table`
            (task_title, task_type, task_date, task_time, task_location, task_description, task_completed)
            VALUES
            ('$Title', '$Type', $Date, $Time, '$Location', '$Description', $Completed)";

    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            'success' => true,
            'Id' => mysqli_insert_id($conn)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => mysqli_error($conn)
        ]);
    }

    exit;
}

// PUT: update task
if ($method === 'PUT') {
    if ($TaskId <= 0) {
        echo json_encode([
            'success' => false,
            'error' => 'missing task id'
        ]);
        exit;
    }

    $Title = ReadTextField($conn, $input, 'Title');
    $Type = ReadTextField($conn, $input, 'Type');
    $Date = ToSqlNullableValue($conn, $input['Date'] ?? null);
    $Time = ToSqlNullableValue($conn, $input['Time'] ?? null);
    $Location = ReadTextField($conn, $input, 'Location');
    $Description = ReadTextField($conn, $input, 'Description');
    $Completed = !empty($input['Completed']) ? 1 : 0;

    if ($Title === '' || $Type === '') {
        echo json_encode([
            'success' => false,
            'error' => 'title and type are required'
        ]);
        exit;
    }

    $sql = "UPDATE `$table` SET
                task_title = '$Title',
                task_type = '$Type',
                task_date = $Date,
                task_time = $Time,
                task_location = '$Location',
                task_description = '$Description',
                task_completed = $Completed
            WHERE task_id = $TaskId";

    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            'success' => true,
            'affectedRows' => mysqli_affected_rows($conn)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => mysqli_error($conn)
        ]);
    }

    exit;
}


// DELETE: delete task
if ($method === 'DELETE') {
    if ($TaskId <= 0) {
        echo json_encode([
            'success' => false,
            'error' => 'missing task id'
        ]);
        exit;
    }

    $sql = "DELETE FROM `$table` WHERE task_id = $TaskId";

    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            'success' => true,
            'affectedRows' => mysqli_affected_rows($conn)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => mysqli_error($conn)
        ]);
    }

    exit;
}

// fallback for unsupported methods.
echo json_encode([
    'success' => false,
    'error' => 'unsupported request method'
]);

mysqli_close($conn);
?>