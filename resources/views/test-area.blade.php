<!DOCTYPE html>
<html>
<head>
    <title>Test Area API</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Test Area API</h1>
    
    <div>
        <button onclick="testProvincesAPI()">Test Provinces API</button>
        <div id="provinces-result"></div>
    </div>
    
    <script>
    function testProvincesAPI() {
        $.ajax({
            url: '/api/areas/provinces',
            type: 'GET',
            success: function(response) {
                console.log('Provinces API Response:', response);
                $('#provinces-result').html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                $('#provinces-result').html('Error: ' + xhr.status + ' - ' + xhr.statusText);
            }
        });
    }
    </script>
</body>
</html>
