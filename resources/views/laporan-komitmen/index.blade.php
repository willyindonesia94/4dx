<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            {{ __('Laporan Komitmen Mingguan Sesi WIG') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Navigation Tabs -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
                <nav class="flex space-x-4">
                    <a href="{{ route('laporan.index') }}" class="text-slate-500 hover:text-slate-700 px-3 py-2 font-medium text-sm rounded-md transition-colors">
                        Laporan Bulanan & Historis
                    </a>
                    <a href="{{ route('laporan-komitmen.index') }}" class="bg-blue-50 text-blue-700 px-3 py-2 font-medium text-sm rounded-md transition-colors">
                        Laporan Komitmen (Mingguan)
                    </a>
                </nav>
            </div>

            <!-- Filter Card -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <form action="{{ route('laporan-komitmen.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tahun</label>
                        <select name="tahun" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" onchange="this.form.submit()">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $filterYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Bulan</label>
                        <select name="bulan" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" onchange="this.form.submit()">
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ $filterMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Minggu Ke-</label>
                        <select name="minggu_ke" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" onchange="this.form.submit()">
                            @foreach($weeks as $week)
                                <option value="{{ $week }}" {{ $filterWeek == $week ? 'selected' : '' }}>Minggu {{ $week }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Pilih WIG</label>
                        <select name="wig_id" class="w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500" onchange="this.form.submit()">
                            @foreach($wigs as $wig)
                                <option value="{{ $wig->id }}" {{ $filterWig == $wig->id ? 'selected' : '' }}>{{ Str::limit($wig->judul, 60) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end space-x-2">
                        <button type="button" onclick="loadPreview()" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-semibold py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                            <span>Tampilkan</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Preview & Export Card -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Pratinjau Laporan</h3>
                    <form action="{{ route('laporan-komitmen.export') }}" method="POST" class="flex items-center space-x-2">
                        @csrf
                        <input type="hidden" name="tahun" value="{{ $filterYear }}">
                        <input type="hidden" name="bulan" value="{{ $filterMonth }}">
                        <input type="hidden" name="minggu_ke" value="{{ $filterWeek }}">
                        <input type="hidden" name="wig_id" value="{{ $filterWig }}">
                        <button type="submit" formaction="{{ route('laporan-komitmen.export-word') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors flex items-center space-x-2 mr-2"> <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg> <span>Download Word</span> </button> <button type="submit" class="bg-red-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Download PDF</span>
                        </button>
                    </form>
                </div>

                <div id="preview-container" class="overflow-x-auto border border-gray-200 rounded-lg bg-gray-50 p-4 min-h-[300px] flex items-center justify-center">
                    <div class="text-gray-400">Klik "Tampilkan" untuk memuat pratinjau data.</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function loadPreview() {
            const container = document.getElementById('preview-container');
            container.innerHTML = '<div class="text-gray-500 animate-pulse flex flex-col items-center"><svg class="animate-spin h-8 w-8 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Memuat Pratinjau...</span></div>';
            
            const params = new URLSearchParams({
                tahun: document.querySelector('select[name="tahun"]').value,
                bulan: document.querySelector('select[name="bulan"]').value,
                minggu_ke: document.querySelector('select[name="minggu_ke"]').value,
                wig_id: document.querySelector('select[name="wig_id"]').value,
            });

            fetch('{{ route("laporan-komitmen.preview") }}?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    container.innerHTML = data.html;
                    container.classList.remove('flex', 'items-center', 'justify-center');
                })
                .catch(error => {
                    container.innerHTML = '<div class="text-red-500">Terjadi kesalahan saat memuat data.</div>';
                });
        }

        // Auto load on init if we have a valid filter combination
        @if($previewData)
        document.addEventListener("DOMContentLoaded", function() {
            loadPreview();
        });
        @endif
    </script>
</x-app-layout>






