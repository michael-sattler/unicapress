<?php
require_once __DIR__ . "/../config-app/config.php"; // App config. should in turn pull in project config
require_once APP_PUBLIC_PATH . "/app/includes/functions.php";

adminonly(); // this will redirect to adminlogin.php if not logged in

$pagetitle = "API Tester";

// Function to get API routes
function getApiRoutes() {
    // Get routes from the shared configuration file
    $routesFile = APP_PUBLIC_PATH . "/api/config-api/routes.php";
    if (!file_exists($routesFile)) {
        return [];
    }

    // Load the routes array
    $routes = require $routesFile;
    
    if (!is_array($routes)) {
        return [];
    }

// Get organized routes
$apiRoutes = getApiRoutes();

// Start output buffering to capture page content
ob_start();
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2>API Endpoint Tester</h2>
            <div class="card">
                <div class="card-body">
                    <form id="apiTestForm" class="mb-4">
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <select class="form-select" id="httpMethod">
                                    <option value="GET">GET</option>
                                    <option value="POST">POST</option>
                                    <option value="PUT">PUT</option>
                                    <option value="DELETE">DELETE</option>
                                    <option value="OPTIONS">OPTIONS</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="endpoint" placeholder="Endpoint (e.g., /content/narratives/list)">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Send Request</button>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-secondary w-100" id="saveTest">Save Test</button>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Request Headers</label>
                                <textarea class="form-control font-monospace" id="headers" rows="3">Content-Type: application/json
Authorization: Bearer <?php echo htmlspecialchars(defined('API_TOKEN') ? API_TOKEN : ''); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Request Body (JSON)</label>
                                <textarea class="form-control font-monospace" id="requestBody" rows="3">{
    
}</textarea>
                            </div>
                        </div>
                    </form>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Response</span>
                                    <span id="responseStatus" class="badge bg-secondary">No Response</span>
                                </div>
                                <div class="card-body">
                                    <pre id="response" class="language-json"><code>// Response will appear here</code></pre>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    Saved Tests
                                </div>
                                <div class="card-body">
                                    <div class="list-group" id="savedTests">
                                        <!-- Saved tests will be populated here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    Available Endpoints <small>dynsamically sourced from api/config-api/routes.php</small>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($apiRoutes as $category => $routes): ?>
                        <div class="col-md-3 mb-4">
                            <h5><?php echo htmlspecialchars($category); ?></h5>
                            <div class="list-group">
                                <?php foreach ($routes as $route): ?>
                                <a href="#" class="list-group-item list-group-item-action endpoint-link" 
                                   data-method="<?php echo htmlspecialchars($route['method']); ?>" 
                                   data-endpoint="/<?php echo htmlspecialchars($route['path']); ?>">
                                    <?php echo htmlspecialchars($route['method'] . ' /' . $route['path']); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    API Documentation
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h5>The interface has several key sections:</h5>
                            <ul>
                                <li>Request Builder (top section):</li>
                                <ul>
                                <li>HTTP Method dropdown (GET, POST, PUT, DELETE)</li>
                                <li>Endpoint input field</li>
                                <li>"Send Request" button</li>
                                <li>"Save Test" button to save frequently used requests</li>
                            </ul>
                            <li>Headers and Body (middle section):</li>
                                <ul>
                                    <li>Request Headers textarea (pre-filled with Content-Type and API Token)</li>
                                    <li>Request Body textarea for JSON data (used for POST/PUT requests)</li>
                                </ul>
                            <li>Response (bottom left):</li>
                                <ul>
                                    <li>Shows the API response with status code</li>
                                    <li>Displays formatted JSON response data</li>
                                </ul>
                            <li>Saved Tests (bottom right):</li>
                                <ul>
                                    <li>List of your previously saved API tests</li>
                                    <li>Click any saved test to load it into the form</li>
                                </ul>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h5>To make an API request:</h5>  
                                <ul>
                                <li>For a GET request:</li>
                                    <ul>
                                    <li>Select "GET" from the method dropdown</li>
                                    <li>Enter an endpoint like "/content/narratives/list"</li>
                                    <li>Make sure your API token is set in the headers</li>
                                    <li>Click "Send Request"</li>
                                </ul>
                                <li>For a POST request:</li>
                                    <ul>
                                    <li>Select "POST" from the method dropdown</li>
                                    <li>Enter an endpoint like "/content/narratives/create"</li>
                                    <li>Add your JSON data to the Request Body, e.g.:</li>
                                        <li>{
                                            "title": "My New Narrative",
                                            "description": "A test narrative"
                                        }</li>
                                    </ul>
                                    <li>Click "Send Request"</li>
                                <li>The "Available Endpoints" section at the bottom shows all available API endpoints organized by category. Click any endpoint to automatically fill in the method and endpoint fields.</li>
                                </ul>
                        </div>
                        <div class="col-md-4">
                            <h5>Saving Tests</h5>
                            <ul>
                                
                                <li>To save a test for reuse:</li>
                                    <ul>
                                    <li>Set up your request (method, endpoint, headers, body)</li>
                                    <li>Click "Save Test"</li>
                                    <li>Enter a name for the test</li>
                                    <li>The test will appear in the "Saved Tests" section</li>
                                    </ul>
                                </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add API base URL to the page
const API_BASE_URL = <?php echo json_encode(defined('APP_API_URL') ? APP_API_URL : 'http://api.myamanuensislocal.com'); ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Load saved tests from localStorage
    loadSavedTests();
    
    // Handle form submission
    document.getElementById('apiTestForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        await sendRequest();
    });
    
    // Handle endpoint links
    document.querySelectorAll('.endpoint-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('httpMethod').value = this.dataset.method;
            document.getElementById('endpoint').value = this.dataset.endpoint;
        });
    });
    
    // Handle save test button
    document.getElementById('saveTest').addEventListener('click', function() {
        const testName = prompt('Enter a name for this test:');
        if (testName) {
            saveTest(testName);
        }
    });
});

async function sendRequest() {
    const method = document.getElementById('httpMethod').value;
    let endpoint = document.getElementById('endpoint').value;
    const headers = parseHeaders(document.getElementById('headers').value);
    const body = document.getElementById('requestBody').value;
    
    const responseStatus = document.getElementById('responseStatus');
    const responseElement = document.getElementById('response');
    
    try {
        responseStatus.textContent = 'Sending...';
        responseStatus.className = 'badge bg-info';
        
        // Clean up the endpoint and API base URL combination
        endpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;
        const apiUrl = API_BASE_URL.endsWith('/') ? API_BASE_URL : API_BASE_URL + '/';
        const fullUrl = apiUrl + endpoint;
        
        // Log request details for debugging
        console.log('Request Details:', {
            url: fullUrl,
            method: method,
            headers: headers,
            body: method !== 'GET' ? body : undefined
        });
        
        const response = await fetch(fullUrl, {
            method: method,
            headers: headers,
            body: method !== 'GET' ? body : undefined
        });
        
        // Get the raw text first
        const rawResponse = await response.text();
        console.log('Raw response:', rawResponse);
        
        try {
            // Try to parse as JSON
            const data = JSON.parse(rawResponse);
            responseStatus.textContent = `${response.status} ${response.statusText}`;
            responseStatus.className = `badge ${response.ok ? 'bg-success' : 'bg-danger'}`;
            responseElement.innerHTML = `<code>${JSON.stringify(data, null, 2)}</code>`;
        } catch (jsonError) {
            // If JSON parsing fails, show the raw response
            responseStatus.textContent = `${response.status} ${response.statusText} (Invalid JSON)`;
            responseStatus.className = 'badge bg-warning';
            responseElement.innerHTML = `<code class="text-danger">Raw Response (Invalid JSON):\n${rawResponse}</code>`;
        }
        
    } catch (error) {
        responseStatus.textContent = 'Error';
        responseStatus.className = 'badge bg-danger';
        responseElement.innerHTML = `<code class="text-danger">${error.message}</code>`;
    }
}

function parseHeaders(headerString) {
    const headers = {};
    headerString.split('\n').forEach(line => {
        const [key, value] = line.split(':').map(part => part.trim());
        if (key && value) {
            headers[key] = value;
        }
    });
    return headers;
}

function saveTest(name) {
    const test = {
        name: name,
        method: document.getElementById('httpMethod').value,
        endpoint: document.getElementById('endpoint').value,
        headers: document.getElementById('headers').value,
        body: document.getElementById('requestBody').value
    };
    
    let savedTests = JSON.parse(localStorage.getItem('apiTests') || '[]');
    savedTests.push(test);
    localStorage.setItem('apiTests', JSON.stringify(savedTests));
    
    loadSavedTests();
}

function loadSavedTests() {
    const savedTests = JSON.parse(localStorage.getItem('apiTests') || '[]');
    const container = document.getElementById('savedTests');
    
    container.innerHTML = savedTests.map(test => `
        <a href="#" class="list-group-item list-group-item-action saved-test">
            <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1">${test.name}</h6>
                <small class="text-muted">${test.method}</small>
            </div>
            <p class="mb-1">${test.endpoint}</p>
        </a>
    `).join('');
    
    // Add click handlers for saved tests
    container.querySelectorAll('.saved-test').forEach((element, index) => {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            const test = savedTests[index];
            document.getElementById('httpMethod').value = test.method;
            document.getElementById('endpoint').value = test.endpoint;
            document.getElementById('headers').value = test.headers;
            document.getElementById('requestBody').value = test.body;
        });
    });
}
</script>

<style>
.font-monospace {
    font-family: monospace;
}
pre {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
}
.endpoint-link {
    font-family: monospace;
    font-size: 0.9rem;
}
</style>

<?php
// Capture the page content and store it in a variable
$page_content = ob_get_clean();

// Include the layout which will use $page_content
require_once __DIR__ . '/elements/admin-layout.php';
?> 