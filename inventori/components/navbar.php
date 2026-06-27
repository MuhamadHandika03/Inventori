<nav class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center max-w-7xl">
        <div class="flex items-center gap-3">
            <div class="bg-blue-600 text-white p-2 rounded-xl shadow-md shadow-blue-200">
                <i class="fa-solid fa-boxes-stacked text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-blue-600">Stockify <span class="text-indigo-600 font-medium text-sm bg-indigo-50 px-2 py-0.5 rounded-md ml-1">v2.0</span></h1>
                <p class="text-xs text-slate-500">Sistem Manajemen Inventori Real-time</p>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="hidden sm:flex flex-col text-right">
                <span id="display-user" class="text-sm font-semibold text-slate-900 uppercase"></span>
                <span class="text-xs text-indigo-600 font-medium flex items-center justify-end gap-1">
                    <i class="fa-solid fa-circle-user"></i> Sesi Aktif
                </span>
            </div>
            <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
            <button onclick="logout()" class="flex items-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-600 text-sm font-semibold px-4 py-2.5 rounded-xl transition duration-200 group">
                <i class="fa-solid fa-arrow-right-from-bracket group-hover:translate-x-0.5 transition-transform"></i>
                <span>Keluar</span>
            </button>
        </div>
    </div>
</nav>