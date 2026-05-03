<!-- pages/logs.php -->
<?php
// Pagination sederhana
$limit = 50;
$current_page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($current_page - 1) * $limit;

// KODE AMAN: Kita tidak melakukan JOIN ke tabel users dulu agar terhindar dari Error "Unknown Column"
$stmt = $pdo->prepare("SELECT * FROM activity_logs ORDER BY tanggal DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();
?>

<div class="p-4 sm:p-6 space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h2 class="text-xl font-bold text-gray-800">Log Aktivitas Sistem</h2>
            <span class="text-xs text-gray-500 font-medium">Menampilkan 50 aktivitas terbaru</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-white text-gray-400 uppercase text-[10px] font-bold tracking-widest border-b">
                    <tr>
                        <th class="py-4 px-6">Waktu</th>
                        <th class="py-4 px-6">User</th>
                        <th class="py-4 px-6">Aktivitas</th>
                        <th class="py-4 px-6">Detail Ringkas</th>
                        <th class="py-4 px-6">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if(count($logs) > 0): foreach($logs as $log): ?>
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="py-4 px-6 text-gray-500">
                            <?= date('d/m/Y H:i:s', strtotime($log['tanggal'])) ?>
                        </td>
                        <td class="py-4 px-6 font-bold text-gray-700">
                            <span class="bg-blue-50 text-blue-600 px-2 py-1 rounded text-xs font-mono">
                                <?= $log['user_id'] ? 'Admin (ID: ' . $log['user_id'] . ')' : 'System' ?>
                            </span>
                        </td>
                        <td class="py-4 px-6 font-medium">
                            <?php 
                                $color = "text-gray-600";
                                if(strpos($log['aktivitas'], 'Hapus') !== false) $color = "text-red-600";
                                if(strpos($log['aktivitas'], 'Tambah') !== false) $color = "text-green-600";
                            ?>
                            <span class="<?= $color ?>"><?= $log['aktivitas'] ?></span>
                        </td>
                        <td class="py-4 px-6 text-gray-500 max-w-xs overflow-hidden text-ellipsis">
                            <?= htmlspecialchars($log['detail']) ?>
                        </td>
                        <td class="py-4 px-6 text-gray-400 font-mono text-[11px]">
                            <?= $log['ip_address'] ?>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="5" class="py-10 text-center text-gray-400">Belum ada log aktivitas.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>