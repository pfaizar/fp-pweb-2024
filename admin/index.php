<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

session_start();
requireAdmin();

$user_name = $_SESSION['name'];

$conn = getDBConnection();
$stmt = $conn->query("
    SELECT 
        COUNT(DISTINCT b.id) as total_bookings,
        (SELECT SUM(total_price) FROM bookings) as total_revenue,
        COUNT(DISTINCT f.id) as active_flights,
        COUNT(DISTINCT u.id) as total_customers
    FROM bookings b
    JOIN flights f ON f.departure_time > NOW()
    LEFT JOIN users u ON u.role = 'customer'
");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | TerbangYuk!</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="icon" type="image/svg+xml" href="../assets/images/logo.svg">
</head>

<body class="bg-gray-50">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-bold text-indigo-600">TerbangYuk!</span>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/"
                            class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="users.php"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Pengguna
                        </a>
                        <a href="tickets.php"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Penerbangan
                        </a>
                        <a href="summary.php"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Laporan
                        </a>
                    </div>
                </div>

                <div class="flex items-center">
                    <a href="/auth/logout.php" class="text-gray-500 hover:text-red-500">Keluar</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h2 class="text-lg font-medium text-gray-900 mb-6">Welcome,
            <?php echo htmlspecialchars($user_name); ?>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm font-medium">Total Pesanan</h3>
                <p class="mt-2 text-3xl font-semibold text-gray-900"><?php echo $stats['total_bookings']; ?></p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm font-medium">Total Penghasilan</h3>
                <p class="mt-2 text-3xl font-semibold text-gray-900">
                    Rp. <?php echo formatPrice($stats['total_revenue']); ?></p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm font-medium">Penerbangan Aktif</h3>
                <p class="mt-2 text-3xl font-semibold text-gray-900"><?php echo $stats['active_flights']; ?>
                    Penerbangan</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-gray-500 text-sm font-medium">Total Pelanggan</h3>
                <p class="mt-2 text-3xl font-semibold text-gray-900"><?php echo $stats['total_customers']; ?>
                    Pengguna</p>
            </div>
        </div>

        <div class="mt-8 gap-8">
            <!-- Recent Bookings -->
            <div class="rounded-lg">
                <h2 class="text-lg font-medium text-gray-900">Pesanan Terbaru</h2>
                <div class="mt-4">
                    <?php
                    $stmt = $conn->query("
                        SELECT b.*, f.flight_number, u.name as customer_name 
                        FROM bookings b
                        JOIN flights f ON b.flight_id = f.id
                        JOIN users u ON b.user_id = u.id
                        ORDER BY b.booking_date DESC
                        LIMIT 10
                    ");
                    $recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <div class="space-y-4">
                        <?php foreach ($recent_bookings as $booking): ?>
                            <div class="border-b pb-4 flex justify-between w-full">
                                <div class="gap-8 flex">
                                    <p class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($booking['customer_name']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        [<?php echo htmlspecialchars($booking['flight_number']); ?>]
                                        <?php echo $booking['seats_booked']; ?> Kursi —
                                        Rp. <?php echo formatPrice($booking['total_price']); ?>
                                    </p>
                                </div>
                                <p class="text-sm text-gray-500"><?php echo formatDateTime($booking['booking_date']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>