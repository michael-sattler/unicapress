<?php
// TODO: show the API connection status and health

// Include configuration and database connection if not already included
if (!isset($mysqli)) {
    require_once __DIR__ . "/config-api/config.php";
}

function handleHealth() {
    global $mysqli;
    
    // Initialize response array
    $response = [
        'status' => 'success',
        'message' => 'All systems operational',
        'data' => [
            'version' => '1.0',a
            'timestamp' => time(),
            'checks' => [
                'database' => [
                    'status' => 'unknown',
                    'message' => ''
                ]
            ]
        ]
    ];

    // Perform database check
    // Try a simple query to verify connection is working
    if ($result = mysqli_query($mysqli, 'SELECT 1')) {
        mysqli_free_result($result);
        $response['data']['checks']['database'] = [
            'status' => 'success',
            'message' => 'Database connection successful'
        ];
    } else {
        $response['status'] = 'error';
        $response['message'] = 'System partially operational';
        $response['data']['checks']['database'] = [
            'status' => 'error',
            'message' => 'Database connection failed: ' . mysqli_error($mysqli)
        ];
    }

    return $response;
}

function handleCorsTest() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? 'none';
    
    return [
        'status' => 'success',
        'message' => 'CORS test via API routing',
        'data' => [
            'origin' => $origin,
            'method' => $_SERVER['REQUEST_METHOD'],
            'timestamp' => time(),
            'headers_sent' => headers_list()
        ]
    ];
}
?>