<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

session_start();
requireAdmin();

$conn = getDBConnection();

if (isset($_POST['create_flight'])) {
    $flight_number = generateFlightNumber();
    $stmt = $conn->prepare("
        INSERT INTO flights (
            flight_number, departure_city, arrival_city, 
            departure_time, arrival_time, total_seats, 
            available_seats, price
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $flight_number,
        $_POST['departure_city'],
        $_POST['arrival_city'],
        $_POST['departure_time'],
        $_POST['arrival_time'],
        $_POST['total_seats'],
        $_POST['total_seats'],
        $_POST['price']
    ]);

    header('Location: tickets.php?message=Penerbangan berhasil ditambahkan');
    exit;
}

if (isset($_POST['delete_flight'])) {
    $stmt = $conn->prepare("DELETE FROM flights WHERE id = ?");
    $stmt->execute([$_POST['flight_id']]);
    header('Location: tickets.php?message=Penerbangan berhasil dihapus');
    exit;
}

$stmt = $conn->query("
    SELECT * FROM flights 
    ORDER BY departure_time DESC
");
$flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penerbangan | TerbangYuk!</title>
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
                            class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
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
        <?php if (isset($_GET['message'])): ?>
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Create Flight Form -->
        <?php if (isset($_GET['action']) && $_GET['action'] === 'new'): ?>
            <div class="mb-6 bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Tambahkan Penerbangan Baru</h2>
                <form action="tickets.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kota Keberangkatan</label>
                            <input type="text" name="departure_city" required
                                class="mt-1 text-sm block w-full rounded-md border-gray-500 shadow-sm border px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kota Kedatangan</label>
                            <input type="text" name="arrival_city" required
                                class="mt-1 text-sm block w-full rounded-md border-gray-500 shadow-sm border px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Waktu Keberangkatan</label>
                            <input type="datetime-local" name="departure_time" required
                                class="mt-1 text-sm block w-full rounded-md border-gray-500 shadow-sm border px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Waktu Kedatangan</label>
                            <input type="datetime-local" name="arrival_time" required
                                class="mt-1 text-sm block w-full rounded-md border-gray-500 shadow-sm border px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jumlah Kursi Tersedia</label>
                            <input type="number" name="total_seats" required min="1"
                                class="mt-1 text-sm block w-full rounded-md border-gray-500 shadow-sm border px-4 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Harga/Kursi (Rupiah)</label>
                            <input type="number" name="price" required min="0" step="0.01"
                                class="mt-1 text-sm block w-full rounded-md border-gray-500 shadow-sm border px-4 py-2">
                        </div>
                    </div>
                    <button type="submit" name="create_flight"
                        class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Tambahkan
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="w-full flex justify-between items-center">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Daftar Penerbangan Tersedia
            </h2>
            <?php if (isset($_GET['action']) && $_GET['action'] === 'new'): ?>
                <div class="flex justify-end mb-4">
                    <a href="?"
                        class="px-4 py-2 border-2 border-indigo-600 rounded-md shadow-sm text-sm font-medium text-indigo-600 bg-white hover:border-indigo-800 hover:text-indigo-800">
                        Tutup
                    </a>
                </div>
            <?php else: ?>
                <div class="flex justify-end mb-4">
                    <a href="?action=new"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Tambah Penerbangan Baru
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr> 
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID Penerbangan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RUTE
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Keberangkatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kedatangan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Kursi
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga/Kursi
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($flights as $flight): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($flight['flight_number']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo htmlspecialchars($flight['departure_city']); ?> -
                                <?php echo htmlspecialchars($flight['arrival_city']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo formatDateTime($flight['departure_time']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo formatDateTime($flight['arrival_time']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $flight['available_seats']; ?>/<?php echo $flight['total_seats']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                Rp. <?php echo formatPrice($flight['price']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <form action="tickets.php" method="POST" class="inline"
                                    onsubmit="return confirm('Are you sure you want to delete this flight?');">
                                    <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                    <button type="submit" name="delete_flight"
                                        class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>