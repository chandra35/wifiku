<!DOCTYPE html>
<html>
<head>
    <title>Test Monitor JavaScript</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Test Router Status AJAX</h1>
    <div id="results"></div>
    <button onclick="testStatus()">Test Status</button>
    
    <script>
        const routerId = '{{ $router->id }}';
        
        // Setup CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        function testStatus() {
            console.log('Testing router status...');
            $('#results').html('Testing...');
            
            $.get('/routers/' + routerId + '/status')
                .done(function(data) {
                    console.log('Success:', data);
                    $('#results').html('<pre>' + JSON.stringify(data, null, 2) + '</pre>');
                })
                .fail(function(xhr) {
                    console.error('Failed:', xhr);
                    $('#results').html('Error: ' + xhr.status + ' - ' + xhr.statusText);
                });
        }
        
        // Auto test on load
        $(document).ready(function() {
            console.log('Router ID:', routerId);
            testStatus();
        });
    </script>
</body>
</html>
