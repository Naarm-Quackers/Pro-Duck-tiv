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
		echo json_encode([
			'success' => true,
			'id' => mysqli_insert_id($conn)
		]);
	} else {
		echo json_encode([
			'success' => false,
			'error' => mysqli_insert_id($conn)
		]);
	}
	exit;
}


// // retrieve the search key field name and value from the path
// $fld = preg_replace('/[^a-z0-9_]+/i','',array_shift($request));
// $key = array_shift($request);
// // retrieve the data to prepare set values
// if (isset ($input))  {
// 	$columns = preg_replace('/[^a-z0-9_]+/i','',array_keys($input));
// 	$values = array_map(function ($value) use ($conn) {
// 		if ($value===null) return null;
// 		return mysqli_real_escape_string($conn,(string)$value);
// 	},array_values($input));

// }

// // create SQL
// switch ($method) {
//   case 'POST':
//   	$sql = "select * from `$table` WHERE username='".$input['username']."' and password ='".$input['password']."'"; break;
// }

// // execute SQL statement
// $result = mysqli_query($conn,$sql);
// if ($result) {
// 	if ($method == 'POST') {
// 			echo json_encode(mysqli_fetch_object($result));

// 	} else {
// 		echo mysqli_affected_rows($conn);
// 	}
// }

echo json_encode(['error' => 'Unsupported method']);

// close mysql connection
mysqli_close($conn);
?>
