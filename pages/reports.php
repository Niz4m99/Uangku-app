<!-- pages/reports.php -->
<?php
// Set nilai filter default
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$jenis      = isset($_GET['jenis']) ? $_GET['jenis'] : 'Semua';

$data = [];
$total_pemasukan = 0;
$total_pengeluaran = 0;

// 1. Ambil Data Pemasukan
if ($jenis == 'Pemasukan' || $jenis == 'Semua') {
    $stmt = $pdo->prepare("SELECT p.tanggal, p.keterangan, p.jumlah, p.jenis, pl.name FROM pemasukan p LEFT JOIN pelanggan pl ON p.pelanggan_id = pl.id WHERE p.tanggal BETWEEN ? AND ?");
    $stmt->execute([$start_date, $end_date]);
    $pemasukan = $stmt->fetchAll();
    
    foreach($pemasukan as $row) {
        $nama_pelanggan = $row['name'] ? $row['name'] : 'Umum/Dihapus';
        $keterangan_lengkap = ($row['jenis'] == 'Tagihan Bulanan') ? $row['keterangan'] . " (" . $nama_pelanggan . ")" : $row['keterangan'];
        
        $data[] = [
            'tanggal' => $row['tanggal'],
            'keterangan' => $keterangan_lengkap,
            'pemasukan' => $row['jumlah'],
            'pengeluaran' => 0
        ];
        $total_pemasukan += $row['jumlah'];
    }
}

// 2. Ambil Data Pengeluaran
if ($jenis == 'Pengeluaran' || $jenis == 'Semua') {
    $stmt = $pdo->prepare("SELECT tanggal, keterangan, jumlah FROM pengeluaran WHERE tanggal BETWEEN ? AND ?");
    $stmt->execute([$start_date, $end_date]);
    $pengeluaran = $stmt->fetchAll();
    
    foreach($pengeluaran as $row) {
        $data[] = [
            'tanggal' => $row['tanggal'],
            'keterangan' => $row['keterangan'],
            'pemasukan' => 0,
            'pengeluaran' => $row['jumlah']
        ];
        $total_pengeluaran += $row['jumlah'];
    }
}

// Urutkan data berdasarkan tanggal dari yang terbaru
usort($data, function($a, $b) {
    return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});

// ==========================================
// LOGIKA PAGINATION (25 Data per Halaman)
// ==========================================
$limit = 25;
$total_data = count($data);
$total_pages = ceil($total_data / $limit);

// Ambil nomor halaman saat ini dari URL (misal: ?p=2), default 1
$current_page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
if ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
}

// Potong array data sesuai halaman yang aktif
$offset = ($current_page - 1) * $limit;
$paginated_data = array_slice($data, $offset, $limit);

// Query String pembantu agar saat klik "Next Page", filter tanggal tidak hilang
$query_string = http_build_query([
    'page' => 'reports',
    'start_date' => $start_date,
    'end_date' => $end_date,
    'jenis' => $jenis
]);
?>

<div class="p-4 sm:p-6 space-y-6">
    
    <!-- Filter Laporan -->
    <div class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-200">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Filter Laporan</h2>
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <!-- Parameter wajib untuk Router -->
            <input type="hidden" name="page" value="reports">
            
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Dari Tanggal</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" class="w-full border border-gray-200 rounded-lg p-2 focus:ring-2 focus:ring-[#0b2853] focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Sampai Tanggal</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" class="w-full border border-gray-200 rounded-lg p-2 focus:ring-2 focus:ring-[#0b2853] focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Jenis Transaksi</label>
                <select name="jenis" class="w-full border border-gray-200 rounded-lg p-2 bg-white focus:ring-2 focus:ring-[#0b2853] focus:outline-none">
                    <option value="Semua" <?= $jenis == 'Semua' ? 'selected' : '' ?>>Semua</option>
                    <option value="Pemasukan" <?= $jenis == 'Pemasukan' ? 'selected' : '' ?>>Pemasukan</option>
                    <option value="Pengeluaran" <?= $jenis == 'Pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-[#0b2853] text-white px-4 py-2 rounded-lg font-bold hover:bg-blue-900 transition">Tampilkan</button>
                <button type="submit" formaction="process/export_pdf.php" formtarget="_blank" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-700 transition flex items-center justify-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    PDF
                </button>
            </div>
        </form>
    </div>

    <!-- Summary / Ringkasan Atas -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-green-200">
            <p class="text-sm text-gray-500 font-medium mb-1">Total Pemasukan</p>
            <h3 class="text-2xl font-bold text-green-600">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></h3>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-red-200">
            <p class="text-sm text-gray-500 font-medium mb-1">Total Pengeluaran</p>
            <h3 class="text-2xl font-bold text-red-500">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></h3>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-blue-200">
            <p class="text-sm text-gray-500 font-medium mb-1">Saldo Bersih</p>
            <h3 class="text-2xl font-bold text-blue-600">Rp <?= number_format($total_pemasukan - $total_pengeluaran, 0, ',', '.') ?></h3>
        </div>
    </div>

    <!-- Tabel Data Laporan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-base font-bold text-gray-800">Rincian Transaksi</h3>
            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">Total: <?= $total_data ?> Data</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-500 border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-5">No</th>
                        <th class="py-3 px-5">Tanggal</th>
                        <th class="py-3 px-5">Keterangan</th>
                        <th class="py-3 px-5 text-right">Pemasukan</th>
                        <th class="py-3 px-5 text-right">Pengeluaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (count($paginated_data) > 0): $no = $offset + 1; foreach ($paginated_data as $row): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-5"><?= $no++ ?></td>
                        <td class="py-3 px-5"><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                        <td class="py-3 px-5 text-gray-800"><?= htmlspecialchars($row['keterangan']) ?></td>
                        <td class="py-3 px-5 text-right text-green-600 font-medium">
                            <?= $row['pemasukan'] > 0 ? 'Rp ' . number_format($row['pemasukan'], 0, ',', '.') : '-' ?>
                        </td>
                        <td class="py-3 px-5 text-right text-red-500 font-medium">
                            <?= $row['pengeluaran'] > 0 ? 'Rp ' . number_format($row['pengeluaran'], 0, ',', '.') : '-' ?>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-400">Tidak ada data transaksi pada periode ini.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- MENU PAGINATION (Halaman 1, 2, 3...) -->
        <?php if ($total_pages > 1): ?>
        <div class="px-5 py-3 border-t border-gray-100 bg-white flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-sm text-gray-500">
                Menampilkan data ke <span class="font-medium"><?= $offset + 1 ?></span> - <span class="font-medium"><?= min($offset + $limit, $total_data) ?></span> dari <span class="font-medium"><?= $total_data ?></span>
            </span>
            <nav class="inline-flex rounded-md shadow-sm">
                <!-- Tombol Prev -->
                <?php if ($current_page > 1): ?>
                    <a href="?<?= $query_string ?>&p=<?= $current_page - 1 ?>" class="px-3 py-1.5 border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 rounded-l-md text-sm font-medium">Prev</a>
                <?php else: ?>
                    <span class="px-3 py-1.5 border border-gray-300 bg-gray-50 text-gray-300 rounded-l-md text-sm font-medium cursor-not-allowed">Prev</span>
                <?php endif; ?>

                <!-- Angka Halaman -->
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?<?= $query_string ?>&p=<?= $i ?>" class="px-3 py-1.5 border-t border-b border-gray-300 text-sm font-medium <?= $i == $current_page ? 'bg-[#0b2853] text-white border-[#0b2853]' : 'bg-white text-gray-500 hover:bg-gray-50 border-r' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <!-- Tombol Next -->
                <?php if ($current_page < $total_pages): ?>
                    <a href="?<?= $query_string ?>&p=<?= $current_page + 1 ?>" class="px-3 py-1.5 border border-gray-300 bg-white text-gray-500 hover:bg-gray-50 rounded-r-md text-sm font-medium">Next</a>
                <?php else: ?>
                    <span class="px-3 py-1.5 border border-gray-300 bg-gray-50 text-gray-300 rounded-r-md text-sm font-medium cursor-not-allowed">Next</span>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>

        <!-- FOOTER RINGKASAN BAWAH -->
        <div class="bg-gray-50 border-t border-gray-200 p-5">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
                <div class="text-xs text-gray-400 italic">
                    Data keseluruhan dari <span class="font-semibold"><?= date('d/m/Y', strtotime($start_date)) ?></span> s/d <span class="font-semibold"><?= date('d/m/Y', strtotime($end_date)) ?></span>
                </div>
                
                <div class="w-full sm:w-80 bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                    <div class="flex justify-between items-center text-sm mb-2">
                        <span class="text-gray-500 font-medium">Total Pemasukan</span>
                        <span class="text-green-600 font-bold">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></span>
                    </div>
                    <div class="flex justify-between items-center text-sm mb-3">
                        <span class="text-gray-500 font-medium">Total Pengeluaran</span>
                        <span class="text-red-500 font-bold">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="border-t border-gray-200 mb-3"></div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-gray-800 font-bold uppercase text-xs tracking-wider">Hasil Akhir</span>
                        <?php $hasil_akhir = $total_pemasukan - $total_pengeluaran; ?>
                        <span class="text-xl font-black <?= $hasil_akhir >= 0 ? 'text-blue-600' : 'text-red-600' ?>">
                            Rp <?= number_format($hasil_akhir, 0, ',', '.') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>