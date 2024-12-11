<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;

session_start();
requireAdmin();

$conn = getDBConnection();

$stmt = $conn->query("
    SELECT 
        f.flight_number,
        f.departure_city,
        f.arrival_city,
        f.departure_time,
        f.total_seats,
        f.available_seats,
        COUNT(b.id) as total_bookings,
        SUM(b.seats_booked) as seats_sold,
        SUM(b.total_price) as revenue
    FROM flights f
    LEFT JOIN bookings b ON f.id = b.flight_id
    GROUP BY f.id
    ORDER BY f.departure_time DESC
");
$summaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['export_pdf'])) {
    $dompdf = new Dompdf();

    $html = '
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; }
        h2 { color: #333; }
    </style>
    <h2>Laporan Penerbangan</h2>
    <p>Dibuat: ' . date('Y-m-d H:i:s') . ' UTC</p>
    <table>
        <tr>
            <th>ID Penerbangan</th>
            <th>Rute</th>
            <th>Keberangkatan</th>
            <th>Total Pesanan</th>
            <th>Jumlah Kursi</th>
            <th>Pendapatan</th>
        </tr>';

    foreach ($summaries as $summary) {
        $html .= '<tr>
            <td>' . $summary['flight_number'] . '</td>
            <td>' . $summary['departure_city'] . ' - ' . $summary['arrival_city'] . '</td>
            <td>' . formatDateTime($summary['departure_time']) . '</td>
            <td>' . $summary['total_bookings'] . ' Pesanan</td>
            <td>' . $summary['seats_sold'] . '/' . $summary['total_seats'] . '</td>
            <td>Rp. ' . formatPrice($summary['revenue']) . '</td>
        </tr>';
    }

    $html .= '</table>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream("laporan-penerbangan.pdf");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Summaries - SkyTickets Admin</title>
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
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
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
                            class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
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
        <div class="w-full flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Laporan Penerbangan
            </h2>
            <div class="flex justify-end mb-4">

                <form action="summary.php" method="POST">
                    <button type="submit" name="export_pdf"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Unduh PDF
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            ID Penerbangan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rute
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Keberangkatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pesanan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Kursi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($summaries as $summary): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($summary['flight_number']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($summary['departure_city']); ?> -
                                <?php echo htmlspecialchars($summary['arrival_city']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo formatDateTime($summary['departure_time']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $summary['total_bookings']; ?> Pesanan
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $summary['seats_sold']; ?>/<?php echo $summary['total_seats']; ?>
                                (<?php echo round(($summary['seats_sold'] / $summary['total_seats']) * 100); ?>%)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Rp. <?php echo formatPrice($summary['revenue']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>