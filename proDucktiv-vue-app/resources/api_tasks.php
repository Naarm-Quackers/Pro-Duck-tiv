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
// connects this php api to the Feenix MariaDB database.
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


// pdt_tasks contains the task records and includes user_id as a foreign key.
$table = 'pdt_tasks';
$method = $_SERVER['REQUEST_METHOD'];

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    $input = [];
}

// used when reading, updating or deleting one specific task.
// example: api_tasks.php?TaskId=3&UserId=5
$TaskId = isset($_GET['TaskId']) ? (int)$_GET['TaskId'] : 0;


// UserId must come from the active logged-in user.
// this value links pdt_tasks.user_id to pdt_users.user_id.
// the api refuses to continue if UserId is missing.
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

// safely reads text fields from the json request body.
// escapes special characters before they are used in sql.
function ReadTextField($conn, $input, $field, $fallback = '') {
    if (!isset($input[$field]) || $input[$field] === null) {
        return $fallback;
    }

    return mysqli_real_escape_string($conn, trim((string)$input[$field]));
}

// converts date/time values into sql-safe values.
// empty values become NULL.
// valid values are escaped and wrapped in quotes.
function ToSqlNullableValue($conn, $value) {
    if (!isset($value) || trim((string)$value) === '') {
        return 'NULL';
    }

    return "'" . mysqli_real_escape_string($conn, trim((string)$value)) . "'";
}

// converts database column names into frontend-friendly names.
// this makes the json easier to use inside Vue components.
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
// if TaskId is provided, read one task owned by this user, otherwise, read all tasks owned by this user.
// user_id filtering prevents users from seeing other users' tasks.
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

// reads json data sent by Vue, validates that all required fields are filled.
// inserts a new task linked to the current UserId.
if ($method === 'POST') {
    $Title = ReadTextField($conn, $input, 'Title');
    $Type = ReadTextField($conn, $input, 'Type');
    $Date = ToSqlNullableValue($conn, isset($input['Date']) ? $input['Date'] : null); // required field, validated below
    $Time = ToSqlNullableValue($conn, isset($input['Time']) ? $input['Time'] : null); // required field, validated below
    $Location = ReadTextField($conn, $input, 'Location');
    $Description = ReadTextField($conn, $input, 'Description');
    $Completed = !empty($input['Completed']) ? 1 : 0;

    if (
        $Title === '' ||
        $Type === '' ||
        empty($input['Date']) ||
        empty($input['Time']) ||
        $Location === '' ||
        $Description === ''
    ) {
        echo json_encode([
            'success' => false,
            'error' => 'all task fields are required'
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

// validates the full updated task data, updates only the task matching both task_id and user_id.
// this prevents users from editing tasks that do not belong to them.
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
    $Date = ToSqlNullableValue($conn, isset($input['Date']) ? $input['Date'] : null); // required field, validated below
    $Time = ToSqlNullableValue($conn, isset($input['Time']) ? $input['Time'] : null); // required field, validated below
    $Location = ReadTextField($conn, $input, 'Location');
    $Description = ReadTextField($conn, $input, 'Description');
    $Completed = !empty($input['Completed']) ? 1 : 0;

    if (
        $Title === '' ||
        $Type === '' ||
        empty($input['Date']) ||
        empty($input['Time']) ||
        $Location === '' ||
        $Description === ''
    ) {
        echo json_encode([
            'success' => false,
            'error' => 'all task fields are required'
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

// deletes only the task matching both task_id and user_id, this protects task ownership.
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
