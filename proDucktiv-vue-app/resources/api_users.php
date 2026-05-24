<?php
header('Content-Type: application/json');

// connect to the mysql database, provide the appropriate credentials
//$conn = mysqli_connect('localhost', 'root', '', 'your-database');
$conn = mysqli_connect('feenix-mariadb.swin.edu.au', 's105341089', '080805', 's105341089_db');

// initialise the table name accordingly
$table = "pdt_users";

// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
// $request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$input = json_decode(file_get_contents('php://input'),true);  // json string to associative array(true)

mysqli_set_charset($conn,'utf8');

if ($method === 'GET') {
	$sql = "SELECT * FROM `$table`";
	$result = mysqli_query($conn, $sql);

	$rows = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}

	echo json_encode($rows);
	exit;
}

if ($method === 'POST') {
	$user_name = mysqli_real_escape_string($conn, $input['user_name'] ?? '');
	$user_password = mysqli_real_escape_string($conn, $input['user_password'] ?? '');
	$user_first_name = mysqli_real_escape_string($conn, $input['user_first_name'] ?? '');
	$user_last_name = mysqli_real_escape_string($conn, $input['user_last_name'] ?? '');
	$user_date_of_birth = mysqli_real_escape_string($conn, $input['user_date_of_birth'] ?? '');

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

echo json_encode(['error' => 'Unsupported method']);

// close mysql connection
mysqli_close($conn);
?>
