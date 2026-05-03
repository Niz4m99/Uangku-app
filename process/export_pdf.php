<?php
// process/export_pdf.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Akses ditolak.");
}

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-t');
$jenis      = $_GET['jenis'] ?? 'Semua';

$data = [];

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
    }
}

// Urutkan data berdasarkan tanggal
usort($data, function($a, $b) {
    return strtotime($a['tanggal']) - strtotime($b['tanggal']);
});
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - PDF</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; font-size: 12px; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0b2853; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #0b2853; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0 0; color: #555; }
        table { w-full; width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
        th { background-color: #f8fafc; font-weight: bold; color: #0b2853; text-align: center; text-transform: uppercase; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .tfoot-total th { background-color: #f1f5f9; font-size: 12px; }
        .tfoot-saldo th { background-color: #dbeafe; color: #1e40af; font-size: 14px; }
        
        /* CSS Khusus agar bagus saat dicetak ke PDF */
        @media print {
            body { padding: 0; }
            @page { size: A4; margin: 15mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <!-- Tombol Bantuan (Otomatis hilang saat di-print) -->
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #0b2853; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
            🖨️ Cetak / Simpan sebagai PDF
        </button>
        <p style="color: red; font-size: 11px;">*Pilih "Save as PDF" di pengaturan printer browser Anda.</p>
    </div>

    <div class="header">
        <h2>Laporan Keuangan WiFi</h2>
        <p>Periode: <strong><?= date('d M Y', strtotime($start_date)) ?></strong> s/d <strong><?= date('d M Y', strtotime($end_date)) ?></strong></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="40%">Keterangan</th>
                <th width="20%">Pemasukan (Rp)</th>
                <th width="20%">Pengeluaran (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $total_pemasukan = 0;
            $total_pengeluaran = 0;
            
            if(count($data) > 0):
                foreach ($data as $row):
                    $total_pemasukan += $row['pemasukan'];
                    $total_pengeluaran += $row['pengeluaran'];
            ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                    <td><?= htmlspecialchars($row['keterangan']) ?></td>
                    <td class="text-right"><?= number_format($row['pemasukan'], 0, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($row['pengeluaran'], 0, ',', '.') ?></td>
                </tr>
            <?php 
                endforeach; 
            else: 
            ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #999;">Tidak ada data pada periode ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="tfoot-total">
                <th colspan="3" class="text-right">TOTAL :</th>
                <th class="text-right"><?= number_format($total_pemasukan, 0, ',', '.') ?></th>
                <th class="text-right"><?= number_format($total_pengeluaran, 0, ',', '.') ?></th>
            </tr>
            <tr class="tfoot-saldo">
                <th colspan="3" class="text-right">SALDO BERSIH :</th>
                <th colspan="2" class="text-center"><?= number_format($total_pemasukan - $total_pengeluaran, 0, ',', '.') ?></th>
            </tr>
        </tfoot>
    </table>

    <script>
        // Otomatis memunculkan dialog Print/Save as PDF saat halaman dibuka
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>