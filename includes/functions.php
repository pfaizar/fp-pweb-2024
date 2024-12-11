<?php
function formatPrice($price)
{
    return number_format($price, 0);
}

function formatDateTime($datetime)
{
    return date('M d, Y h:i A', strtotime($datetime));
}

function generateFlightNumber()
{
    return 'FL' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

function uploadPhoto($file)
{
    $target_dir = "../assets/uploads/";
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    if ($file["size"] > 5000000) { 
        return false;
    }

    $allowed_types = ['jpg', 'jpeg', 'png'];
    if (!in_array($file_extension, $allowed_types)) {
        return false;
    }

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    }
    return false;
}

function getAvailableFlights()
{
    $conn = getDBConnection();
    $stmt = $conn->query("
        SELECT * FROM flights 
        WHERE available_seats > 0 
        AND departure_time > NOW()
        ORDER BY departure_time ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserBookings($user_id)
{
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT b.*, f.* 
        FROM bookings b
        JOIN flights f ON b.flight_id = f.id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}