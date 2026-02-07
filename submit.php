<?php
// submit.php - Backend script to save data to MySQL database

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$servername = "localhost";
$username = "root"; // Change as needed
$password = ""; // Change as needed
$dbname = "hacking_interface_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['name']) || !isset($input['fingerprintId'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

$name = trim($input['name']);
$fingerprintId = trim($input['fingerprintId']);

// Validate data
if (empty($name) || strlen($name) < 2) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid name. Minimum 2 characters required.'
    ]);
    exit;
}

if (empty($fingerprintId)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid fingerprint ID'
    ]);
    exit;
}

// Get user IP address
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// Sanitize inputs for database
$name = $conn->real_escape_string($name);
$fingerprintId = $conn->real_escape_string($fingerprintId);
$ipAddress = $conn->real_escape_string($ipAddress);
$userAgent = $conn->real_escape_string($userAgent);

// Insert data into database
$sql = "INSERT INTO users (name, fingerprint_id, ip_address, user_agent) 
        VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $fingerprintId, $ipAddress, $userAgent);

if ($stmt->execute()) {
    $recordId = $stmt->insert_id;
    
    // Log the submission (optional)
    error_log("New scan submission: ID=$recordId, Name=$name, FPID=$fingerprintId, IP=$ipAddress");
    
    echo json_encode([
        'success' => true,
        'message' => 'Data submitted successfully',
        'recordId' => $recordId,
        'data' => [
            'name' => $name,
            'fingerprintId' => $fingerprintId,
            'ipAddress' => $ipAddress,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error submitting data: ' . $conn->error
    ]);
}

// Close connections
$stmt->close();
$conn->close();
?>