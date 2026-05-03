<!-- pages/customers.php -->
<?php
$stmt = $pdo->query("SELECT * FROM pelanggan ORDER BY id DESC");
$pelanggan = $stmt->fetchAll();
?>

<div class="p-4 sm:p-6 space-y-6">
    
    <!-- Notifikasi Sukses / Error -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($_GET['msg']) ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['err'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4" role="alert">
            <span class="block sm:inline"><?= htmlspecialchars($_GET['err']) ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white p-5 sm:p-6 rounded-xl shadow-sm border border-gray-200">
        
        <!-- Header Tabel & Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h2 class="text-xl font-bold text-gray-800">Data Pelanggan</h2>
            
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                <!-- Tombol Export Excel (CSV) -->
                <a href="process/export_customers.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition flex-1 sm:flex-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export
                </a>
                
                <!-- Tombol Import Excel (CSV) -->
                <button onclick="document.getElementById('modalImport').classList.remove('hidden')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition flex-1 sm:flex-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Import
                </button>

                <!-- Tombol Tambah Pelanggan -->
                <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition w-full sm:w-auto mt-2 sm:mt-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Pelanggan
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm border-b border-gray-200">
                        <th class="py-3 px-4 font-semibold">No</th>
                        <th class="py-3 px-4 font-semibold">Nama</th>
                        <th class="py-3 px-4 font-semibold">Alamat</th>
                        <th class="py-3 px-4 font-semibold">HP</th>
                        <th class="py-3 px-4 font-semibold">Paket</th>
                        <th class="py-3 px-4 font-semibold text-center">Status</th>
                        <th class="py-3 px-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                    <?php if(count($pelanggan) > 0): ?>
                        <?php $no = 1; foreach($pelanggan as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4"><?= $no++ ?></td>
                            <td class="py-3 px-4 font-medium text-gray-800"><?= htmlspecialchars($row['name']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($row['alamat']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($row['no_hp']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($row['paket']) ?></td>
                            <td class="py-3 px-4 text-center">
                                <?php if($row['status'] == 'Aktif'): ?>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium border border-green-200">Aktif</span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-medium border border-red-200">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center space-x-2">
                                <button onclick="editPelanggan(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['name'])) ?>', '<?= htmlspecialchars(addslashes($row['alamat'])) ?>', '<?= htmlspecialchars(addslashes($row['no_hp'])) ?>', '<?= $row['paket'] ?>', '<?= $row['status'] ?>')" class="text-gray-500 hover:text-blue-600 border border-gray-200 p-1.5 rounded-md hover:bg-blue-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <form action="process/crud_customer.php" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="text-red-400 hover:text-red-600 border border-gray-200 p-1.5 rounded-md hover:bg-red-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="py-8 text-center text-gray-400">Belum ada data pelanggan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Pelanggan -->
<div id="modalTambah" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800">Tambah Pelanggan Baru</h3>
            <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form action="process/crud_customer.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="create">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat (RT/RW)</label>
                <input type="text" name="alamat" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                <input type="text" name="no_hp" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Paket (Mbps)</label>
                    <select name="paket" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="5 Mbps">5 Mbps</option>
                        <option value="10 Mbps">10 Mbps</option>
                        <option value="20 Mbps">20 Mbps</option>
                        <option value="50 Mbps">50 Mbps</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="pt-4 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg text-sm font-medium">Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pelanggan -->
<div id="modalEdit" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800">Edit Pelanggan</h3>
            <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form action="process/crud_customer.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="edit_id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" id="edit_name" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat (RT/RW)</label>
                <input type="text" name="alamat" id="edit_alamat" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP</label>
                <input type="text" name="no_hp" id="edit_hp" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Paket (Mbps)</label>
                    <select name="paket" id="edit_paket" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="5 Mbps">5 Mbps</option>
                        <option value="10 Mbps">10 Mbps</option>
                        <option value="20 Mbps">20 Mbps</option>
                        <option value="50 Mbps">50 Mbps</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="edit_status" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="pt-4 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg text-sm font-medium">Update Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Import Excel (CSV) -->
<!-- Modal Import Excel (CSV) -->
<div id="modalImport" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800">Import Data Pelanggan</h3>
            <button onclick="document.getElementById('modalImport').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form action="process/import_customers.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            
            <!-- Kotak Aturan & Download Template -->
            <div class="bg-blue-50 text-blue-800 p-4 rounded-lg text-sm mb-4 border border-blue-100">
                <p class="font-bold mb-2">Aturan File Import:</p>
                <ol class="list-decimal ml-4 space-y-1 mb-3">
                    <li>Gunakan Excel, pastikan ada 5 kolom persis seperti ini: <strong>Nama Lengkap, Alamat, Nomor HP, Paket, Status</strong></li>
                    <li>Simpan file dalam format <strong>CSV (Comma delimited) (*.csv)</strong></li>
                    <li>Pastikan baris pertama adalah judul kolom (Header).</li>
                </ol>
                
                <!-- Tombol Download Mentahan -->
                <div class="pt-3 mt-3 border-t border-blue-200">
                    <p class="text-xs mb-1">Belum punya formatnya?</p>
                    <a href="process/download_template.php" class="inline-flex items-center gap-1.5 bg-blue-600 text-white px-3 py-1.5 rounded text-xs font-semibold hover:bg-blue-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Template CSV
                    </a>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Upload File Excel (.csv)</label>
                <input type="file" name="file_excel" accept=".csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="pt-4 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalImport').classList.add('hidden')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-yellow-500 text-white hover:bg-yellow-600 rounded-lg text-sm font-medium transition shadow-sm">Mulai Import</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editPelanggan(id, name, alamat, hp, paket, status) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_alamat').value = alamat;
        document.getElementById('edit_hp').value = hp;
        document.getElementById('edit_paket').value = paket;
        document.getElementById('edit_status').value = status;
        document.getElementById('modalEdit').classList.remove('hidden');
    }
</script>