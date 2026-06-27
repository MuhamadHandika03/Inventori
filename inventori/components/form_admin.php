<div id="form-admin-container" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
    <div class="flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
        <i class="fa-solid fa-circle-plus text-indigo-600 text-lg"></i>
        <h2 id="form-title" class="text-lg font-bold text-slate-900">Tambah Barang Baru</h2>
    </div>
    <form id="inventory-form" onsubmit="saveItem(event)" class="space-y-4">
        <input type="hidden" id="item-id">
        <div>
            <label class="block text-slate-700 text-xs font-semibold mb-1.5">Kode Barang</label>
            <input type="text" id="item-code" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm" placeholder="Contoh: BRG-001">
        </div>
        <div>
            <label class="block text-slate-700 text-xs font-semibold mb-1.5">Nama Barang</label>
            <input type="text" id="item-name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 text-sm" placeholder="Contoh: Monitor Asus 24''">
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-700 text-xs font-semibold mb-1.5">Stok Awal</label>
                <input type="number" id="item-stock" min="0" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none text-sm" placeholder="0">
            </div>
            <div>
                <label class="block text-slate-700 text-xs font-semibold mb-1.5">Satuan</label>
                <input type="text" id="item-unit" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none text-sm" placeholder="Pcs / Unit">
            </div>
        </div>
        <div>
            <label class="block text-slate-700 text-xs font-semibold mb-1.5">Harga Satuan</label>
            <div class="relative">
                <span class="absolute left-3.5 top-2.5 text-sm text-slate-400 font-medium">Rp</span>
                <input type="number" id="item-price" min="0" required class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none text-sm" placeholder="0">
            </div>
        </div>
        <div class="flex gap-2 pt-2">
            <button type="submit" class="flex-grow bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl text-sm transition">
                <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan
            </button>
            <button type="button" id="cancel-btn" onclick="resetForm()" class="hidden bg-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl text-sm">
                Batal
            </button>
        </div>
    </form>
</div>