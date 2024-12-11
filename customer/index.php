<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

session_start();
requireLogin();

$available_flights = getAvailableFlights();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['flight_id']) && isset($_POST['seats'])) {

    $flight_id = $_POST['flight_id'];
    $seats = (int) $_POST['seats'];
    $user_id = $_SESSION['user_id'];

    if ($seats < 1) {
        $error_message = "Jumlah kursi harus lebih dari 0.";
    } else if ($seats >= 1) {
        $conn = getDBConnection();

        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare("SELECT available_seats, price FROM flights WHERE id = ? FOR UPDATE");
            $stmt->execute([$flight_id]);
            $flight = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($flight && $flight['available_seats'] >= $seats) {
                $stmt = $conn->prepare("INSERT INTO bookings (user_id, flight_id, seats_booked, total_price, booking_date) VALUES (?, ?, ?, ?, NOW())");
                $total_price = $seats * $flight['price'];
                $stmt->execute([$user_id, $flight_id, $seats, $total_price]);

                $stmt = $conn->prepare("UPDATE flights SET available_seats = available_seats - ? WHERE id = ?");
                $stmt->execute([$seats, $flight_id]);

                $conn->commit();

                $format_total_price = formatPrice($total_price);

                $success_message = "Berhasil memesan $seats kursi dengan harga Rp. $format_total_price";
            } else {
                $error_message = "Kursi tidak cukup.";
                $conn->rollBack();
            }
        } catch (Exception $e) {
            $conn->rollBack();
            $error_message = "Internal Error.";
        }
    }


}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | TerbangYuk!</title>
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
                            class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Beranda
                        </a>
                        <a href="dashboard.php"
                            class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
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

    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="w-full flex justify-between items-center">

            <h2 class="text-lg font-medium text-gray-900 mb-4">Penerbangan Tersedia</h2>
            <p class="text-sm text-gray-500"><?php echo count($available_flights); ?> Tersedia
            </p>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="mb-4 rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800"><?php echo htmlspecialchars($success_message); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="mb-4 rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800"><?php echo htmlspecialchars($error_message); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($available_flights as $flight): ?>
                <div class="bg-white overflow-hidden shadow rounded-lg divide-y divide-gray-200">
                    <div class="px-4 py-5 sm:px-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">
                                Penerbangan <?php echo htmlspecialchars($flight['flight_number']); ?>
                            </h3>
                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                <?php echo $flight['available_seats']; ?> kursi tersedia
                            </span>
                        </div>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="text-sm text-gray-500">Keberangkatan</div>
                                    <div class="font-medium"><?php echo htmlspecialchars($flight['departure_city']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo formatDateTime($flight['departure_time']); ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-500">Tujuan</div>
                                    <div class="font-medium"><?php echo htmlspecialchars($flight['arrival_city']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo formatDateTime($flight['arrival_time']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-bold text-indigo-600">Rp.
                                    <?php echo formatPrice($flight['price']); ?></span>
                            </div>
                            <form method="POST" action="index.php" class="mt-4">
                                <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                <div class="flex space-x-4">
                                    <div class="flex-1">
                                        <div>
                                            <label class="sr-only">Pesan Kursi</label>
                                            <input type="number" name="seats" required min="1"
                                                class="mt-1 text-sm block w-full rounded-md border-gray-500 shadow-sm border px-4 py-2 focus:outline-non focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        </div>
                                    </div>
                                    <button type="submit"
                                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Pesan Tiket
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (empty($available_flights)): ?>
            <div class="text-center py-12">
                <h3 class="text-lg font-medium text-gray-900">Tidak ada penerbangan yang ditemukan</h3>
                <p class="mt-2 text-sm text-gray-500">Datang kembali nanti untuk melihat jadwal penerbangan terbaru.</p>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>