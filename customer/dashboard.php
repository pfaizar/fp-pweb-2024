<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;

session_start();
requireLogin();

if (isAdmin()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];
$bookings = getUserBookings($user_id);
$available_flights = getAvailableFlights();

if (isset($_GET['download']) && isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];
    $booking = array_filter($bookings, function ($b) use ($booking_id) {
        return $b['id'] == $booking_id;
    });

    if (!empty($booking)) {
        $booking = reset($booking);

        $dompdf = new Dompdf();

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                h1 { color: #4F46E5; font-size: 24px; margin-bottom: 20px; }
                h2 { font-size: 20px; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                td { padding: 10px; border-bottom: 1px solid #eee; }
                .label { font-weight: bold; width: 150px; }
            </style>
        </head>
        <body>
            <h1>Tiket Penerbangan</h1>
            <h2>Penerbangan ' . htmlspecialchars($booking['flight_number']) . '</h2>
            
            <table>
                <tr>
                    <td class="label">Penumpang :</td>
                    <td>' . htmlspecialchars($_SESSION['name']) . '</td>
                </tr>
                <tr>
                    <td class="label">Keberangkatan :</td>
                    <td>' . htmlspecialchars($booking['departure_city']) . '</td>
                </tr>
                <tr>
                    <td class="label">Tujuan :</td>
                    <td>' . htmlspecialchars($booking['arrival_city']) . '</td>
                </tr>
                <tr>
                    <td class="label">Waktu Keberangkatan :</td>
                    <td>' . htmlspecialchars($booking['departure_time']) . '</td>
                </tr>
                <tr>
                    <td class="label">Waktu Kedatangan :</td>
                    <td>' . htmlspecialchars($booking['arrival_time']) . '</td>
                </tr>
                <tr>
                    <td class="label">Jumlah Kursi :</td>
                    <td>' . htmlspecialchars($booking['seats_booked']) . '</td>
                </tr>
            </table>
        </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('ticket_' . $booking['flight_number'] . '.pdf', [
            'Attachment' => true
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | TerbangYuk!</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                        <a href="index.php"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">

                            Beranda
                        </a>
                        <a href="dashboard.php"
                            class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="/auth/logout.php" class="text-gray-500 hover:text-red-500">Keluar</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Dashboard Pengguna</h2>

        <div class="flex gap-6 lg:flex-row flex-col-reverse">
            <div class="space-y-6 w-full">
                <?php foreach ($bookings as $booking): ?>
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Penerbangan <?php echo htmlspecialchars($booking['flight_number']); ?>
                                </h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                    Dipesan pada <?php echo formatDateTime($booking['booking_date']); ?>
                                </p>
                            </div>
                            <a href="?download=true&booking_id=<?php echo $booking['id']; ?>"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Unduh Tiket
                            </a>
                        </div>
                        <div class="border-t border-gray-200">
                            <dl>
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Keberangkatan</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <?php echo htmlspecialchars($booking['departure_city']); ?>
                                        <br>
                                        <span
                                            class="text-gray-500"><?php echo formatDateTime($booking['departure_time']); ?></span>
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Tujuan</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <?php echo htmlspecialchars($booking['arrival_city']); ?>
                                        <br>
                                        <span
                                            class="text-gray-500"><?php echo formatDateTime($booking['arrival_time']); ?></span>
                                    </dd>
                                </div>
                                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Jumlah Kursi</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        <?php echo $booking['seats_booked']; ?> Kursi
                                    </dd>
                                </div>
                                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    <?php echo strtotime($booking['departure_time']) > time() ?
                                        'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                            <?php echo strtotime($booking['departure_time']) > time() ? 'Mendatang' : 'Terlewat'; ?>
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($bookings)): ?>
                    <div class="text-center py-12 w-full">
                        <h3 class="text-lg font-medium text-gray-900">Tiket tidak ditemukan</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Belum ada tiket yang dipesan.
                            <a href="index.php" class="text-indigo-600 hover:text-indigo-500">Cari penerbangan terbaru</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="lg:w-[25vw] w-full">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Pesanan
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            <?php echo count($bookings); ?> Pesanan
                        </dd>
                    </div>
                </div>

                <h2 class="text-lg leading-6 font-medium text-gray-900 mb-4 mt-6">Pesanan Terbaru</h2>
                <div class="">
                    <?php foreach (array_slice($bookings, 0, 1) as $booking): ?>
                        <div class="bg-white shadow rounded-lg p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-sm font-medium text-indigo-600">
                                    Penerbangan <?php echo htmlspecialchars($booking['flight_number']); ?>
                                </div>
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full <?php echo strtotime($booking['departure_time']) > time() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo strtotime($booking['departure_time']) > time() ? 'Mendatang' : 'Terlewat'; ?>
                                </span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Keberangkatan</span>
                                    <span
                                        class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($booking['departure_city']); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Tujuan</span>
                                    <span
                                        class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($booking['arrival_city']); ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Tanggal</span>
                                    <span
                                        class="text-sm font-medium text-gray-900"><?php echo formatDateTime($booking['departure_time']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
</body>

</html>