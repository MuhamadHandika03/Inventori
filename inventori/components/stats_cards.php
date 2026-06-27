<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500 mb-1">Total Ragam Barang</p>
            <h3 id="stat-total-items" class="text-3xl font-bold text-slate-900">0</h3>
        </div>
        <div class="bg-indigo-50 text-indigo-600 h-12 w-12 rounded-xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-box"></i>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500 mb-1">Akumulasi Kuantitas Stok</p>
            <h3 id="stat-total-stock" class="text-3xl font-bold text-slate-900">0</h3>
        </div>
        <div class="bg-emerald-50 text-emerald-600 h-12 w-12 rounded-xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-cubes"></i>
        </div>
    </div>
    
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between col-span-1 sm:col-span-2 lg:col-span-1">
        <div>
            <p class="text-sm font-medium text-slate-500 mb-1">Barang Menipis (&le; 5)</p>
            <h3 id="stat-low-stock" class="text-3xl font-bold text-rose-600">0</h3>
        </div>
        <div id="stat-low-box" class="bg-rose-50 text-rose-600 h-12 w-12 rounded-xl flex items-center justify-center text-xl">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
    </div>
</div>