<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Shipment</title>
</head>
<body>
    <h1>Create Shipment</h1>
    <form action="/shipments" method="POST">
        @csrf
        <label>Receiver First Name:</label>
        <input name="receiver_first_name" required><br>
        <label>Receiver Last Name:</label>
        <input name="receiver_last_name" required><br>
        <label>Receiver Phone:</label>
        <input name="receiver_phone" required><br>
        <label>Receiver Email (optional):</label>
        <input name="receiver_email" type="email"><br>
        <label>Building Number:</label>
        <input name="building_number" type="number" required><br>
        <label>Floor (optional):</label>
        <input name="floor"><br>
        <label>Apartment (optional):</label>
        <input name="apartment"><br>
        <label>Address Line:</label>
        <input name="first_line" required><br>
        <label>City (e.g., EG-05):</label>
        <input name="city" required><br>
        <label>Zone (e.g., Dakahlia):</label>
        <input name="zone" required><br>
        <label>Notes:</label>
        <input name="notes"><br>
        <label>Cash on Delivery:</label>
        <input name="cod" type="number" step="0.01" required><br>
        <label>Business Reference (optional):</label>
        <input name="business_reference"><br>
        <button type="submit">Create Shipment</button>
    </form>
    @if(session('success'))
        <p>{{ session('success') }}</p>
    @endif
    <a href="/">Back</a>
</body>
</html>