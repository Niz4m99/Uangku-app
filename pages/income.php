<!-- pages/income.php -->
<?php
// ==========================================
// LOGIKA PAGINATION (25 Data per Halaman)
// ==========================================
$limit = 25;

$stmtCount = $pdo->query("SELECT COUNT(*) as total FROM pemasukan");
$total_data = $stmtCount->fetch()['total'];
$total_pages = ceil($total_data / $limit);

$current_page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
if ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
}

$offset = ($current_page - 1) * $limit;

$stmt = $pdo->prepare("SELECT p.*, pl.name FROM pemasukan p LEFT JOIN pelanggan pl ON p.pelanggan_id = pl.id ORDER BY p.tanggal DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$pemasukan = $stmt->fetchAll();

$stmtTotal = $pdo->query("SELECT COALESCE(SUM(jumlah), 0) as total_uang FROM pemasukan");
$total_uang_pemasukan = $stmtTotal->fetch()['total_uang'];

$stmtPl = $pdo->query("SELECT id, name FROM pelanggan WHERE status = 'Aktif'");
$dataPelanggan = $stmtPl->fetchAll();
?>

<!-- Include CSS Tom Select -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
    .ts-control { border-radius: 0.5rem; padding: 0.5rem 0.75rem; border-color: #e5e7eb; font-size: 0.875rem; }
    .ts-control.focus { border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2); }
    .ts-dropdown { border-radius: 0.5rem; font-size: 0.875rem; overflow: hidden;}
</style>

<div class="p-4 sm:p-6 space-y-6">
    <div class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-200">
        
        <!-- Header: Judul & Tombol -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h2 class="text-xl font-bold text-gray-800">Pemasukan</h2>
            <div class="flex gap-2 w-full sm:w-auto">
                <!-- Tombol Hapus Masal (Sembunyi secara default) -->
                <button type="submit" form="formBulkIncome" id="btnBulkIncome" class="hidden bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium items-center justify-center gap-2 hover:bg-red-700 transition flex-1 sm:flex-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Hapus (<span id="countIncome">0</span>)
                </button>
                <button onclick="document.getElementById('modalPemasukan').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2 hover:bg-blue-700 transition flex-1 sm:flex-none">
                    <span>+</span> Tambah Pemasukan
                </button>
            </div>
        </div>

        <!-- Kotak Total -->
        <div class="w-full sm:w-1/2 lg:w-1/3 bg-white p-5 rounded-xl shadow-sm border border-green-200 mb-6">
            <p class="text-sm text-gray-500 font-medium mb-1">Total Keseluruhan Pemasukan</p>
            <h3 class="text-2xl lg:text-3xl font-bold text-green-600">Rp <?= number_format($total_uang_pemasukan, 0, ',', '.') ?></h3>
        </div>

        <!-- FORM BULK ACTION PEMASUKAN -->
        <form id="formBulkIncome" action="process/crud_income.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus SEMUA data pemasukan yang dipilih secara permanen?');">
            <input type="hidden" name="action" value="bulk_delete">
            
            <div class="overflow-x-auto rounded-t-lg border border-gray-100 border-b-0">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 text-gray-500">
                        <tr>
                            <!-- Checkbox Select All -->
                            <th class="py-3 px-4 w-10 text-center">
                                <input type="checkbox" id="selectAllIncome" onclick="toggleSelectAll('chkIncome', this, 'btnBulkIncome', 'countIncome')" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            </th>
                            <th class="py-3 px-4">No</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Pelanggan</th>
                            <th class="py-3 px-4">Keterangan</th>
                            <th class="py-3 px-4">Jumlah</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(count($pemasukan) > 0): $no = $offset + 1; foreach($pemasukan as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <!-- Checkbox Baris -->
                            <td class="py-3 px-4 text-center">
                                <input type="checkbox" name="ids[]" value="<?= $row['id'] ?>" class="chkIncome w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer" onclick="updateBulkButton('chkIncome', 'btnBulkIncome', 'countIncome', 'selectAllIncome')">
                            </td>
                            <td class="py-3 px-4"><?= $no++ ?></td>
                            <td class="py-3 px-4"><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                            
                            <td class="py-3 px-4 font-medium">
                                <?php if ($row['jenis'] == 'Pemasukan Lain'): ?>
                                    <span class="text-gray-400 italic">- (Umum) -</span>
                                <?php elseif ($row['name']): ?>
                                    <?= htmlspecialchars($row['name']) ?>
                                <?php else: ?>
                                    <span class="text-gray-400 italic">Pelanggan Dihapus</span>
                                <?php endif; ?>
                            </td>
                            
                            <td class="py-3 px-4">
                                <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full mb-1 inline-block <?= $row['jenis'] == 'Tagihan Bulanan' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                                    <?= $row['jenis'] ?? 'Tagihan Bulanan' ?>
                                </span><br>
                                <span class="text-gray-700"><?= htmlspecialchars($row['keterangan']) ?></span>
                            </td>
                            
                            <td class="py-3 px-4 text-green-600 font-bold">Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                            <td class="py-3 px-4 text-center space-x-2">
                                <button type="button" onclick="editPemasukan(<?= $row['id'] ?>, '<?= $row['jenis'] ?? 'Tagihan Bulanan' ?>', '<?= $row['pelanggan_id'] ?>', '<?= $row['tanggal'] ?>', '<?= htmlspecialchars(addslashes($row['keterangan'])) ?>', <?= $row['jumlah'] ?>)" class="text-gray-400 hover:text-blue-600 border border-gray-200 p-1.5 rounded-md hover:bg-blue-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <!-- Tombol Hapus Single (Menggunakan tag a agar tidak tabrakan dengan form bulk) -->
                                <a href="process/crud_income.php?action=delete&id=<?= $row['id'] ?>" onclick="return confirm('Hapus data ini?');" class="inline-block text-gray-400 hover:text-red-600 border border-gray-200 p-1.5 rounded-md hover:bg-red-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="7" class="py-10 text-center text-gray-400">Belum ada data pemasukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>

        <!-- MENU PAGINATION -->
        <?php if ($total_pages > 1): ?>
        <div class="px-5 py-3 border border-gray-100 rounded-b-lg bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-sm text-gray-500">
                Menampilkan <span class="font-medium"><?= $offset + 1 ?></span> - <span class="font-medium"><?= min($offset + $limit, $total_data) ?></span> dari <span class="font-medium"><?= $total_data ?></span> data
            </span>
            <nav class="inline-flex rounded-md shadow-sm">
                <?php if ($current_page > 1): ?>
                    <a href="?page=income&p=<?= $current_page - 1 ?>" class="px-3 py-1.5 border border-gray-300 bg-white text-gray-500 hover:bg-gray-100 rounded-l-md text-sm font-medium">Prev</a>
                <?php else: ?>
                    <span class="px-3 py-1.5 border border-gray-300 bg-gray-100 text-gray-400 rounded-l-md text-sm font-medium cursor-not-allowed">Prev</span>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=income&p=<?= $i ?>" class="px-3 py-1.5 border-t border-b border-gray-300 text-sm font-medium <?= $i == $current_page ? 'bg-[#0b2853] text-white border-[#0b2853]' : 'bg-white text-gray-500 hover:bg-gray-100 border-r' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                <?php if ($current_page < $total_pages): ?>
                    <a href="?page=income&p=<?= $current_page + 1 ?>" class="px-3 py-1.5 border border-gray-300 bg-white text-gray-500 hover:bg-gray-100 rounded-r-md text-sm font-medium">Next</a>
                <?php else: ?>
                    <span class="px-3 py-1.5 border border-gray-300 bg-gray-100 text-gray-400 rounded-r-md text-sm font-medium cursor-not-allowed">Next</span>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Modal Tambah Pemasukan (TETAP SAMA) -->
<div id="modalPemasukan" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <!-- Isi Modal Tambah Tetap Seperti Sebelumnya -->
    <div class="bg-white rounded-xl w-full max-w-md p-6">
        <h3 class="font-bold text-lg mb-4">Catat Pemasukan Baru</h3>
        <form action="process/crud_income.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Jenis Pemasukan</label>
                <select name="jenis" id="tambah_jenis" onchange="togglePelanggan(this.value, 'tambah')" class="w-full border rounded-lg p-2 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                    <option value="Tagihan Bulanan">Tagihan Bulanan (Pelanggan)</option>
                    <option value="Pemasukan Lain">Pemasukan Lainnya (Insidental)</option>
                </select>
            </div>
            <div id="container_pelanggan_tambah">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Pilih Pelanggan</label>
                <select id="tambah_pelanggan_id" name="pelanggan_id" required>
                    <option value="">-- Cari & Pilih Pelanggan Aktif --</option>
                    <?php foreach($dataPelanggan as $p): ?><option value="<?= $p['id'] ?>"><?= $p['name'] ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Tanggal</label><input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required></div>
                <div><label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Jumlah (Rp)</label><input type="number" name="jumlah" placeholder="150000" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required></div>
            </div>
            <div><label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Keterangan</label><input type="text" name="keterangan" placeholder="Contoh: Bayar Paket Mei / Biaya Pasang Baru" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required></div>
            <div class="flex justify-end gap-2 pt-2"><button type="button" onclick="document.getElementById('modalPemasukan').classList.add('hidden')" class="text-gray-500 px-4 hover:bg-gray-100 rounded-lg">Batal</button><button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700">Simpan</button></div>
        </form>
    </div>
</div>

<!-- Modal Edit Pemasukan (TETAP SAMA) -->
<div id="modalEditPemasukan" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <!-- Isi Modal Edit Tetap Seperti Sebelumnya -->
    <div class="bg-white rounded-xl w-full max-w-md p-6">
        <h3 class="font-bold text-lg mb-4">Edit Data Pemasukan</h3>
        <form action="process/crud_income.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Jenis Pemasukan</label>
                <select name="jenis" id="edit_jenis" onchange="togglePelanggan(this.value, 'edit')" class="w-full border rounded-lg p-2 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                    <option value="Tagihan Bulanan">Tagihan Bulanan (Pelanggan)</option>
                    <option value="Pemasukan Lain">Pemasukan Lainnya (Insidental)</option>
                </select>
            </div>
            <div id="container_pelanggan_edit">
                <label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Pilih Pelanggan</label>
                <select id="edit_pelanggan_id" name="pelanggan_id" required>
                    <option value="">-- Cari & Pilih Pelanggan --</option>
                    <?php foreach($dataPelanggan as $p): ?><option value="<?= $p['id'] ?>"><?= $p['name'] ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Tanggal</label><input type="date" name="tanggal" id="edit_tanggal" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required></div>
                <div><label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Jumlah (Rp)</label><input type="number" name="jumlah" id="edit_jumlah" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required></div>
            </div>
            <div><label class="block text-xs font-bold text-gray-500 mb-1 uppercase">Keterangan</label><input type="text" name="keterangan" id="edit_keterangan" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:outline-none" required></div>
            <div class="flex justify-end gap-2 pt-2"><button type="button" onclick="document.getElementById('modalEditPemasukan').classList.add('hidden')" class="text-gray-500 px-4 hover:bg-gray-100 rounded-lg">Batal</button><button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700">Update Data</button></div>
        </form>
    </div>
</div>

<!-- Include JS Tom Select & Script Custom -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    let tsTambah = new TomSelect("#tambah_pelanggan_id", { create: false, sortField: { field: "text", direction: "asc" } });
    let tsEdit = new TomSelect("#edit_pelanggan_id", { create: false, sortField: { field: "text", direction: "asc" } });

    function togglePelanggan(jenis, mode) {
        let container = document.getElementById('container_pelanggan_' + mode);
        let selectField = document.getElementById(mode + '_pelanggan_id');
        if (jenis === 'Pemasukan Lain') {
            container.classList.add('hidden');
            selectField.removeAttribute('required');
        } else {
            container.classList.remove('hidden');
            selectField.setAttribute('required', 'required');
        }
    }

    function editPemasukan(id, jenis, pelanggan_id, tanggal, keterangan, jumlah) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_jenis').value = jenis;
        togglePelanggan(jenis, 'edit');
        if(pelanggan_id) { tsEdit.setValue(pelanggan_id); } else { tsEdit.clear(); }
        document.getElementById('edit_tanggal').value = tanggal;
        document.getElementById('edit_keterangan').value = keterangan;
        document.getElementById('edit_jumlah').value = jumlah;
        document.getElementById('modalEditPemasukan').classList.remove('hidden');
    }

    // --- SCRIPT UNTUK CHECKBOX BULK ACTION ---
    function toggleSelectAll(chkClass, source, btnId, countId) {
        let checkboxes = document.querySelectorAll('.' + chkClass);
        for(let i=0; i<checkboxes.length; i++) {
            checkboxes[i].checked = source.checked;
        }
        updateBulkButton(chkClass, btnId, countId, source.id);
    }

    function updateBulkButton(chkClass, btnId, countId, selectAllId) {
        let checkedBoxes = document.querySelectorAll('.' + chkClass + ':checked');
        let totalBoxes = document.querySelectorAll('.' + chkClass);
        let btn = document.getElementById(btnId);
        let countSpan = document.getElementById(countId);
        let selectAll = document.getElementById(selectAllId);

        // Update status Select All jika ada yang di-uncheck
        if(checkedBoxes.length === totalBoxes.length && totalBoxes.length > 0) {
            selectAll.checked = true;
        } else {
            selectAll.checked = false;
        }

        if(checkedBoxes.length > 0) {
            btn.classList.remove('hidden');
            btn.classList.add('flex');
            countSpan.innerText = checkedBoxes.length;
        } else {
            btn.classList.add('hidden');
            btn.classList.remove('flex');
        }
    }
</script>