<!-- pages/dashboard.php -->
<?php
// Set filter bulan (default: bulan ini)
$selected_month = isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m');

// 1. AMBIL DATA METRIK ATAS
$stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah), 0) as total FROM pemasukan WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?");
$stmt->execute([$selected_month]);
$total_pemasukan = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah), 0) as total FROM pengeluaran WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?");
$stmt->execute([$selected_month]);
$total_pengeluaran = $stmt->fetch()['total'];

$saldo = $total_pemasukan - $total_pengeluaran;

$stmt = $pdo->query("SELECT COUNT(id) as total FROM pelanggan WHERE status = 'Aktif'");
$pelanggan_aktif = $stmt->fetch()['total'];

// 2. AMBIL DATA UNTUK GRAFIK (LINE & BAR CHART)
// Kita buat array default untuk tanggal 1-31 jika data kosong agar grafik tetap terbentuk garis sumbunya
$chart_labels = [];
$chart_pemasukan = [];
$chart_pengeluaran = [];

$days_in_month = cal_days_in_month(CAL_GREGORIAN, date('m', strtotime($selected_month)), date('Y', strtotime($selected_month)));
for ($i = 1; $i <= $days_in_month; $i++) {
    $date_str = $selected_month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
    $chart_labels[] = $i . ' ' . date('M', strtotime($selected_month));
    
    // Pemasukan per hari
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah), 0) as total FROM pemasukan WHERE tanggal = ?");
    $stmt->execute([$date_str]);
    $chart_pemasukan[] = $stmt->fetch()['total'];

    // Pengeluaran per hari
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah), 0) as total FROM pengeluaran WHERE tanggal = ?");
    $stmt->execute([$date_str]);
    $chart_pengeluaran[] = $stmt->fetch()['total'];
}

// 3. AMBIL DATA UNTUK DONUT CHART (KATEGORI PENGELUARAN)
// Sekarang diganti mengambil data dari keterangan secara otomatis
$stmt = $pdo->prepare("
    SELECT keterangan as nama_kategori, COALESCE(SUM(jumlah), 0) as total 
    FROM pengeluaran 
    WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?
    GROUP BY keterangan HAVING total > 0 LIMIT 6
");
$stmt->execute([$selected_month]);
$kategori_data = $stmt->fetchAll();

$donut_labels = [];
$donut_data = [];
foreach ($kategori_data as $row) {
    $donut_labels[] = $row['nama_kategori'];
    $donut_data[] = $row['total'];
}

// 4. AMBIL TRANSAKSI TERAKHIR (Gabungan)
$stmt = $pdo->prepare("
    SELECT tanggal, keterangan, 'Pemasukan' as tipe, jumlah FROM pemasukan WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?
    UNION ALL
    SELECT tanggal, keterangan, 'Pengeluaran' as tipe, jumlah FROM pengeluaran WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?
    ORDER BY tanggal DESC LIMIT 5
");
$stmt->execute([$selected_month, $selected_month]);
$transaksi_terakhir = $stmt->fetchAll();

// 5. AMBIL PELANGGAN TERBARU
$stmt = $pdo->query("SELECT * FROM pelanggan ORDER BY id DESC LIMIT 5");
$pelanggan_terbaru = $stmt->fetchAll();
?>

<div class="p-4 sm:p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <form method="GET" action="" class="mt-2 sm:mt-0 flex items-center gap-2">
            <input type="hidden" name="page" value="dashboard">
            <input type="month" name="bulan" value="<?= $selected_month ?>" class="px-4 py-2 bg-white border border-gray-200 rounded-lg shadow-sm text-sm text-gray-700 focus:ring-2 focus:ring-[#0b2853] focus:outline-none" onchange="this.form.submit()">
        </form>
    </div>

    <!-- 4 Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <p class="text-sm text-gray-500 font-medium mb-1">Total Pemasukan</p>
            <h3 class="text-xl lg:text-2xl font-bold text-green-600 mb-2">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></h3>
            <p class="text-xs text-green-500 font-medium">↑ dari bulan lalu</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <p class="text-sm text-gray-500 font-medium mb-1">Total Pengeluaran</p>
            <h3 class="text-xl lg:text-2xl font-bold text-red-500 mb-2">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></h3>
            <p class="text-xs text-red-500 font-medium">↑ dari bulan lalu</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <p class="text-sm text-gray-500 font-medium mb-1">Saldo</p>
            <h3 class="text-xl lg:text-2xl font-bold text-blue-600 mb-2">Rp <?= number_format($saldo, 0, ',', '.') ?></h3>
            <p class="text-xs text-green-500 font-medium">↑ dari bulan lalu</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <p class="text-sm text-gray-500 font-medium mb-1">Pelanggan Aktif</p>
            <h3 class="text-xl lg:text-2xl font-bold text-purple-600 mb-2"><?= $pelanggan_aktif ?></h3>
            <p class="text-xs text-green-500 font-medium">↑ pelanggan aktif</p>
        </div>
    </div>

    <!-- Middle Section: Line Chart & Donut Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <h3 class="text-base font-bold text-gray-800 mb-4">Grafik Pemasukan & Pengeluaran</h3>
            <div class="relative h-64 w-full">
                <canvas id="financeLineChart"></canvas>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-200">
            <h3 class="text-base font-bold text-gray-800 mb-4">Kategori Pengeluaran</h3>
            <div class="relative h-64 w-full flex justify-center items-center">
                <?php if(empty($donut_data)): ?>
                    <p class="text-gray-400 text-sm">Belum ada data pengeluaran bulan ini.</p>
                <?php else: ?>
                    <canvas id="expensePieChart"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Transaksi Terakhir -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-base font-bold text-gray-800">Transaksi Terakhir</h3>
            <a href="?page=reports" class="text-sm text-blue-600 border border-blue-200 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition">Lihat Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-sm">
                        <th class="py-3 px-5 font-medium border-b border-gray-200">Tanggal</th>
                        <th class="py-3 px-5 font-medium border-b border-gray-200">Keterangan</th>
                        <th class="py-3 px-5 font-medium border-b border-gray-200">Tipe</th>
                        <th class="py-3 px-5 font-medium border-b border-gray-200">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700">
                    <?php if (count($transaksi_terakhir) > 0): ?>
                        <?php foreach ($transaksi_terakhir as $trx): ?>
                            <tr class="hover:bg-gray-50 border-b border-gray-100">
                                <td class="py-3 px-5"><?= date('d M Y', strtotime($trx['tanggal'])) ?></td>
                                <td class="py-3 px-5"><?= htmlspecialchars($trx['keterangan']) ?></td>
                                <td class="py-3 px-5">
                                    <span class="<?= $trx['tipe'] == 'Pemasukan' ? 'text-green-600' : 'text-red-500' ?> font-medium">
                                        <?= $trx['tipe'] ?>
                                    </span>
                                </td>
                                <td class="py-3 px-5">Rp <?= number_format($trx['jumlah'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="py-6 text-center text-gray-400">Belum ada transaksi di bulan ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- NEW SECTION: Laporan Keuangan & Pelanggan Terbaru -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        
        <!-- Kiri: Laporan Keuangan (Col-span 3) -->
        <div class="lg:col-span-3 bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex flex-col">
            <h3 class="text-base font-bold text-gray-800 mb-4">Laporan Keuangan</h3>
            
            <!-- FORM EXPORT EXCEL -->
            <form action="process/export_pdf.php" method="GET" target="_blank" class="flex flex-col h-full">
                <!-- Filters -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="<?= date('Y-m-01') ?>" class="w-full px-3 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="<?= date('Y-m-t') ?>" class="w-full px-3 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                    </div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Jenis Laporan</label>
                            <select name="jenis" class="w-full px-3 py-1.5 border border-gray-200 rounded-lg text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <option value="Semua">Semua</option>
                                <option value="Pemasukan">Pemasukan</option>
                                <option value="Pengeluaran">Pengeluaran</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Mini Metrics Row -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="border border-green-100 bg-green-50/30 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Total Pemasukan</p>
                        <h4 class="text-lg font-bold text-green-600">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></h4>
                    </div>
                    <div class="border border-red-100 bg-red-50/30 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Total Pengeluaran</p>
                        <h4 class="text-lg font-bold text-red-500">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></h4>
                    </div>
                    <div class="border border-blue-100 bg-blue-50/30 p-3 rounded-lg">
                        <p class="text-xs text-gray-500 mb-1">Saldo Akhir</p>
                        <h4 class="text-lg font-bold text-blue-600">Rp <?= number_format($saldo, 0, ',', '.') ?></h4>
                    </div>
                </div>

                <!-- Bar Chart -->
                <div class="relative h-48 w-full mb-4">
                    <canvas id="financeBarChart"></canvas>
                </div>

                <!-- Tombol Export -->
                <div class="mt-auto flex justify-end">
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition flex items-center gap-2">
    <!-- Icon PDF -->
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
    Export PDF
</button>
                </div>
            </form>
        </div>

        <!-- Kanan: Pelanggan Terbaru (Col-span 2) -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-base font-bold text-gray-800">Pelanggan Terbaru</h3>
                <a href="?page=customers" class="text-sm text-blue-600 border border-blue-200 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition">Lihat Semua</a>
            </div>
            <div class="p-4 flex-1 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 text-sm text-gray-500">
                            <th class="py-2 px-2 font-medium">No</th>
                            <th class="py-2 px-2 font-medium">Nama</th>
                            <th class="py-2 px-2 font-medium">Paket</th>
                            <th class="py-2 px-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <?php if(count($pelanggan_terbaru) > 0): ?>
                            <?php $no=1; foreach($pelanggan_terbaru as $p): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50">
                                <td class="py-3 px-2"><?= $no++ ?></td>
                                <td class="py-3 px-2 font-medium"><?= htmlspecialchars($p['name']) ?></td>
                                <td class="py-3 px-2 text-gray-500"><?= htmlspecialchars($p['paket']) ?></td>
                                <td class="py-3 px-2">
                                    <?php if($p['status'] == 'Aktif'): ?>
                                        <span class="text-green-600 bg-green-50 border border-green-200 px-2 py-0.5 rounded text-xs">Aktif</span>
                                    <?php else: ?>
                                        <span class="text-red-500 bg-red-50 border border-red-200 px-2 py-0.5 rounded text-xs">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="py-6 text-center text-gray-400">Belum ada data pelanggan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>

<script>
    // Ambil data dinamis dari PHP ke format JSON untuk Javascript
    const chartLabels = <?= json_encode($chart_labels) ?>;
    const chartPemasukan = <?= json_encode($chart_pemasukan) ?>;
    const chartPengeluaran = <?= json_encode($chart_pengeluaran) ?>;
    
    const donutLabels = <?= json_encode($donut_labels) ?>;
    const donutData = <?= json_encode($donut_data) ?>;

    // 1. LINE CHART (Grafik Pemasukan & Pengeluaran)
    const ctxLine = document.getElementById('financeLineChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Pemasukan',
                data: chartPemasukan,
                borderColor: '#16a34a', // Hijau
                backgroundColor: '#16a34a',
                borderWidth: 2,
                pointRadius: 3,
                fill: false,
                tension: 0.1
            }, {
                label: 'Pengeluaran',
                data: chartPengeluaran,
                borderColor: '#ef4444', // Merah
                backgroundColor: '#ef4444',
                borderWidth: 2,
                pointRadius: 3,
                fill: false,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', align: 'start', labels: { boxWidth: 8, usePointStyle: true } } },
            scales: { y: { beginAtZero: true, ticks: { callback: function(value) { return value === 0 ? '0' : (value / 1000000) + 'jt'; } } } }
        }
    });

    // 2. DONUT CHART (Kategori Pengeluaran) - Hanya dirender jika ada data
    const pieCanvas = document.getElementById('expensePieChart');
    if (pieCanvas) {
        new Chart(pieCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: donutLabels,
                datasets: [{
                    data: donutData,
                    backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#8b5cf6', '#6b7280', '#ec4899'],
                    borderWidth: 0
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: { legend: { position: 'right', labels: { boxWidth: 10, usePointStyle: true, padding: 15 } } }
            }
        });
    }

    // 3. BAR CHART (Laporan Keuangan Section)
    const ctxBar = document.getElementById('financeBarChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Pemasukan',
                data: chartPemasukan,
                backgroundColor: '#16a34a',
                borderRadius: 2
            }, {
                label: 'Pengeluaran',
                data: chartPengeluaran,
                backgroundColor: '#ef4444',
                borderRadius: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top', align: 'center', labels: { boxWidth: 10, usePointStyle: true } } },
            scales: { 
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { callback: function(value) { return value === 0 ? '0' : (value / 1000000) + 'jt'; } } }
            }
        }
    });
</script>