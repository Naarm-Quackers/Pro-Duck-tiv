<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// database connection
$conn = mysqli_connect(
    'feenix-mariadb.swin.edu.au',
    's105341089',
    '080805',
    's105341089_db'
);

// local xampp connection if needed
// $conn = mysqli_connect("localhost", "root", "", "task");

if (!$conn) {
    echo json_encode([
        'success' => false,
        'error' => 'database connection failed: ' . mysqli_connect_error()
    ]);
    exit;
}

mysqli_set_charset($conn, 'utf8');

$table = 'pdt_tasks';
$method = $_SERVER['REQUEST_METHOD'];

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    $input = [];
}

// task id from URL
$TaskId = isset($_GET['TaskId']) ? (int)$_GET['TaskId'] : 0;

// user id must come from logged-in frontend/session
$UserId = 0;

if (isset($_GET['UserId'])) {
    $UserId = (int)$_GET['UserId'];
} elseif (isset($input['UserId'])) {
    $UserId = (int)$input['UserId'];
}

if ($UserId <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'missing user id'
    ]);
    exit;
}

function ReadTextField($conn, $input, $field, $fallback = '') {
    if (!isset($input[$field]) || $input[$field] === null) {
        return $fallback;
    }

    return mysqli_real_escape_string($conn, trim((string)$input[$field]));
}

function ToSqlNullableValue($conn, $value) {
    if (!isset($value) || trim((string)$value) === '') {
        return 'NULL';
    }

    return "'" . mysqli_real_escape_string($conn, trim((string)$value)) . "'";
}

function FormatTaskRow($row) {
    return [
        'Id' => (int)$row['task_id'],
        'UserId' => (int)$row['user_id'],
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

// GET: read tasks for current user
if ($method === 'GET') {
    if ($TaskId > 0) {
        $sql = "SELECT * FROM `$table`
                WHERE task_id = $TaskId
                AND user_id = $UserId
                LIMIT 1";
    } else {
        $sql = "SELECT * FROM `$table`
                WHERE user_id = $UserId
                ORDER BY task_completed ASC, task_date ASC, task_time ASC, task_id DESC";
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

// POST: create task for current user
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
            (user_id, task_title, task_type, task_date, task_time, task_location, task_description, task_completed)
            VALUES
            ($UserId, '$Title', '$Type', $Date, $Time, '$Location', '$Description', $Completed)";

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

// PUT: update only the current user's task
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
                task_completed = $Completed,
                updated_at = NOW()
            WHERE task_id = $TaskId
            AND user_id = $UserId";

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

// DELETE: delete only the current user's task
if ($method === 'DELETE') {
    if ($TaskId <= 0) {
        echo json_encode([
            'success' => false,
            'error' => 'missing task id'
        ]);
        exit;
    }

    $sql = "DELETE FROM `$table`
            WHERE task_id = $TaskId
            AND user_id = $UserId";

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

echo json_encode([
    'success' => false,
    'error' => 'unsupported request method'
]);

mysqli_close($conn);
?>