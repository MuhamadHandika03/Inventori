<?php
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.html');
    exit;
}

// XSS-safe output
$display_name = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$role         = htmlspecialchars($_SESSION['role'], ENT_QUOTES, 'UTF-8');
$is_admin     = $_SESSION['role'] === 'admin';

include 'components/header.php';
include 'components/navbar.php';
?>

<main class="container mx-auto px-6 py-8 max-w-7xl flex-grow">
    
    <?php include 'components/stats_cards.php'; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start mb-8">
        
        <div class="lg:sticky lg:top-24">
            <?php if ($is_admin): ?>
            <?php include 'components/form_admin.php'; ?>
            <?php endif; ?>
            <?php include 'components/form_staff.php'; ?>
        </div>

        <div id="table-container" class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/50">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Daftar Stok Inventori</h2>
                    <p class="text-xs text-slate-500">Data fisik barang yang tersedia di gudang penyimpanan</p>
                </div>
                <div class="relative w-full sm:w-64">
                    <span class="absolute left-3 top-2.5 text-slate-400 text-sm"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="search-bar" oninput="renderTable()" class="w-full pl-9 pr-3.5 py-2 bg-white border border-slate-200 rounded-xl focus:outline-none text-xs" placeholder="Cari produk...">
                </div>
            </div>

            <div id="loading-indicator" class="text-center py-4 text-slate-400 text-sm">
                <i class="fa-solid fa-circle-notch animate-spin mr-1"></i> Memuat data...
            </div>

            <div class="overflow-x-auto">
                <table class="w-full table-auto text-left text-sm text-slate-600">
                    <thead>
                        <tr class="bg-slate-100/70 text-slate-700 text-xs font-semibold uppercase tracking-wider border-b border-slate-200/60">
                            <th class="px-6 py-3.5">Kode</th>
                            <th class="px-6 py-3.5">Nama Produk</th>
                            <th class="px-6 py-3.5">Kategori</th>
                            <th class="px-6 py-3.5 text-center">Status Volume Stok</th>
                            <th class="px-6 py-3.5 text-right">Harga Satuan</th>
                            <?php if ($is_admin): ?>
                            <th class="px-6 py-3.5 text-center">Aksi Manajemen</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="inventory-table-body" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
            <div id="empty-state" class="text-center py-12 text-slate-400 hidden">
                <i class="fa-regular fa-folder-open text-3xl mb-2 block"></i> Belum ada data produk terdaftar.
            </div>
            <div id="error-state" class="text-center py-12 text-red-400 hidden">
                <i class="fa-solid fa-triangle-exclamation text-3xl mb-2 block"></i> Gagal memuat data. <button onclick="loadSemuaData()" class="text-indigo-600 underline">Coba lagi</button>
            </div>
        </div>

    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-lg font-bold text-slate-900">📋 Log Riwayat Aktivitas Keluar Masuk Stok</h2>
            <p class="text-xs text-slate-500">Rekam jejak digital audit mutasi barang oleh sistem</p>
        </div>
        <div id="riwayat-loading" class="text-center py-4 text-slate-400 text-sm">
            <i class="fa-solid fa-circle-notch animate-spin mr-1"></i> Memuat riwayat...
        </div>
        <div class="overflow-x-auto">
            <table class="w-full table-auto text-left text-sm text-slate-600">
                <thead>
                    <tr class="bg-slate-100/70 text-slate-700 text-xs font-semibold uppercase tracking-wider border-b border-slate-200/60">
                        <th class="px-6 py-3.5">Waktu Operasional</th>
                        <th class="px-6 py-3.5">Operator</th>
                        <th class="px-6 py-3.5">Identitas Produk</th>
                        <th class="px-6 py-3.5 text-center">Jenis Aktivitas</th>
                        <th class="px-6 py-3.5 text-center">Kuantitas</th>
                        <th class="px-6 py-3.5">Keterangan Tambahan</th>
                    </tr>
                </thead>
                <tbody id="riwayat-table-body" class="divide-y divide-slate-100 text-xs md:text-sm"></tbody>
            </table>
        </div>
    </div>

</main>

<script>
    const API_URL = 'api.php';
    let inventory = [];
    let csrfToken = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>';

    const role = <?= json_encode($role) ?>;
    const username = <?= json_encode($display_name) ?>;

    document.getElementById('display-user').innerText = `${username} (${role})`;

    document.getElementById('form-staff-container').classList.remove('hidden');

    if (role === 'admin') {
        const adminForm = document.querySelector('#form-admin-container');
        if (adminForm) adminForm.classList.remove('hidden');
    }

    async function apiFetch(url, options = {}) {
        const headers = { 'Content-Type': 'application/json' };
        if (csrfToken) headers['X-CSRF-Token'] = csrfToken;
        if (options.body && csrfToken) {
            // Inject csrf_token into body for non-GET
            const body = typeof options.body === 'string' ? JSON.parse(options.body) : options.body;
            body.csrf_token = csrfToken;
            options.body = JSON.stringify(body);
        }
        const res = await fetch(url, { ...options, headers });
        if (res.status === 403) {
            // CSRF mismatch — try refresh
            const refreshRes = await fetch('auth.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({}) });
            // If refresh fails, redirect to login
            window.location.href = 'login.html';
            throw new Error('Session expired');
        }
        return res;
    }

    async function loadSemuaData() {
        showLoading();
        await fetchInventory();
        await fetchRiwayat();
        if (role === 'admin') await loadKategoriDropdown();
        populateDropdownBarang();
    }

    let kategoriList = [];
    async function loadKategoriDropdown() {
        const res = await apiFetch(`${API_URL}?action=kategori`, { method: 'GET' });
        kategoriList = await res.json();
        const sel = document.getElementById('item-kategori');
        sel.innerHTML = '<option value="">-- Pilih Kategori --</option>';
        kategoriList.forEach(k => {
            const opt = document.createElement('option');
            opt.value = k.id;
            opt.textContent = `${k.name} (contoh: ${k.code_prefix}-001)`;
            sel.appendChild(opt);
        });
    }

    function showLoading() {
        const li = document.getElementById('loading-indicator');
        if (li) li.classList.remove('hidden');
        document.getElementById('error-state').classList.add('hidden');
    }

    function hideLoading() {
        const li = document.getElementById('loading-indicator');
        if (li) li.classList.add('hidden');
    }

    async function fetchInventory() {
        try {
            const response = await apiFetch(API_URL, { method: 'GET' });
            inventory = await response.json();
            renderTable();
            hitungKalkulasiStatistik();
            hideLoading();
        } catch (error) {
            hideLoading();
            document.getElementById('error-state').classList.remove('hidden');
            console.error(error);
        }
    }

    function hitungKalkulasiStatistik() {
        document.getElementById('stat-total-items').innerText = inventory.length;
        let totalStok = 0, lowStokCount = 0;
        inventory.forEach(item => {
            totalStok += parseInt(item.stock);
            if(parseInt(item.stock) <= 5) lowStokCount++;
        });
        document.getElementById('stat-total-stock').innerText = totalStok;
        document.getElementById('stat-low-stock').innerText = lowStokCount;

        const warningBox = document.getElementById('stat-low-box');
        if(lowStokCount > 0) {
            warningBox.className = "bg-rose-100 text-rose-600 h-12 w-12 rounded-xl flex items-center justify-center text-xl";
        } else {
            warningBox.className = "bg-slate-100 text-slate-400 h-12 w-12 rounded-xl flex items-center justify-center text-xl";
        }
    }

    async function fetchRiwayat() {
        try {
            document.getElementById('riwayat-loading').classList.remove('hidden');
            const response = await apiFetch(`${API_URL}?action=riwayat`, { method: 'GET' });
            const riwayat = await response.json();
            document.getElementById('riwayat-loading').classList.add('hidden');
            const tbody = document.getElementById('riwayat-table-body');
            tbody.innerHTML = '';

            if (riwayat.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-slate-400"><i class="fa-solid fa-clock-rotate-left block text-xl mb-1"></i> Belum ada rekaman log mutasi stok harian.</td></tr>`;
                return;
            }

            riwayat.forEach(log => {
                const row = document.createElement('tr');
                row.className = "hover:bg-slate-50/80 transition text-slate-600";
                const badgeJenis = log.jenis_transaksi === 'masuk' 
                    ? '<span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-full font-semibold text-xs inline-flex items-center gap-1"><i class="fa-solid fa-circle-arrow-down"></i> Masuk</span>'
                    : '<span class="bg-rose-50 text-rose-700 border border-rose-200 px-2.5 py-1 rounded-full font-semibold text-xs inline-flex items-center gap-1"><i class="fa-solid fa-circle-arrow-up"></i> Keluar</span>';

                const nama = escapeHtml(log.nama_barang || '');
                const kode = escapeHtml(log.kode_barang || '');
                const ket  = escapeHtml(log.keterangan || '');

                row.innerHTML = `
                    <td class="px-6 py-4 text-slate-400 font-medium">${escapeHtml(log.tanggal)}</td>
                    <td class="px-6 py-4"><span class="bg-slate-100 text-slate-700 font-semibold px-2 py-1 rounded-md text-xs uppercase"><i class="fa-regular fa-user mr-1"></i>${escapeHtml(log.username)}</span></td>
                    <td class="px-6 py-4"><span class="font-mono bg-slate-50 border border-slate-200 text-slate-600 px-1.5 py-0.5 rounded text-xs mr-2">${kode}</span><span class="font-medium text-slate-900">${nama}</span></td>
                    <td class="px-6 py-4 text-center">${badgeJenis}</td>
                    <td class="px-6 py-4 text-center font-bold ${log.jenis_transaksi === 'masuk' ? 'text-emerald-600' : 'text-rose-600'}">${log.jenis_transaksi === 'masuk' ? '+' : '-'}${escapeHtml(log.jumlah)}</td>
                    <td class="px-6 py-4 text-slate-500 max-w-xs truncate">${ket || '<span class="text-slate-300 italic">Tanpa info</span>'}</td>
                `;
                tbody.appendChild(row);
            });
        } catch (error) {
            document.getElementById('riwayat-loading').classList.add('hidden');
            console.error(error);
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function populateDropdownBarang() {
        const select = document.getElementById('mutasi-barang-id');
        select.innerHTML = '<option value="" disabled selected>-- Pilih Item Inventori --</option>';
        inventory.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = `${item.code} - ${item.name} (${item.stock} ${item.unit} Tersedia)`;
            select.appendChild(opt);
        });
    }

    function renderTable() {
        const tableBody = document.getElementById('inventory-table-body');
        const emptyState = document.getElementById('empty-state');
        const searchQuery = document.getElementById('search-bar').value.toLowerCase();
        tableBody.innerHTML = '';
        
        const filteredItems = inventory.filter(item => 
            item.name.toLowerCase().includes(searchQuery) || item.code.toLowerCase().includes(searchQuery)
        );

        if (filteredItems.length === 0) {
            emptyState.classList.remove('hidden');
            return;
        } else { emptyState.classList.add('hidden'); }

        filteredItems.forEach((item) => {
            const row = document.createElement('tr');
            row.className = "hover:bg-slate-50/70 transition group";
            let aksiButton = '';
            if (role === 'admin') {
                aksiButton = `
                    <td class="px-6 py-3.5 text-center flex justify-center gap-1.5">
                        <button onclick="editItem('${item.id}')" class="text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white px-3 py-1.5 rounded-xl text-xs font-semibold border border-indigo-100 transition duration-150 flex items-center gap-1"><i class="fa-regular fa-pen-to-square"></i> Edit</button>
                        <button onclick="deleteItem('${item.id}')" class="text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white px-3 py-1.5 rounded-xl text-xs font-semibold border border-rose-100 transition duration-150 flex items-center gap-1"><i class="fa-regular fa-trash-can"></i> Hapus</button>
                    </td>`;
            }

            const kat = escapeHtml(item.kategori_name || '');
            const badgeStock = parseInt(item.stock) <= 5 
                ? `<span class="bg-rose-50 text-rose-600 border border-rose-100 px-2.5 py-1 rounded-xl text-xs font-bold inline-block min-w-[70px]"><i class="fa-solid fa-circle-exclamation mr-1 text-xs text-rose-500"></i>Kritis: ${escapeHtml(item.stock)} ${escapeHtml(item.unit)}</span>`
                : `<span class="bg-emerald-50 text-emerald-700 border border-emerald-100 px-2.5 py-1 rounded-xl text-xs font-bold inline-block min-w-[70px]"><i class="fa-solid fa-check mr-1 text-xs text-emerald-500"></i>Aman: ${escapeHtml(item.stock)} ${escapeHtml(item.unit)}</span>`;

            row.innerHTML = `
                <td class="px-6 py-4 font-mono font-bold text-slate-500 group-hover:text-indigo-600 transition-colors">${escapeHtml(item.code)}</td>
                <td class="px-6 py-4 font-semibold text-slate-900">${escapeHtml(item.name)}</td>
                <td class="px-6 py-4"><span class="bg-slate-100 text-slate-600 text-xs font-medium px-2 py-0.5 rounded">${kat}</span></td>
                <td class="px-6 py-4 text-center">${badgeStock}</td>
                <td class="px-6 py-4 text-right font-mono font-bold text-slate-900">Rp ${parseInt(item.price).toLocaleString('id-ID')}</td>
                ${aksiButton}
            `;
            tableBody.appendChild(row);
        });
    }

    async function saveMutasi(e) {
        e.preventDefault();
        const barang_id = document.getElementById('mutasi-barang-id').value;
        if(!barang_id) { alert("Pilih barang terlebih dahulu!"); return; }

        const jenis_transaksi = document.querySelector('input[name="jenis_transaksi"]:checked').value;
        const jumlah = document.getElementById('mutasi-jumlah').value;
        const keterangan = document.getElementById('mutasi-keterangan').value;
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch animate-spin mr-1"></i> Memproses...';

        try {
            const response = await apiFetch(`${API_URL}?action=transaksi`, {
                method: 'POST',
                body: JSON.stringify({ barang_id, username, jenis_transaksi, jumlah, keterangan })
            });
            const result = await response.json();

            if (response.ok) {
                document.getElementById('mutasi-form').reset();
                loadSemuaData();
            } else { alert(result.message); }
        } catch (error) { console.error(error); }
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-circle-check mr-1"></i> Eksekusi Mutasi';
    }

    async function saveItem(e) {
        e.preventDefault();
        if (role !== 'admin') return;

        const id = document.getElementById('item-id').value;
        const payload = {
            kategori_id: parseInt(document.getElementById('item-kategori').value),
            name: document.getElementById('item-name').value,
            stock: parseInt(document.getElementById('item-stock').value),
            unit: document.getElementById('item-unit').value,
            price: parseInt(document.getElementById('item-price').value)
        };
        if (!payload.kategori_id) { alert('Pilih kategori!'); return; }
        const btn = e.target.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch animate-spin mr-1"></i> Menyimpan...';

        const url = id ? `${API_URL}?id=${id}` : API_URL;
        const method = id ? 'PUT' : 'POST';

        try {
            const res = await apiFetch(url, { method, body: JSON.stringify(payload) });
            const result = await res.json();
            if (!res.ok) alert(result.message);
            loadSemuaData();
            resetForm();
        } catch (error) { console.error(error); }
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk mr-1"></i> Simpan';
    }

    function editItem(id) {
        const item = inventory.find(item => item.id == id);
        if (item) {
            document.getElementById('item-id').value = item.id;
            document.getElementById('item-kategori').value = item.kategori_id;
            document.getElementById('item-name').value = item.name;
            document.getElementById('item-stock').value = item.stock;
            document.getElementById('item-stock').disabled = true; 
            document.getElementById('item-unit').value = item.unit;
            document.getElementById('item-price').value = item.price;
            document.getElementById('form-title').innerText = "Edit Parameter Produk";
            document.getElementById('cancel-btn').classList.remove('hidden');
        }
    }

    async function deleteItem(id) {
        if (confirm("Pindahkan barang ke tempat sampah? Data bisa dipulihkan.")) {
            const res = await apiFetch(`${API_URL}?id=${id}`, { method: 'DELETE' });
            const result = await res.json();
            if (!res.ok) alert(result.message);
            loadSemuaData();
        }
    }

    function resetForm() {
        document.getElementById('inventory-form').reset();
        document.getElementById('item-id').value = '';
        document.getElementById('item-stock').disabled = false;
        document.getElementById('form-title').innerText = "Tambah Barang Baru";
        document.getElementById('cancel-btn').classList.add('hidden');
    }

    function logout() {
        window.location.href = 'logout.php';
    }

    window.onload = loadSemuaData;
</script>

<?php include 'components/footer.php'; ?>
