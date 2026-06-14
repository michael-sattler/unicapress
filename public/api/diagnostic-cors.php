<!DOCTYPE html>
<html>
<head>
    <title>CORS Test</title>
</head>
<body>
    <h1>CORS Test for API</h1>
    <button onclick="testCors()">Test CORS</button>
    <div id="result"></div>

    <script>
    async function testCors() {
        const result = document.getElementById('result');
        result.innerHTML = 'Testing...';
        
        try {
            // Test the API endpoint that was failing
            const response = await fetch('https://.'.APP_API_URL.'/api/diagnostic-cors.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    test: 'cors'
                })
            });
            
            const data = await response.json();
            result.innerHTML = `
                <h3>✅ CORS Test Successful!</h3>
                <p><strong>Status:</strong> ${response.status}</p>
                <p><strong>Response:</strong></p>
                <pre>${JSON.stringify(data, null, 2)}</pre>
            `;
        } catch (error) {
            result.innerHTML = `
                <h3>❌ CORS Test Failed</h3>
                <p><strong>Error:</strong> ${error.message}</p>
                <p>This likely means CORS headers are still not properly configured.</p>
            `;
        }
    }
    </script>
</body>
</html> 