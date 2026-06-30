<div id="form-staff-container" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hidden">
    <div class="flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
        <i class="fa-solid fa-right-left text-emerald-600 text-lg"></i>
        <h2 class="text-lg font-bold text-slate-900">Update Mutasi Stok</h2>
    </div>
    <form id="mutasi-form" onsubmit="saveMutasi(event)" class="space-y-4">
        <div>
            <label class="block text-slate-700 text-xs font-semibold mb-1.5">Pilih Barang Gudang</label>
            <select id="mutasi-barang-id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none text-sm text-slate-700">
                </select>
        </div>
        <div>
            <label class="block text-slate-700 text-xs font-semibold mb-1.5">Jenis Transaksi</label>
            <div class="grid grid-cols-2 gap-3 mt-1">
                <label class="flex items-center justify-center gap-2 border border-slate-200 rounded-xl p-2.5 text-sm cursor-pointer hover:bg-slate-50 font-medium transition [&:has(input:checked)]:border-emerald-500 [&:has(input:checked)]:bg-emerald-50/50 [&:has(input:checked)]:text-emerald-700">
                    <input type="radio" name="jenis_transaksi" value="masuk" checked class="accent-emerald-600">
                    <span><i class="fa-solid fa-square-plus text-emerald-600"></i> Masuk</span>
                </label>
                <label class="flex items-center justify-center gap-2 border border-slate-200 rounded-xl p-2.5 text-sm cursor-pointer hover:bg-slate-50 font-medium transition [&:has(input:checked)]:border-rose-500 [&:has(input:checked)]:bg-rose-50/50 [&:has(input:checked)]:text-rose-700">
                    <input type="radio" name="jenis_transaksi" value="keluar" class="accent-rose-600">
                    <span><i class="fa-solid fa-square-minus text-rose-600"></i> Keluar</span>
                </label>
            </div>
        </div>
        <div>
            <label class="block text-slate-700 text-xs font-semibold mb-1.5">Jumlah Kuantitas</label>
            <input type="number" id="mutasi-jumlah" min="1" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none text-sm" placeholder="Jumlah barang">
        </div>
        <div>
            <label class="block text-slate-700 text-xs font-semibold mb-1.5">Keterangan / Deskripsi</label>
            <input type="text" id="mutasi-keterangan" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none text-sm" placeholder="Kiriman supplier / restock">
        </div>
        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-xl text-sm shadow-md shadow-emerald-100">
            <i class="fa-solid fa-circle-check mr-1"></i> Eksekusi Mutasi
        </button>
    </form>
</div>