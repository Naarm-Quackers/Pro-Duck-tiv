<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(0);

$conn = mysqli_connect('feenix-mariadb.swin.edu.au', 's105341089', '080805', 's105341089_db');

ob_end_clean();
header('Content-Type: application/json');

if (!$conn) {
    http_response_code(500);
    echo json_encode(array('error' => 'DB connection failed: ' . mysqli_connect_error()));
    exit;
}

// initialise the table name accordingly
$table = "pdt_users";

// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
// $request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);  // json string to associative array(true)

mysqli_set_charset($conn,'utf8');

if ($method === 'GET') {
	$fld = preg_replace('/[^a-z0-9_]+/i', '', isset($_GET['fld']) ? $_GET['fld'] : '');
	$key = isset($_GET['key']) ? $_GET['key'] : '';

	$where = ($fld && $key) ? " WHERE `$fld`='" . mysqli_real_escape_string($conn, $key) . "'" : '';
	$sql = "SELECT user_id, user_name, user_first_name, user_last_name, user_date_of_birth FROM `$table`$where";
	$result = mysqli_query($conn, $sql);

	$rows = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}

	echo json_encode($rows);
	exit;
}

if ($method === 'POST') {
	$user_name          = mysqli_real_escape_string($conn, isset($input['user_name'])          ? $input['user_name']          : '');
	$user_password      = mysqli_real_escape_string($conn, isset($input['user_password'])      ? $input['user_password']      : '');
	$user_first_name    = mysqli_real_escape_string($conn, isset($input['user_first_name'])    ? $input['user_first_name']    : '');
	$user_last_name     = mysqli_real_escape_string($conn, isset($input['user_last_name'])     ? $input['user_last_name']     : '');
	$user_date_of_birth = mysqli_real_escape_string($conn, isset($input['user_date_of_birth']) ? $input['user_date_of_birth'] : '');

	$sql = "INSERT INTO `$table` (user_name, user_password, user_first_name, user_last_name, user_date_of_birth) VALUES ('$user_name', '$user_password', '$user_first_name', '$user_last_name', '$user_date_of_birth')";

	if (mysqli_query($conn, $sql)) {
		echo json_encode(array('success' => true, 'id' => mysqli_insert_id($conn)));
	} else {
		http_response_code(500);
		echo json_encode(array('error' => mysqli_error($conn)));
	}
	exit;
}

if ($method === 'PUT') {
	$fld = preg_replace('/[^a-z0-9_]+/i', '', isset($_GET['fld']) ? $_GET['fld'] : '');
	$key = isset($_GET['key']) ? $_GET['key'] : '';

	$user_first_name    = mysqli_real_escape_string($conn, isset($input['user_first_name'])    ? $input['user_first_name']    : '');
	$user_last_name     = mysqli_real_escape_string($conn, isset($input['user_last_name'])     ? $input['user_last_name']     : '');
	$user_date_of_birth = mysqli_real_escape_string($conn, isset($input['user_date_of_birth']) ? $input['user_date_of_birth'] : '');

	$dob = $user_date_of_birth ? "'$user_date_of_birth'" : 'NULL';

	$safeKey = mysqli_real_escape_string($conn, $key);
	$sql = "UPDATE `$table` SET user_first_name='$user_first_name', user_last_name='$user_last_name', user_date_of_birth=$dob WHERE `$fld`='$safeKey'";
	$result = mysqli_query($conn, $sql);

	if ($result) {
		$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id, user_name, user_first_name, user_last_name, user_date_of_birth FROM `$table` WHERE `$fld`='$safeKey'"));
		echo json_encode($row);
	} else {
		http_response_code(500);
		echo json_encode(array('error' => mysqli_error($conn)));
	}
	exit;
}

echo json_encode(array('error' => 'Unsupported method'));

// close mysql connection
mysqli_close($conn);
?>
