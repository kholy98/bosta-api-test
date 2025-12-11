<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Shipment</title>
</head>
<body>
    <h1>Track Shipment</h1>
    <form action="/track" method="POST">
        @csrf
        <label>Tracking Number:</label>
        <input name="tracking_number" required><br>
        <button type="submit">Track</button>
    </form>
    <a href="/">Back</a>
</body>
</html>