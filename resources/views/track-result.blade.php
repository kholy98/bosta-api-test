<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Result</title>
</head>
<body>
    <h1>Tracking Result</h1>
    <p>Tracking Number: {{ $data['trackingNumber'] }}</p>
    <p>Status: {{ $data['state']['status'] }}</p>
    <p>State Value: {{ $data['state']['value'] }}</p>
    <p>Location: {{ $data['state']['location'] ?? 'N/A' }}</p>
    <p>Timestamp: {{ $data['state']['timestamp'] ?? 'N/A' }}</p>
    <!-- Add more fields as needed -->
    <a href="/track-shipment">Track Another</a> | <a href="/">Home</a>
</body>
</html>