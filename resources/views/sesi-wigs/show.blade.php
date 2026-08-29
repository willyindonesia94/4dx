<x-app-layout>
@php
$formatLmValue = function($value, $satuan) {
    if ($value === null || $value === '') return '-';
    if (trim($satuan) === '%') {
        $formatted = number_format((float)$value * 100, 2, ",", ".");
        $formatted = rtrim(rtrim($formatted, '0'), ',');
        return $formatted . '%';
    }
    return number_format((float)$value, 2, ",", ".");
};
@endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Filter Navigasi Sesi WIG -->
            <div class="bg-white px-6 py-4 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="font-bold text-gray-700">Pilih Sesi WIG:</span>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    @php
                        $allSesis = \App\Models\SesiWig::orderBy('tanggal_pelaksanaan', 'desc')->get();
                        $groupedSesis = $allSesis->groupBy(function($s) {
                            return strtoupper(\Carbon\Carbon::create(null, $s->bulan, 1)->locale('id')->translatedFormat('F') . ' ' . $s->tahun);
                        });
                        $currentMonthKey = strtoupper(\Carbon\Carbon::create(null, $sesi_wig->bulan, 1)->locale('id')->translatedFormat('F') . ' ' . $sesi_wig->tahun);
                    @endphp
                    
                    <select onchange="window.location.href=this.value" class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm w-full sm:w-64 font-medium text-gray-700">
                        <option disabled>Pilih Bulan...</option>
                        @foreach($groupedSesis as $monthKey => $sesisGroup)
                            @php
                                $firstSesiInMonth = $sesisGroup->last();
                            @endphp
                            <option value="{{ route('sesi-wigs.show', $firstSesiInMonth->id) }}" {{ $monthKey === $currentMonthKey ? 'selected' : '' }}>
                                {{ $monthKey }}
                            </option>
                        @endforeach
                    </select>

                    <select onchange="window.location.href=this.value" class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm w-full sm:w-72 font-medium text-gray-700">
                        <option disabled>Pilih Sesi pada {{ $currentMonthKey }}...</option>
                        @foreach($groupedSesis[$currentMonthKey]->sortBy('tanggal_pelaksanaan') ?? collect() as $s)
                            <option value="{{ route('sesi-wigs.show', $s->id) }}" {{ $s->id == $sesi_wig->id ? 'selected' : '' }}>
                                {{ $s->nama_sesi }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Session Info Banner -->
            <div class="bg-gradient-to-r from-blue-900 to-indigo-800 overflow-hidden shadow-xl sm:rounded-2xl relative border border-blue-800">
                <div class="absolute top-0 right-0 -mt-8 -mr-8 w-48 h-48 bg-white opacity-5 rounded-full transform scale-150 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-32 h-32 bg-cyan-400 opacity-10 rounded-full transform scale-150 pointer-events-none"></div>
                
                <div class="p-8 text-white relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-3xl font-extrabold tracking-tight text-white drop-shadow-md">{{ $sesi_wig->nama_sesi }}</h3>
                            @if($sesi_wig->tipe_sesi == 'Mingguan')
                                <span class="px-3 py-1 text-xs leading-5 font-bold rounded-full bg-green-500 text-white shadow-sm uppercase tracking-wider">Mingguan</span>
                            @else
                                <span class="px-3 py-1 text-xs leading-5 font-bold rounded-full bg-purple-500 text-white shadow-sm uppercase tracking-wider">Bulanan</span>
                            @endif
                        </div>
                        <p class="text-cyan-300 font-medium text-lg flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ \Carbon\Carbon::parse($sesi_wig->tanggal_pelaksanaan)->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-blue-200 uppercase tracking-wider mb-1">Level Diundang</p>
                        <div class="flex gap-2 justify-end">
                            @foreach($sesi_wig->level_terlibat ?? [] as $lvl)
                                <span class="bg-white/20 px-3 py-1 rounded-lg text-sm font-bold shadow-sm backdrop-blur-sm">{{ $lvl }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Presenter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 mb-6" x-data="{ showDrawModal: false }">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-4">
                        <h4 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                            Presenter Sesi Ini
                        </h4>
                        @if(!$isUlpLevel && $canEditSesiWig)
                        <button @click="showDrawModal = true" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-all flex items-center gap-2 mt-4 md:mt-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Pilih Presenter
                        </button>
                        @endif
                    </div>

                    @if(isset($presenters) && $presenters->count() > 0)
                        <div class="flex flex-wrap gap-4 mt-4">
                            @foreach($presenters as $p)
                                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-4">
                                    <div class="bg-amber-100 p-3 rounded-full text-amber-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-amber-600 font-bold uppercase">{{ $p->type }}</div>
                                        <div class="text-lg font-bold text-gray-800">{{ $p->name }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 mt-4 text-gray-500 italic bg-gray-50 rounded-xl border border-dashed border-gray-300">
                            Belum ada presenter yang dipilih untuk sesi ini. Klik tombol pilih untuk menentukan presenter.
                        </div>
                    @endif
                </div>

                <!-- Modal Pilih Presenter -->
                <template x-teleport="body">
                    <div x-show="showDrawModal" style="display: none; background-color: rgba(17, 24, 39, 0.7); backdrop-filter: blur(8px);" class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div @click.away="showDrawModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
                            
                            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                                    Pilih Presenter WIG
                                </h3>
                                <button type="button" @click="showDrawModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <div class="p-6">
                                <form action="{{ route('sesi-wigs.set-presenter', $sesi_wig->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih UP3 (Opsional)</label>
                                        <select name="up3_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">-- Pilih UP3 --</option>
                                            @foreach($up3s as $up3)
                                                <option value="{{ $up3->id }}" {{ $presenters->where('id', $up3->id)->count() > 0 ? 'selected' : '' }}>{{ $up3->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="mb-6">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih ULP (Opsional)</label>
                                        <select name="ulp_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">-- Pilih ULP --</option>
                                            @foreach($allUlps as $ulp)
                                                <option value="{{ $ulp->id }}" {{ $presenters->where('id', $ulp->id)->count() > 0 ? 'selected' : '' }}>{{ $ulp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex justify-end gap-3">
                                        <button type="button" @click="showDrawModal = false" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold rounded-lg transition-colors">Batal</button>
                                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md transition-colors">Simpan Presenter</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Notes Section (Komitmen & Evaluasi) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100" x-data="{ showModal: false, showPrevModal: false }">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <!-- Button to view previous evaluation -->
                        <button @click="showPrevModal = true" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2.5 px-6 rounded-lg shadow-sm transition-all flex items-center gap-2 w-full sm:w-auto justify-center">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Lihat Notulensi Sebelumnya
                        </button>

                        <!-- Button to write new notes -->
                        @if($canEditSesiWig)
                        <button @click="showModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-md transition-all flex items-center gap-2 w-full sm:w-auto justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            Tulis Notulensi Sesi Ini
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Modal View Evaluasi Sebelumnya -->
                <template x-teleport="body">
                    <div x-show="showPrevModal" style="display: none; background-color: rgba(17, 24, 39, 0.5); backdrop-filter: blur(8px);" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div @click.away="showPrevModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">
                            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Evaluasi & Komitmen Sesi Sebelumnya
                                </h3>
                                <button type="button" @click="showPrevModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                            <div class="p-6 overflow-y-auto bg-white min-h-[300px]">
                                @if($previousSesi)
                                    <div class="prose prose-sm max-w-none text-gray-700">
                                        {!! $previousSesi->komitmen ?: '<p class="text-gray-400 italic">Tidak ada catatan dari sesi sebelumnya.</p>' !!}
                                    </div>
                                @else
                                    <div class="text-center text-gray-500 py-10 italic flex flex-col items-center justify-center h-full">
                                        <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                        Belum ada sesi WIG sebelumnya.
                                    </div>
                                @endif
                            </div>
                            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
                                <button type="button" @click="showPrevModal = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 shadow-sm transition-all">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Modal Tulis Notulensi -->
                <template x-teleport="body">
                    <div x-show="showModal" style="display: none; background-color: rgba(17, 24, 39, 0.5); backdrop-filter: blur(8px);" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div @click.away="showModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-5xl overflow-hidden flex flex-col h-[90vh]">
                            <form action="{{ route('sesi-wigs.update-notes', $sesi_wig->id) }}" method="POST" id="form-notulensi" class="flex flex-col h-full min-h-0">
                                @csrf
                                @method('PUT')
                                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2" id="modal-title">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Notulensi & Komitmen Sesi Ini
                                    </h3>
                                    <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                                        <span class="sr-only">Close</span>
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                <div class="p-6 flex-grow overflow-y-auto bg-gray-50/50 min-h-0 flex flex-col">
                                    <!-- Editor Notulensi -->
                                    <div class="flex flex-col flex-grow min-h-0">
                                        <div class="flex justify-between items-center mb-3">
                                            <div class="text-sm text-gray-600">Gunakan tanda <strong>@</strong> untuk menyebut (mention) Unit.</div>
                                            <select id="template-selector" onchange="insertTemplate(this.value)" class="text-sm rounded-md border-gray-300 py-1.5 pl-3 pr-8 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-gray-700 font-medium bg-white">
                                                <option value="">-- Pilih Template Dokumen --</option>
                                                <option value="standar">Berita Acara & Notulensi Standar</option>
                                                <option value="evaluasi">Formulir Evaluasi & Hambatan</option>
                                                <option value="kosong">Kosongkan Editor</option>
                                            </select>
                                        </div>
                                        <div class="flex-grow min-h-0 flex flex-col">
                                            <textarea name="komitmen" id="notulensi-editor" class="flex-grow">{{ old('komitmen', $sesi_wig->komitmen) }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-between gap-3 items-center flex-shrink-0">
                                    <button type="button" onclick="exportToPDF()" class="px-4 py-2 bg-rose-600 border border-transparent rounded-lg text-white font-medium hover:bg-rose-700 shadow-sm transition-all flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Ekspor PDF
                                    </button>
                                    <div class="flex gap-3">
                                        <button type="button" @click="showModal = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 shadow-sm transition-all">
                                            Batal
                                        </button>
                                        <button type="submit" onclick="tinymce.triggerSave();" class="px-5 py-2.5 bg-indigo-600 border border-transparent rounded-lg text-white font-medium hover:bg-indigo-700 shadow-sm transition-all flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Simpan Catatan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </template>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <div class="w-full space-y-6">
                    <!-- WIG Cards -->
            <div class="mb-6">
                <div class="flex flex-col md:flex-row justify-between items-center mb-4 px-2">
                    <h3 class="text-xl font-bold text-gray-800">Capaian WIG s.d Tanggal {{ \Carbon\Carbon::parse($sesi_wig->tanggal_pelaksanaan)->format('d/m/Y') }}</h3>
                </div>
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Scoreboard WIG {{ $isUlpLevel ? 'ULP' : ($isUp3Level ? 'UP3' : 'UID') }} (Matrix)
                    </h3>
                    <div class="space-y-4" x-data="{ activeTab: {{ $wigs->first()->id ?? 'null' }} }">
                        <!-- TABS -->
                        @if($wigs->count() > 0)
                            <div class="flex overflow-x-auto whitespace-nowrap gap-2 mb-2 border-b border-gray-200 pb-3 custom-scrollbar" style="-webkit-overflow-scrolling: touch;">
                                @foreach($wigs as $wig)
                                    <button @click="activeTab = {{ $wig->id }}"
                                            :style="activeTab === {{ $wig->id }} ? 'background-color: #0b2256; color: white; border-color: #0b2256; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);' : 'background-color: #f3f4f6; color: #4b5563; border-color: #d1d5db;'"
                                            class="px-4 py-2 rounded-md text-xs font-bold transition-all border outline-none hover:bg-gray-200 flex-shrink-0">
                                        {{ $wig->judul }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @forelse($wigs as $wig)
                            @php
                                $pctUid = $wig->capaian;
                                $isExceed = $pctUid >= 100;
                                $wigLms = $lms->where('wig_id', $wig->id);
                                $bulanT = \Carbon\Carbon::parse($sesi_wig->tanggal_pelaksanaan)->month;
                                
                                $namaBulanTarget = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$targetBulan - 1] ?? '';
                                $namaBulanPrev = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$prevBulan - 1] ?? '';
                            @endphp
                            
                            <div x-show="activeTab === {{ $wig->id }}" x-cloak class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm transition-all duration-300">
                                <!-- Infografis WIG & LM -->
                                <div class="flex flex-col xl:flex-row items-start gap-5 mb-6">
                                    <!-- KIRI: WIG Card & Tabel WIG -->
                                    <div class="w-full xl:w-[55%] flex flex-col gap-4">
                                        <!-- WIG Card -->
                                        <div class="w-full bg-white rounded-lg shadow-sm border border-[#0b2256] overflow-hidden flex flex-col">
                                            <div class="bg-[#0b2256] text-white px-3 py-2 text-[10px] font-bold uppercase truncate">
                                                WIG PERFORMANCE | {{ $isExceed ? 'EXCEEDED TARGET' : 'PERFORMANCE WATCH' }}
                                            </div>
                                            <div class="p-3 flex-1 flex flex-col sm:flex-row">
                                                <div class="w-full sm:w-1/2 text-center flex flex-col justify-center items-center px-2 py-2">
                                                    <div class="text-2xl font-bold {{ $isExceed ? 'text-green-600' : 'text-red-600' }}">{{ number_format($pctUid, 2) }} %</div>
                                                    <div class="text-[10px] font-bold text-gray-700 mt-1">Capaian WIG {{ $isUlpLevel ? 'ULP' : ($isUp3Level ? 'UP3' : 'UID Jabar') }}</div>
                                                    <div class="text-[9px] text-gray-500 mt-1">Target: {{ number_format($wig->total_target ?? 0, 2) }}</div>
                                                    <div class="text-[9px] text-gray-500">Realisasi: {{ number_format($wig->total_realisasi ?? 0, 2) }}</div>
                                                </div>
                                                <div class="w-full sm:w-1/2 border-t sm:border-t-0 sm:border-l border-gray-200 mt-2 sm:mt-0 pt-2 sm:pt-0 sm:pl-3 flex flex-col justify-end">
                                                    <div class="text-[9px] font-bold text-gray-600 text-center mb-1.5">TREND CAPAIAN WIG (%)</div>
                                                    <div class="flex items-end justify-between h-10 gap-[1px] mt-auto">
                                                        @for($i=1; $i<=$bulanT; $i++)
                                                            <div class="bg-blue-500 flex-1 rounded-t-sm" style="height: {{ max(4, min(100, $pctUid)) / 2.5 }}px;"></div>
                                                        @endfor
                                                    </div>
                                                    <div class="flex justify-between text-[8px] text-gray-400 font-bold mt-1">
                                                        <span>JAN</span>
                                                        <span>..</span>
                                                        <span>{{ strtoupper(substr(\Carbon\Carbon::parse($sesi_wig->tanggal_pelaksanaan)->translatedFormat('F'),0,3)) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Tabel WIG per UP3 -->
                                        <div class="overflow-x-auto border border-gray-300 rounded-md bg-white shadow-sm">
                                            <table class="w-full text-xs text-left">
                                                <thead class="bg-[#d9edf7] text-gray-800 uppercase font-bold text-[10px] border-b border-gray-300">
                                                    <tr>
                                                        <th rowspan="2" class="px-2 py-1.5 border-r border-gray-300 text-center align-middle">UNIT</th>
                                                        <th colspan="3" class="px-2 py-1.5 border-r border-gray-300 text-center border-b">{{ strtoupper($namaBulanPrev) }}</th>
                                                        <th colspan="3" class="px-2 py-1.5 text-center border-b">{{ strtoupper($namaBulanTarget) }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="px-2 py-1 border-r border-gray-300 text-center bg-[#eef7fc]">Target</th>
                                                        <th class="px-2 py-1 border-r border-gray-300 text-center bg-[#eef7fc]">Realisasi</th>
                                                        <th class="px-2 py-1 border-r border-gray-300 text-center bg-[#eef7fc]">Capaian (%)</th>
                                                        <th class="px-2 py-1 border-r border-gray-300 text-center">Target</th>
                                                        <th class="px-2 py-1 border-r border-gray-300 text-center">Realisasi</th>
                                                        <th class="px-2 py-1 text-center">Capaian (%)</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    <!-- UID Row -->
                                                    <tr class="bg-[#fcf8e3]">
                                                        <td class="px-2 py-1.5 border-r border-gray-300 font-bold whitespace-nowrap">UID Jawa Barat</td>
                                                        <!-- prev -->
                                                        <td class="px-2 py-1.5 border-r border-gray-300 text-right font-bold text-gray-500">-</td>
                                                        <td class="px-2 py-1.5 border-r border-gray-300 text-right font-bold text-gray-500">-</td>
                                                        <td class="px-2 py-1.5 border-r border-gray-300 text-right font-bold text-gray-500">-</td>
                                                        <!-- current -->
                                                        <td class="px-2 py-1.5 border-r border-gray-300 text-right font-bold">{{ number_format($wig->total_target ?? 0, 2) }}</td>
                                                        <td class="px-2 py-1.5 border-r border-gray-300 text-right font-bold">{{ number_format($wig->total_realisasi ?? 0, 2) }}</td>
                                                        <td class="px-2 py-1.5 text-right font-bold">{{ number_format($pctUid, 2) }}%</td>
                                                    </tr>
                                                    <!-- UP3 Rows -->
                                                    @foreach($filteredUp3sByWig[$wig->id] as $up3)
                                                        @php
                                                            $uData = $wigUnitData[$wig->id][$up3->id] ?? null;
                                                        @endphp
                                                        <tr class="hover:bg-gray-50">
                                                            <td class="px-2 py-1.5 border-r border-gray-300 whitespace-nowrap">{{ $up3->name }}</td>
                                                            <td class="px-2 py-1.5 border-r border-gray-300 text-right bg-[#eef7fc]/30">{{ number_format($uData['prev']['t'] ?? 0, 2) }}</td>
                                                            <td class="px-2 py-1.5 border-r border-gray-300 text-right bg-[#eef7fc]/30">{{ number_format($uData['prev']['r'] ?? 0, 2) }}</td>
                                                            <td class="px-2 py-1.5 border-r border-gray-300 text-right font-semibold bg-[#eef7fc]/30 {{ ($uData['prev']['pct'] ?? 0) >= 100 ? 'text-green-600' : '' }}">{{ number_format($uData['prev']['pct'] ?? 0, 2) }}%</td>
                                                            <td class="px-2 py-1.5 border-r border-gray-300 text-right">{{ number_format($uData['cur']['t'] ?? 0, 2) }}</td>
                                                            <td class="px-2 py-1.5 border-r border-gray-300 text-right">{{ number_format($uData['cur']['r'] ?? 0, 2) }}</td>
                                                            <td class="px-2 py-1.5 text-right font-semibold {{ ($uData['cur']['pct'] ?? 0) >= 100 ? 'text-green-600' : '' }}">{{ number_format($uData['cur']['pct'] ?? 0, 2) }}%</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <!-- KANAN: LM Cards Container -->
                                    <div class="w-full xl:w-[45%] bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                        <div class="text-[10px] font-bold text-[#0b2256] uppercase mb-3 border-b border-gray-200 pb-2">PERFORMA LEAD MEASURE</div>
                                        @if($wigLms->count() > 0)
                                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 0.75rem;">
                                                @foreach($wigLms as $idx => $lm)
                                                    @php
                                                        $lmPct = $lm->capaian;
                                                        $lmColor = $lmPct >= 100 ? 'text-green-600' : 'text-red-600';
                                                        $lmBadge = $lmPct >= 100 ? 'bg-green-500' : 'bg-red-500';
                                                        $lmStatus = $lmPct >= 100 ? 'EXCEEDED TARGET' : 'PERFORMANCE WATCH';
                                                        
                                                        // Fetch LM menang kalah data which was built in controller
                                                        $mkLevel = $isUlpLevel || $isUp3Level ? 'ulp' : 'up3';
                                                        $menang = count($lmMenangKalah[$lm->id][$mkLevel]['menang'] ?? []);
                                                        $kalah = count($lmMenangKalah[$lm->id][$mkLevel]['kalah'] ?? []);
                                                    @endphp
                                                    <div class="bg-gray-50 border border-gray-300 rounded-lg flex flex-col items-center justify-center p-3 text-center border-t-4 {{ $lmPct >= 100 ? 'border-t-green-500' : 'border-t-red-500' }} shadow-sm hover:shadow-md transition-shadow">
                                                        <div class="text-[10px] font-bold text-gray-700 leading-tight mb-2 h-[26px] overflow-hidden line-clamp-2" title="{{ $lm->judul_lm }}">LM {{ $loop->iteration }} - {{ preg_replace('/^LM\s*-?\s*\d+\s*/i', '', $lm->judul_lm) }}</div>
                                                        <div class="text-xl font-bold {{ $lmColor }} mb-1">{{ number_format($lmPct, 2) }} %</div>
                                                        <div class="text-[9px] font-bold text-gray-500 mb-2">Target: {{ $formatLmValue($lm->total_target, $lm->satuan->name ?? '') }} | Real: {{ $formatLmValue($lm->total_realisasi, $lm->satuan->name ?? '') }}</div>
                                                        <div class="{{ $lmBadge }} text-white text-[9px] font-bold px-3 py-0.5 rounded-full mb-2">{{ $lmStatus }}</div>
                                                        <div class="text-[9px] font-bold text-gray-800">{{ strtoupper($mkLevel) }} Menang: {{ $menang }} | {{ strtoupper($mkLevel) }} Kalah: {{ $kalah }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="flex flex-col items-center justify-center bg-gray-50 border border-gray-200 rounded-lg text-gray-400 italic text-xs py-8">
                                                Tidak ada data Lead Measure yang terkait dengan WIG ini.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-gray-100">Belum ada WIG yang didefinisikan.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- LM Table -->
            <div class="mb-6" x-data="{ selectedWig: {{ $wigs->where('masterLms', '!=', '[]')->first()->id ?? 'null' }} }">
                <div class="flex flex-col md:flex-row justify-between items-center mb-4 px-2">
                    <h3 class="text-xl font-bold text-gray-800">Capaian Lead Measure s.d Tanggal {{ \Carbon\Carbon::parse($sesi_wig->tanggal_pelaksanaan)->format('d/m/Y') }}</h3>
                </div>
                
                <!-- MATRIX LM CONTAINER -->
                <div>
                    @foreach($wigs as $wig)
                        @php $wigLms = $lms->where('wig_id', $wig->id); @endphp
                        @if($wigLms->count() > 0)
                            <div class="cursor-pointer group flex items-center justify-between bg-white border border-gray-200 rounded-lg p-3 md:p-4 mb-3 shadow-sm hover:shadow-md transition-all" @click="selectedWig = selectedWig === {{ $wig->id }} ? null : {{ $wig->id }}">
                                <h4 class="text-base md:text-lg font-bold text-indigo-900">{{ $wig->judul }}</h4>
                                <svg class="w-5 h-5 md:w-6 md:h-6 text-gray-400 group-hover:text-indigo-600 transform transition-transform duration-300" :class="selectedWig === {{ $wig->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                            
                            <!-- Container Tabel LM -->
                            <div x-show="selectedWig === {{ $wig->id }}" x-collapse x-cloak>
                                @foreach($wigLms as $lm)
                                    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 items-start mb-8">
                                        <!-- Kiri: Tabel LM -->
                                        <div class="xl:col-span-3 w-full">
                                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                                                <div class="p-4 bg-gray-50 border-b border-gray-200">
                                                    <h5 class="font-bold text-gray-800">{{ $lm->judul_lm }}</h5>
                                                </div>
                                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-300 text-xs">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th rowspan="2" class="px-4 py-3 border border-gray-300 text-left font-bold text-gray-800 sticky left-0 bg-gray-100 z-10">UNIT</th>
                                                @foreach($sesi_wigs_matrix as $sw)
                                                    <th colspan="8" class="px-4 py-2 border border-gray-300 text-center font-bold text-gray-800 bg-indigo-50">
                                                        {{ strtolower(trim($sw->tipe_sesi)) === 'mingguan' ? 'MINGGU ' . $sw->minggu_ke : strtoupper($sw->tipe_sesi) }}
                                                    </th>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                @foreach($sesi_wigs_matrix as $sw)
                                                    <th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">TARGET AWAL</th>
                                                    <th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">TARGET+<br>CARRY OVER</th>
                                                    <th colspan="2" class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">KOMITMEN</th>
                                                    <th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">REALISASI</th>
                                                    <th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">PENCAPAIAN (%)</th>
                                                    <th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50 w-24">CARRY OVER</th>
                                                    <th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50 w-12" title="Tren terhadap Realisasi Minggu Sebelumnya">TREN</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <!-- Baris UID Jabar (Total Keseluruhan) -->
                                            @if(!$isUlpLevel)
                                            <tr class="bg-indigo-50 border-b-2 border-indigo-200">
                                                <td class="px-4 py-2 border border-gray-300 font-black text-indigo-900 whitespace-nowrap sticky left-0 bg-indigo-50 z-10 uppercase">
                                                    UID JABAR
                                                </td>
                                                @foreach($sesi_wigs_matrix as $sw)
                                                    @php
                                                        // Pull UID target and realisasi from unit_id = 1 instead of summing UP3
                                                        $uidTarget = $matrixTargets[$lm->id][1][$sw->id] ?? 0;
                                                        $uidRealisasi = $matrixRealisasi[$lm->id][1][$sw->id] ?? 0;
                                                        
                                                        $up3Count = count($up3s);
                                                        $isPercent = trim($lm->satuan->name ?? '') === '%';
                                                        
                                                        // Fallback target jika kosong
                                                        if ($uidTarget == 0) {
                                                            foreach($filteredUp3sByWig[$wig->id] as $up3Unit) {
                                                                $uidTarget += $matrixTargets[$lm->id][$up3Unit->id][$sw->id] ?? 0;
                                                            }
                                                            if ($isPercent && $up3Count > 0) {
                                                                $uidTarget = $uidTarget / $up3Count;
                                                            }
                                                        }
                                                        
                                                        // Fallback realisasi jika kosong (biasanya UP3 mengisi masing-masing sehingga unit 1 kosong)
                                                        if ($uidRealisasi == 0) {
                                                            foreach($filteredUp3sByWig[$wig->id] as $up3Unit) {
                                                                $uidRealisasi += $matrixRealisasi[$lm->id][$up3Unit->id][$sw->id] ?? 0;
                                                            }
                                                            if ($isPercent && $up3Count > 0) {
                                                                $uidRealisasi = $uidRealisasi / $up3Count;
                                                            }
                                                        }
                                                        
                                                        $uidPencapaian = 0;
                                                        if ($uidTarget > 0) {
                                                            if (strtolower($lm->polaritas) === 'negatif' || $lm->polaritas === '3') {
                                                                $uidPencapaian = round(($uidTarget / max(0.0001, $uidRealisasi)) * 100, 2);
                                                            } else {
                                                                $uidPencapaian = round(($uidRealisasi / $uidTarget) * 100, 2);
                                                            }
                                                        }
                                                        $uidBgColor = 'bg-red-500 text-white';
                                                        if ($uidPencapaian >= 100) {
                                                            $uidBgColor = 'bg-green-500 text-white'; // No komitmen on UID level currently
                                                        }
                                                        $uidCarryOver = max(0, $uidTarget - $uidRealisasi);
                                                        
                                                        $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                        $prevUidTarget = 0;
                                                        $prevUidRealisasi = 0;
                                                        if ($prevSw) {
                                                            $prevUidTarget = $matrixTargets[$lm->id][1][$prevSw->id] ?? 0;
                                                            $prevUidRealisasi = $matrixRealisasi[$lm->id][1][$prevSw->id] ?? 0;
                                                            
                                                            if ($prevUidTarget == 0) {
                                                                foreach($filteredUp3sByWig[$wig->id] as $up3Unit) {
                                                                    $prevUidTarget += $matrixTargets[$lm->id][$up3Unit->id][$prevSw->id] ?? 0;
                                                                }
                                                                if ($isPercent && $up3Count > 0) {
                                                                    $prevUidTarget = $prevUidTarget / $up3Count;
                                                                }
                                                            }
                                                            
                                                            if ($prevUidRealisasi == 0) {
                                                                foreach($filteredUp3sByWig[$wig->id] as $up3Unit) {
                                                                    $prevUidRealisasi += $matrixRealisasi[$lm->id][$up3Unit->id][$prevSw->id] ?? 0;
                                                                }
                                                                if ($isPercent && $up3Count > 0) {
                                                                    $prevUidRealisasi = $prevUidRealisasi / $up3Count;
                                                                }
                                                            }
                                                        }
                                                        $prevUidCarryOver = max(0, $prevUidTarget - $prevUidRealisasi);
                                                        $uidTargetPlusCarryOver = $uidTarget + $prevUidCarryOver;
                                                        
                                                        $uidTrendIcon = '<span class="text-gray-400">-</span>';
                                                        if ($prevSw) {
                                                            if ($uidRealisasi > $prevUidRealisasi) {
                                                                $uidTrendIcon = '<svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>';
                                                            } else if ($uidRealisasi < $prevUidRealisasi) {
                                                                $uidTrendIcon = '<svg class="w-5 h-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>';
                                                            } else {
                                                                $uidTrendIcon = '<svg class="w-5 h-5 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>';
                                                            }
                                                        }
                                                    @endphp
                                                    <td class="px-2 py-2 border border-gray-300 text-right font-black text-indigo-900">{{ $formatLmValue($uidTarget, $lm->satuan->name ?? '') }}</td>
                                                    <td class="px-2 py-2 border border-gray-300 text-right font-black text-purple-900 bg-purple-50">{{ $formatLmValue($uidTargetPlusCarryOver, $lm->satuan->name ?? '') }}</td>
                                                    <td class="px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50">-</td><td class="px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50">-</td>
                                                    <td class="px-2 py-2 border border-gray-300 text-right font-black text-indigo-900">{{ $formatLmValue($uidRealisasi, $lm->satuan->name ?? '') }}</td>
                                                    <td class="px-2 py-2 border border-gray-300 text-right font-black {{ $uidBgColor }}">{{ $uidPencapaian }}%</td>
                                                    <td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">{{ $uidCarryOver > 0 ? $formatLmValue($uidCarryOver, $lm->satuan->name ?? '') : '0' }}</td>
                                                    <td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">{!! $uidTrendIcon !!}</td>
                                                @endforeach
                                            </tr>
                                            @endif
                                            @foreach($filteredUp3sByWig[$wig->id] as $up3)
                                                @php
                                                    $ulps = $allUlps->where('parent_id', $up3->id);
                                                    $isExpanded = false;
                                                    $user = auth()->user();
                                                    if ($user && ($isUlpLevel || $user->hasRole('Staff ULP') || $ulps->contains('id', $user->unit_id) || $user->unit_id == $up3->id)) {
                                                        $isExpanded = true;
                                                    }
                                                @endphp
                                                <tr class="hover:bg-slate-200 transition-colors bg-slate-100 cursor-pointer up3-row" onclick="toggleUlps('{{$lm->id}}-{{$up3->id}}')">
                                                    <td class="px-4 py-2 border border-gray-300 font-bold text-indigo-900 whitespace-nowrap sticky left-0 bg-slate-100 z-10">
                                                        <div class="flex items-center justify-between">
                                                            <span>{{ $up3->name }}</span>
                                                            <svg id="icon-{{$lm->id}}-{{$up3->id}}" class="w-4 h-4 text-gray-500 transform transition-transform {{ $isExpanded ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                        </div>
                                                    </td>
                                                    @foreach($sesi_wigs_matrix as $sw)
                                                        @php
                                                            // Menampilkan rekap target dan realisasi UP3
                                                            $up3Target = $matrixTargets[$lm->id][$up3->id][$sw->id] ?? 0;
                                                            $up3Realisasi = $matrixRealisasi[$lm->id][$up3->id][$sw->id] ?? 0;
                                                            $up3Pencapaian = 0;
                                                            if ($up3Target > 0) {
                                                                if (strtolower($lm->polaritas) === 'negatif' || $lm->polaritas === '3') {
                                                                    $up3Pencapaian = round(($up3Target / max(0.0001, $up3Realisasi)) * 100, 2);
                                                                } else {
                                                                    $up3Pencapaian = round(($up3Realisasi / $up3Target) * 100, 2);
                                                                }
                                                            }
                                                            
                                                            $up3KomData = $matrixKomitmen[$lm->id][$up3->id][$sw->id] ?? null;
                                                            $up3KomVal = $up3KomData !== null ? floatval($up3KomData['komitmen']) : 0;
                                                            
                                                            $up3BgColor = 'bg-red-500 text-white';
                                                            if ($up3Pencapaian >= 100) {
                                                                if ($up3KomData !== null && $up3KomVal > 0 && $up3Realisasi < $up3KomVal) {
                                                                    $up3BgColor = 'bg-orange-500 text-white';
                                                                } else {
                                                                    $up3BgColor = 'bg-green-500 text-white';
                                                                }
                                                            }
                                                            $up3CarryOver = max(0, $up3Target - $up3Realisasi);
                                                            
                                                            $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                            $prevUp3Target = 0;
                                                            $prevUp3Realisasi = 0;
                                                            if ($prevSw) {
                                                                $prevUp3Target = $matrixTargets[$lm->id][$up3->id][$prevSw->id] ?? 0;
                                                                $prevUp3Realisasi = $matrixRealisasi[$lm->id][$up3->id][$prevSw->id] ?? 0;
                                                            }
                                                            $prevUp3CarryOver = max(0, $prevUp3Target - $prevUp3Realisasi);
                                                            $up3TargetPlusCarryOver = $up3Target + $prevUp3CarryOver;
                                                            $prevUp3Realisasi = 0;
                                                            $up3TrendIcon = '<span class="text-gray-400">-</span>';
                                                            if ($prevSw) {
                                                                $prevUp3Realisasi = $matrixRealisasi[$lm->id][$up3->id][$prevSw->id] ?? 0;
                                                                if ($up3Realisasi > $prevUp3Realisasi) {
                                                                    $up3TrendIcon = '<svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>';
                                                                } else if ($up3Realisasi < $prevUp3Realisasi) {
                                                                    $up3TrendIcon = '<svg class="w-5 h-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>';
                                                                } else {
                                                                    $up3TrendIcon = '<svg class="w-5 h-5 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>';
                                                                }
                                                            }
                                                        @endphp
                                                        <td class="px-2 py-2 border border-gray-300 text-right font-semibold">{{ $formatLmValue($up3Target, $lm->satuan->name ?? '') }}</td>
                                                        <td class="px-2 py-2 border border-gray-300 text-right font-semibold text-purple-900 bg-purple-50">{{ $formatLmValue($up3TargetPlusCarryOver, $lm->satuan->name ?? '') }}</td>
                                                                @php 
                                                                    $komData = $matrixKomitmen[$lm->id][$up3->id][$sw->id] ?? null;
                                                                    $hasKom = $komData !== null;
                                                                    $komitmenVal = $hasKom ? $komData['komitmen'] : '';

                                                                    $userAuth = auth()->user();
                                                                    $canEditUp3Komitmen = $canEditSesiWig;
                                                                    if ($userAuth && in_array(strtoupper(trim($up3->type)), ['UP2D', 'UP2K'])) {
                                                                        if ($userAuth->unit_id == $up3->id && (str_contains(strtoupper($userAuth->role_name ?? ''), 'UP2D') || str_contains(strtoupper($userAuth->role_name ?? ''), 'UP2K'))) {
                                                                            $canEditUp3Komitmen = true;
                                                                        }
                                                                    }
                                                                    
                                                                    // Calculate Target + Carry Over of NEXT week for UP3
                                                                    $nextSwObj = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke + 1)->first();
                                                                    $nextUp3Target = 0;
                                                                    if ($nextSwObj) {
                                                                        $nextUp3Target = $matrixTargets[$lm->id][$up3->id][$nextSwObj->id] ?? 0;
                                                                    }
                                                                    $carryOverFromThisWeek = max(0, $up3Target - $up3Realisasi);
                                                                    $targetCarryOverMingguDepan = $nextUp3Target + $carryOverFromThisWeek;
                                                                    
                                                                    $komBg = 'bg-slate-50';
                                                                    $komText = 'text-gray-700';
                                                                    
                                                                    if ($hasKom && $komitmenVal !== '' && $komitmenVal !== null) {
                                                                        if ((float)$komitmenVal < (float)$targetCarryOverMingguDepan) {
                                                                            $komBg = 'bg-red-500';
                                                                            $komText = 'text-white';
                                                                        }
                                                                    }
                                                                @endphp
                                                            <td class="px-2 py-2 border border-gray-300 text-center {{ $komBg }}">
                                                                <span class="text-xs font-semibold {{ $komText }}">{{ $komitmenVal !== '' && $komitmenVal !== null ? $formatLmValue($komitmenVal, $lm->satuan->name ?? '') : '-' }}</span>
                                                                </td>
                                                            <td class="px-2 py-2 border border-gray-300 text-center bg-slate-50 w-10">
                                                                <button type="button" 
                                                                    @click="window.dispatchEvent(new CustomEvent('open-komitmen', { detail: { sesi: {{ $sw->id }}, lm: {{ $lm->id }}, unit: {{ $up3->id }}, target: {{ $up3Target }}, realisasi: {{ $up3Realisasi }}, capai: {{ $up3Pencapaian }}, unitName: '{{ addslashes($up3->name) }}', lmName: '{{ addslashes($lm->judul_lm) }}', wigName: '{{ addslashes($wig->judul) }}', date: '{{ \Carbon\Carbon::parse($sw->tanggal_pelaksanaan)->format('d/m/Y') }}', readonly: {{ $canEditUp3Komitmen ? 'false' : 'true' }} } }))"
                                                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full transition-all shadow-sm focus:outline-none {{ $hasKom ? 'bg-green-100 text-green-600 hover:bg-green-200 border border-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-200' }}"
                                                                    title="{{ $hasKom ? ($canEditUp3Komitmen ? 'Edit Form Komitmen' : 'Lihat Komitmen') : ($canEditUp3Komitmen ? 'Isi Form Komitmen' : 'Belum Ada Komitmen') }}">
                                                                    @if($canEditUp3Komitmen)
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $hasKom ? 'M5 13l4 4L19 7' : 'M12 4v16m8-8H4' }}"></path></svg>
@else
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
@endif
                                                                </button>
                                                        </td>
                                                        <td class="px-2 py-2 border border-gray-300 text-right font-semibold">{{ $formatLmValue($up3Realisasi, $lm->satuan->name ?? '') }}</td>
                                                        <td class="px-2 py-2 border border-gray-300 text-right font-bold {{ $up3BgColor }}">{{ $up3Pencapaian }}%</td>
                                                        <td class="px-2 py-2 border border-gray-300 text-center bg-slate-50 text-xs font-semibold text-gray-700">
                                                            {{ $up3CarryOver > 0 ? $formatLmValue($up3CarryOver, $lm->satuan->name ?? '') : '0' }}
                                                        </td>
                                                        <td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">{!! $up3TrendIcon !!}</td>
                                                    @endforeach
                                                </tr>
                                                @foreach($ulps as $u)
                                                    <tr class="hover:bg-slate-50 transition-colors ulp-row-{{$lm->id}}-{{$up3->id}} {{ $isExpanded ? '' : 'hidden' }}">
                                                        <td class="px-4 py-2 border border-gray-300 font-medium text-gray-800 whitespace-nowrap sticky left-0 bg-white z-10 pl-8">
                                                            {{ $u->name }}
                                                        </td>
                                                        @foreach($sesi_wigs_matrix as $sw)
                                                            @php
                                                                $target = $matrixTargets[$lm->id][$u->id][$sw->id] ?? 0;
                                                                $realisasi = $matrixRealisasi[$lm->id][$u->id][$sw->id] ?? 0;
                                                                $pencapaian = 0;
                                                                if ($target > 0) {
                                                                    if (strtolower($lm->polaritas) === 'negatif' || $lm->polaritas === '3') {
                                                                        $pencapaian = round(($target / max(0.0001, $realisasi)) * 100, 2);
                                                                    } else {
                                                                        $pencapaian = round(($realisasi / $target) * 100, 2);
                                                                    }
                                                                }
                                                                $komitmenData = $matrixKomitmen[$lm->id][$u->id][$sw->id] ?? null;
                                                                $komitmenVal = $komitmenData ? $komitmenData['komitmen'] : '';
                                                                $carryOverVal = $komitmenData ? $komitmenData['carry_over'] : '';
                                                                
                                                                $bgColor = '';
                                                                if ($pencapaian < 100) {
                                                                    $bgColor = 'bg-red-500 text-white';
                                                                } else if ($pencapaian >= 100) {
                                                                    if ($komitmenVal !== '' && $realisasi < (float)$komitmenVal) {
                                                                        $bgColor = 'bg-orange-500 text-white';
                                                                    } else {
                                                                        $bgColor = 'bg-green-500 text-white';
                                                                    }
                                                                }
                                                                
                                                                $canEdit = false;
                                                                $user = auth()->user();
                                                                if ($user && $canEditSesiWig) {
                                                                    if ($user->hasRole('Super Admin') || ($user->hasRole('Staff ULP') && $user->unit_id == $u->id)) {
                                                                        $canEdit = true;
                                                                    }
                                                                }
                                                                
                                                                $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                                $prevRealisasi = 0;
                                                                $trendIcon = '<span class="text-gray-400">-</span>';
                                                                if ($prevSw) {
                                                                    $prevRealisasi = $matrixRealisasi[$lm->id][$u->id][$prevSw->id] ?? 0;
                                                                    if ($realisasi > $prevRealisasi) {
                                                                        $trendIcon = '<svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>';
                                                                    } else if ($realisasi < $prevRealisasi) {
                                                                        $trendIcon = '<svg class="w-5 h-5 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>';
                                                                    } else {
                                                                        $trendIcon = '<svg class="w-5 h-5 text-gray-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>';
                                                                    }
                                                                }
                                                                
                                                                $ulpCarryOver = max(0, $target - $realisasi);
                                                                
                                                                $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                                $prevUlpTarget = 0;
                                                                $prevUlpRealisasi = 0;
                                                                if ($prevSw) {
                                                                    $prevUlpTarget = $matrixTargets[$lm->id][$u->id][$prevSw->id] ?? 0;
                                                                    $prevUlpRealisasi = $matrixRealisasi[$lm->id][$u->id][$prevSw->id] ?? 0;
                                                                }
                                                                $prevUlpCarryOver = max(0, $prevUlpTarget - $prevUlpRealisasi);
                                                                $ulpTargetPlusCarryOver = $target + $prevUlpCarryOver;
                                                            @endphp
                                                            <td class="px-2 py-2 border border-gray-300 text-right">{{ $formatLmValue($target, $lm->satuan->name ?? '') }}</td>
                                                                <td class="px-2 py-2 border border-gray-300 text-right text-purple-900 bg-purple-50">{{ $formatLmValue($ulpTargetPlusCarryOver, $lm->satuan->name ?? '') }}</td>
                                                                @php 
                                                                    $komData = $matrixKomitmen[$lm->id][$u->id][$sw->id] ?? null;
                                                                    $hasKom = $komData !== null;
                                                                    $komitmenVal = $hasKom ? $komData['komitmen'] : '';
                                                                    
                                                                    $komBg = 'bg-slate-50';
                                                                    $komText = 'text-gray-700';
                                                                    $komInputClass = 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500';
                                                                    
                                                                    // Calculate Target + Carry Over of NEXT week
                                                                    $nextSwObj = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke + 1)->first();
                                                                    $nextTarget = 0;
                                                                    if ($nextSwObj) {
                                                                        $nextTarget = $matrixTargets[$lm->id][$u->id][$nextSwObj->id] ?? 0;
                                                                    }
                                                                    $carryOverFromThisWeek = max(0, $target - $realisasi);
                                                                    $targetCarryOverMingguDepan = $nextTarget + $carryOverFromThisWeek;
                                                                    
                                                                    if ($hasKom && $komitmenVal !== '' && $komitmenVal !== null) {
                                                                        if ((float)$komitmenVal < (float)$targetCarryOverMingguDepan) {
                                                                            $komBg = 'bg-red-500';
                                                                            $komText = 'text-white';
                                                                            $komInputClass = 'bg-red-50 text-red-900 border-red-300 focus:ring-red-500 focus:border-red-500';
                                                                        }
                                                                    }
                                                                @endphp
                                                            <td class="px-2 py-2 border border-gray-300 text-center {{ $komBg }}">
                                                                @if($canEdit)
                                                                        <input type="number" step="any" class="w-16 text-xs p-1 border rounded komitmen-input {{ $komInputClass }}" 
                                                                            data-lm="{{ $lm->id }}" data-unit="{{ $u->id }}" data-sesi="{{ $sw->id }}" data-type="komitmen"
                                                                            value="{{ $komitmenVal }}" placeholder="-">
                                                                    @else
                                                                        <span class="text-xs font-semibold {{ $komText }}">{{ $komitmenVal !== '' && $komitmenVal !== null ? $formatLmValue($komitmenVal, $lm->satuan->name ?? '') : '-' }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-2 py-2 border border-gray-300 text-center bg-slate-50 w-10">
                                                                    <button type="button" 
                                                                        @click="window.dispatchEvent(new CustomEvent('open-komitmen', { detail: { sesi: {{ $sw->id }}, lm: {{ $lm->id }}, unit: {{ $u->id }}, target: {{ $target }}, realisasi: {{ $realisasi }}, capai: {{ $pencapaian }}, unitName: '{{ addslashes($u->name) }}', lmName: '{{ addslashes($lm->judul_lm) }}', wigName: '{{ addslashes($wig->judul) }}', date: '{{ \Carbon\Carbon::parse($sw->tanggal_pelaksanaan)->format('d/m/Y') }}', readonly: {{ $canEditSesiWig ? 'false' : 'true' }} } }))"
                                                                        class="inline-flex items-center justify-center w-6 h-6 shrink-0 rounded-full transition-all shadow-sm focus:outline-none {{ $hasKom ? 'bg-green-100 text-green-600 hover:bg-green-200 border border-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-200' }}"
                                                                        title="{{ $hasKom ? ($canEditSesiWig ? 'Edit Form Komitmen' : 'Lihat Komitmen') : ($canEditSesiWig ? 'Isi Form Komitmen' : 'Belum Ada Komitmen') }}">
                                                                        @if($canEditSesiWig)
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $hasKom ? 'M5 13l4 4L19 7' : 'M12 4v16m8-8H4' }}"></path></svg>
@else
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
@endif
                                                                    </button>
                                                            </td>
                                                            <td class="px-2 py-2 border border-gray-300 text-right">{{ $formatLmValue($realisasi, $lm->satuan->name ?? '') }}</td>
                                                            <td class="px-2 py-2 border border-gray-300 text-right font-bold {{ $bgColor }}">
                                                                {{ $pencapaian }}%
                                                            </td>
                                                            <td class="px-2 py-2 border border-gray-300 text-center bg-slate-50 text-xs font-semibold text-gray-700">
                                                                {{ $ulpCarryOver > 0 ? $formatLmValue($ulpCarryOver, $lm->satuan->name ?? '') : '0' }}
                                                            </td>
                                                            <td class="px-2 py-2 border border-gray-300 text-center">{!! $trendIcon !!}</td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                                </div>
                                            </div>
                                        </div> <!-- End of col-span-3 -->

                                        <!-- Kanan: Menang/Kalah Widget per LM -->
                                        <div class="xl:col-span-1 w-full sticky top-6">
                                            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6" x-data="{ tab: '{{ $isUlpLevel ? 'ulp' : 'up3' }}' }">
                                                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                                    Menang Kalah LM
                                                </h3>
                                                
                                                <!-- Tabs -->
                                                <div class="flex space-x-1 bg-gray-100/50 p-1 rounded-lg mb-4 text-xs font-semibold">
                                                    @if(!$isUlpLevel)
                                                    <button @click="tab = 'up3'" :class="tab === 'up3' ? 'bg-white shadow text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-1.5 rounded-md transition-all">UP3</button>
                                                    @endif
                                                    <button @click="tab = 'ulp'" :class="tab === 'ulp' ? 'bg-white shadow text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-1.5 rounded-md transition-all">ULP</button>
                                                </div>

                                                <!-- UP3 Tab -->
                                                <div x-show="tab === 'up3'" class="space-y-4">
                                                    <div class="grid grid-cols-2 gap-3 mb-4">
                                                        <div class="bg-green-50 border border-green-100 rounded-md p-3 text-center">
                                                            <div class="text-2xl font-black text-green-600">{{ count($lmMenangKalah[$lm->id]['up3']['menang'] ?? []) }}</div>
                                                            <div class="text-[10px] uppercase font-bold text-green-800">Menang</div>
                                                        </div>
                                                        <div class="bg-red-50 border border-red-100 rounded-md p-3 text-center">
                                                            <div class="text-2xl font-black text-red-600">{{ count($lmMenangKalah[$lm->id]['up3']['kalah'] ?? []) }}</div>
                                                            <div class="text-[10px] uppercase font-bold text-red-800">Kalah</div>
                                                        </div>
                                                    </div>
                                                    <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                                        @foreach($lmMenangKalah[$lm->id]['up3']['menang'] ?? [] as $item)
                                                            <div class="flex justify-between items-center bg-green-50/50 border border-green-100 px-3 py-2 rounded-md">
                                                                <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                                                <span class="text-xs font-black text-green-600">{{ $item['score'] }}%</span>
                                                            </div>
                                                        @endforeach
                                                        @foreach($lmMenangKalah[$lm->id]['up3']['kalah'] ?? [] as $item)
                                                            <div class="flex justify-between items-center bg-red-50/50 border border-red-100 px-3 py-2 rounded-md">
                                                                <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                                                <span class="text-xs font-black text-red-600">{{ $item['score'] }}%</span>
                                                            </div>
                                                        @endforeach
                                                        @if(empty($lmMenangKalah[$lm->id]['up3']['menang']) && empty($lmMenangKalah[$lm->id]['up3']['kalah']))
                                                            <div class="text-center text-xs text-gray-400 italic">Belum ada data.</div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- ULP Tab -->
                                                <div x-show="tab === 'ulp'" class="space-y-4" style="display: none;">
                                                    <div class="grid grid-cols-2 gap-3 mb-4">
                                                        <div class="bg-green-50 border border-green-100 rounded-md p-3 text-center">
                                                            <div class="text-2xl font-black text-green-600">{{ count($lmMenangKalah[$lm->id]['ulp']['menang'] ?? []) }}</div>
                                                            <div class="text-[10px] uppercase font-bold text-green-800">Menang</div>
                                                        </div>
                                                        <div class="bg-red-50 border border-red-100 rounded-md p-3 text-center">
                                                            <div class="text-2xl font-black text-red-600">{{ count($lmMenangKalah[$lm->id]['ulp']['kalah'] ?? []) }}</div>
                                                            <div class="text-[10px] uppercase font-bold text-red-800">Kalah</div>
                                                        </div>
                                                    </div>
                                                    <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                                        @foreach($lmMenangKalah[$lm->id]['ulp']['menang'] ?? [] as $item)
                                                            <div class="flex justify-between items-center bg-green-50/50 border border-green-100 px-3 py-2 rounded-md">
                                                                <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                                                <span class="text-xs font-black text-green-600">{{ $item['score'] }}%</span>
                                                            </div>
                                                        @endforeach
                                                        @foreach($lmMenangKalah[$lm->id]['ulp']['kalah'] ?? [] as $item)
                                                            <div class="flex justify-between items-center bg-red-50/50 border border-red-100 px-3 py-2 rounded-md">
                                                                <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                                                <span class="text-xs font-black text-red-600">{{ $item['score'] }}%</span>
                                                            </div>
                                                        @endforeach
                                                        @if(empty($lmMenangKalah[$lm->id]['ulp']['menang']) && empty($lmMenangKalah[$lm->id]['ulp']['kalah']))
                                                            <div class="text-center text-xs text-gray-400 italic">Belum ada data.</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div> <!-- End of col-span-1 -->
                                    </div> <!-- End of Grid untuk LM -->
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

                </div>
            </div> <!-- End of grid wrapper -->
        </div>
    </div>

    <!-- TinyMCE Editor & html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.komitmen-input');
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    const lm_id = this.getAttribute('data-lm');
                    const unit_id = this.getAttribute('data-unit');
                    const sesi_id = this.getAttribute('data-sesi');
                    const type = this.getAttribute('data-type');
                    const val = this.value;
                    
                    const otherType = type === 'komitmen' ? 'carry_over' : 'komitmen';
                    const otherInput = document.querySelector(`.komitmen-input[data-lm="${lm_id}"][data-unit="${unit_id}"][data-sesi="${sesi_id}"][data-type="${otherType}"]`);
                    
                    const payload = {
                        _token: '{{ csrf_token() }}',
                        lm_id: lm_id,
                        unit_id: unit_id
                    };
                    payload[type] = val;
                    if (otherInput) {
                        payload[otherType] = otherInput.value;
                    }

                    this.classList.add('opacity-50');
                    
                    fetch(`/sesi-wigs/${sesi_id}/komitmen`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload)
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.classList.remove('opacity-50');
                        if (!data.success) {
                            alert('Gagal menyimpan: ' + (data.message || 'Error tidak diketahui'));
                        }
                    })
                    .catch(err => {
                        this.classList.remove('opacity-50');
                        alert('Terjadi kesalahan jaringan.');
                    });
                });
            });
        });

        function toggleUlps(id) {
            const rows = document.querySelectorAll('.ulp-row-' + id);
            const icon = document.getElementById('icon-' + id);
            let isHidden = false;
            rows.forEach(r => {
                if (r.classList.contains('hidden')) {
                    r.classList.remove('hidden');
                    isHidden = true;
                } else {
                    r.classList.add('hidden');
                }
            });
            if (icon) {
                if (isHidden) {
                    icon.classList.add('rotate-180');
                } else {
                    icon.classList.remove('rotate-180');
                }
            }
        }
        
        // Menghapus fungsi toggleWig karena sudah menggunakan Alpine.js x-data
        


        document.addEventListener('DOMContentLoaded', function() {
            tinymce.init({
                selector: '#notulensi-editor',
                height: 500,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | ' +
                'bold italic textcolor | alignleft aligncenter ' +
                'alignright alignjustify | table | bullist numlist outdent indent | ' +
                'removeformat | help',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px } table { border-collapse: collapse; width: 100%; } table, th, td { border: 1px solid #cbd5e1; padding: 8px; } th { background-color: #f1f5f9; }',
                setup: function (editor) {
                    editor.ui.registry.addAutocompleter('mentions', {
                        ch: '@',
                        minChars: 0,
                        columns: 1,
                        fetch: function (pattern) {
                            var units = [
                                @foreach($units as $u)
                                    { value: '{{ $u->name }}', text: '{{ $u->name }} ({{ $u->type }})' },
                                @endforeach
                            ];
                            var matched = units.filter(function (unit) {
                                return unit.text.toLowerCase().indexOf(pattern.toLowerCase()) !== -1;
                            });
                            
                            return new tinymce.util.Promise(function (resolve) {
                                var results = matched.map(function (unit) {
                                    return {
                                        type: 'autocompleteitem',
                                        value: unit.value,
                                        text: unit.text,
                                    };
                                });
                                resolve(results);
                            });
                        },
                        onAction: function (autocompleteApi, rng, value) {
                            editor.selection.setRng(rng);
                            editor.insertContent('<strong style="color: #4f46e5;">@' + value + '</strong> ');
                            autocompleteApi.hide();
                        }
                    });
                }
            });
        });

        function insertTemplate(type) {
            var editor = tinymce.activeEditor;
            
            const standardTemplate = `
                <div style="font-family: inherit;">
                    <h2 style="text-align: center; color: #1e293b; margin-bottom: 20px;">NOTULENSI & BERITA ACARA SESI WIG</h2>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;" border="1">
                        <tbody>
                            <tr>
                                <td style="width: 25%; padding: 8px; background-color: #f8fafc; font-weight: bold;">Hari/Tanggal</td>
                                <td style="width: 75%; padding: 8px;"></td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; background-color: #f8fafc; font-weight: bold;">Pimpinan Rapat</td>
                                <td style="padding: 8px;"></td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; background-color: #f8fafc; font-weight: bold;">Peserta</td>
                                <td style="padding: 8px;"></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h3 style="color: #334155; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px;">1. Pembahasan Pencapaian WIG</h3>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;" border="1">
                        <thead>
                            <tr style="background-color: #f1f5f9;">
                                <th style="padding: 8px; text-align: left; width: 5%;">No</th>
                                <th style="padding: 8px; text-align: left; width: 45%;">Agenda / Topik Pembahasan</th>
                                <th style="padding: 8px; text-align: left; width: 50%;">Hasil Pembahasan & Evaluasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 8px;">1</td>
                                <td style="padding: 8px;">Review Komitmen Sebelumnya</td>
                                <td style="padding: 8px;"></td>
                            </tr>
                            <tr>
                                <td style="padding: 8px;">2</td>
                                <td style="padding: 8px;">Capaian Lead Measure</td>
                                <td style="padding: 8px;"></td>
                            </tr>
                        </tbody>
                    </table>

                    <h3 style="color: #334155; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px;">2. Komitmen & Tindak Lanjut</h3>
                    <table style="width: 100%; border-collapse: collapse;" border="1">
                        <thead>
                            <tr style="background-color: #f1f5f9;">
                                <th style="padding: 8px; text-align: left; width: 5%;">No</th>
                                <th style="padding: 8px; text-align: left; width: 65%;">Detail Komitmen / Tindak Lanjut</th>
                                <th style="padding: 8px; text-align: left; width: 30%;">PIC Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 8px;">1</td>
                                <td style="padding: 8px;"></td>
                                <td style="padding: 8px;"></td>
                            </tr>
                            <tr>
                                <td style="padding: 8px;">2</td>
                                <td style="padding: 8px;"></td>
                                <td style="padding: 8px;"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;

            const evaluasiTemplate = `
                <div style="font-family: inherit;">
                    <h2 style="text-align: center; color: #1e293b; margin-bottom: 20px;">FORMULIR KENDALA & TINDAK LANJUT WIG</h2>
                    
                    <h3 style="color: #334155; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px;">A. Daftar Kendala / Hambatan Utama</h3>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;" border="1">
                        <thead>
                            <tr style="background-color: #fee2e2;">
                                <th style="padding: 8px; text-align: left; width: 5%;">No</th>
                                <th style="padding: 8px; text-align: left; width: 45%;">Deskripsi Kendala / Hambatan</th>
                                <th style="padding: 8px; text-align: left; width: 50%;">Akar Masalah (Root Cause)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 8px;">1</td>
                                <td style="padding: 8px;"></td>
                                <td style="padding: 8px;"></td>
                            </tr>
                        </tbody>
                    </table>

                    <h3 style="color: #334155; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px;">B. Rencana Tindak Lanjut (Action Plan)</h3>
                    <table style="width: 100%; border-collapse: collapse;" border="1">
                        <thead>
                            <tr style="background-color: #e0e7ff;">
                                <th style="padding: 8px; text-align: left; width: 5%;">No</th>
                                <th style="padding: 8px; text-align: left; width: 45%;">Rencana Tindak Lanjut</th>
                                <th style="padding: 8px; text-align: left; width: 25%;">Target Selesai</th>
                                <th style="padding: 8px; text-align: left; width: 25%;">PIC Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 8px;">1</td>
                                <td style="padding: 8px;"></td>
                                <td style="padding: 8px;"></td>
                                <td style="padding: 8px;"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;

            if (type === 'standar') {
                editor.setContent(standardTemplate);
            } else if (type === 'evaluasi') {
                editor.setContent(evaluasiTemplate);
            } else if (type === 'kosong') {
                editor.setContent("");
            }
            
            document.getElementById('template-selector').value = '';
        }

        function exportToPDF() {
            var content = tinymce.activeEditor.getContent();
            if (!content || content.trim() === '') {
                alert("Editor masih kosong, tidak ada yang bisa diekspor.");
                return;
            }
            
            var element = document.createElement('div');
            element.innerHTML = content;
            element.style.padding = '30px';
            element.style.fontFamily = 'Helvetica, Arial, sans-serif';
            element.style.fontSize = '14px';
            element.style.color = '#333';
            
            // Fix table borders for PDF
            var tables = element.querySelectorAll('table');
            tables.forEach(function(table) {
                table.style.borderCollapse = 'collapse';
                table.style.width = '100%';
                table.style.marginBottom = '20px';
                table.setAttribute('border', '1');
                table.style.borderColor = '#cbd5e1';
                var cells = table.querySelectorAll('th, td');
                cells.forEach(function(cell) {
                    cell.style.padding = '8px';
                    cell.style.border = '1px solid #cbd5e1';
                });
            });
            
            var opt = {
              margin:       [0.5, 0.5, 0.5, 0.5],
              filename:     'Notulensi-WIG-{{ \Carbon\Carbon::parse($sesi_wig->tanggal_pelaksanaan)->format("d-m-Y") }}.pdf',
              image:        { type: 'jpeg', quality: 0.98 },
              html2canvas:  { scale: 2, useCORS: true },
              jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
            };
            
            html2pdf().set(opt).from(element).save();
        }
    </script>

    <!-- Modal Form Komitmen LM -->
    <div x-data="komitmenModal()" @open-komitmen.window="openModal($event.detail)" x-show="isOpen" style="display: none; z-index: 9999;" class="fixed inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="isOpen" x-transition class="relative z-10 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle w-full max-w-4xl border border-slate-100 flex flex-col">
                <form @submit.prevent="saveKomitmen" class="flex flex-col">
                    <div class="bg-gradient-to-r from-blue-50 to-white px-4 sm:px-6 py-4 border-b border-gray-100 flex justify-between items-start sm:items-center">
                        <h3 class="text-lg font-extrabold text-gray-900 flex items-start gap-3" id="modal-title">
                            <svg class="w-5 h-5 text-blue-600 shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>FORM KOMITMEN LEAD MEASURE (LM)</span>
                        </h3>
                        <button @click="closeModal()" type="button" class="text-gray-400 hover:text-gray-600 transition bg-white hover:bg-gray-100 rounded-full p-1 focus:outline-none shrink-0 ml-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div class="p-4 sm:p-6 bg-white overflow-y-auto max-h-[75vh]">
                        <!-- Top Info -->
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 mb-6 text-sm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-4">
                                <div class="flex items-start"><span class="font-semibold text-slate-500 w-[110px] sm:w-32 shrink-0">Unit</span> <span class="font-bold text-slate-800">: <span x-text="params.unitName"></span></span></div>
                                <div class="flex items-start"><span class="font-semibold text-slate-500 w-[110px] sm:w-32 shrink-0">Tanggal</span> <span class="font-bold text-slate-800">: <span x-text="params.date"></span></span></div>
                                <div class="md:col-span-2 flex items-start"><span class="font-semibold text-slate-500 w-[110px] sm:w-32 shrink-0">WIG</span> <span class="font-bold text-slate-800 flex-1">: <span x-text="params.wigName"></span></span></div>
                                <div class="md:col-span-2 flex items-start"><span class="font-semibold text-slate-500 w-[110px] sm:w-32 shrink-0">Lead Measure</span> <span class="font-bold text-indigo-700 flex-1">: <span x-text="params.lmName"></span></span></div>
                                <div class="md:col-span-2 flex sm:items-center items-start gap-2">
                                    <span class="font-semibold text-slate-500 w-[110px] sm:w-32 shrink-0 mt-1 sm:mt-0">PIC LM</span> 
                                    <span class="mt-1 sm:mt-0">:</span>
                                    <input type="text" x-model="form.pic_lm" class="w-full md:w-1/2 p-1.5 border border-slate-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white" placeholder="Nama PIC LM">
                                </div>
                                <div class="md:col-span-2 flex sm:items-center items-start gap-2 mt-1">
                                    <span class="font-semibold text-slate-500 w-[110px] sm:w-32 shrink-0 mt-1 sm:mt-0">Angka Komitmen</span> 
                                    <span class="mt-1 sm:mt-0">:</span>
                                    <input type="number" step="any" x-model="form.komitmen" class="w-32 p-1.5 border border-slate-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white" placeholder="Target Angka">
                                </div>
                            </div>
                        </div>

                        <!-- 1. Target & Realisasi -->
                        <h4 class="font-bold text-slate-800 mb-3 flex items-center gap-2 uppercase text-sm">
                            <span class="bg-indigo-100 text-indigo-700 w-6 h-6 rounded-full inline-flex items-center justify-center text-xs">1</span> 
                            Target & Realisasi
                        </h4>
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-x-auto mb-6">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-100 border-b border-slate-200 text-slate-700">
                                    <tr>
                                        <th class="py-2 px-4 text-left font-semibold whitespace-nowrap">Target LM</th>
                                        <th class="py-2 px-4 text-left font-semibold whitespace-nowrap">Realisasi Minggu Ini</th>
                                        <th class="py-2 px-4 text-left font-semibold whitespace-nowrap">Gap</th>
                                        <th class="py-2 px-4 text-left font-semibold whitespace-nowrap">% Pencapaian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="py-2 px-4 font-bold text-indigo-900 whitespace-nowrap" x-text="formatNumber(params.target)"></td>
                                        <td class="py-2 px-4 font-bold text-slate-800 whitespace-nowrap" x-text="formatNumber(params.realisasi)"></td>
                                        <td class="py-2 px-4 font-bold whitespace-nowrap" :class="(params.realisasi - params.target) >= 0 ? 'text-green-600' : 'text-red-600'">
                                            <span x-show="(params.realisasi - params.target) > 0">+</span><span x-text="formatNumber(params.realisasi - params.target)"></span>
                                        </td>
                                        <td class="py-2 px-4 whitespace-nowrap">
                                            <span class="px-2 py-1 rounded text-xs font-bold" 
                                                :class="params.capai >= 100 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                                x-text="params.capai + '%'"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 2. Hambatan -->
                        <h4 class="font-bold text-slate-800 mb-2 flex items-center gap-2 uppercase text-sm">
                            <span class="bg-indigo-100 text-indigo-700 w-6 h-6 rounded-full inline-flex items-center justify-center text-xs">2</span> 
                            Hambatan (Obstacles)
                        </h4>
                        <p class="text-xs text-slate-500 mb-3 italic">Apa yang menjadi kendala/hambatan untuk mencapai target LM minggu ini?</p>
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden mb-6">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-100 border-b border-slate-200 text-slate-700">
                                    <tr>
                                        <th class="py-2 px-4 text-left font-semibold w-1/2 border-r border-slate-200">Hambatan</th>
                                        <th class="py-2 px-4 text-left font-semibold w-1/2">Dukungan yang Dibutuhkan</th>
                                        <th class="py-2 px-2 text-center w-12"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="(h, index) in form.hambatans" :key="index">
                                        <tr>
                                            <td class="py-2 px-2 border-r border-slate-200">
                                                <textarea x-model="h.hambatan" class="w-full p-2 border-0 bg-transparent resize-none focus:ring-0 text-sm h-12" placeholder="Tulis hambatan..."></textarea>
                                            </td>
                                            <td class="py-2 px-2">
                                                <textarea x-model="h.dukungan" class="w-full p-2 border-0 bg-transparent resize-none focus:ring-0 text-sm h-12" placeholder="Tulis dukungan yang dibutuhkan..."></textarea>
                                            </td>
                                            <td class="py-2 px-2 text-center text-red-400 hover:text-red-600">
                                                <button type="button" @click="form.hambatans.splice(index, 1)" tabindex="-1" title="Hapus Baris">
                                                    <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div class="bg-slate-50 border-t border-slate-200 p-2 text-center">
                                <button type="button" @click="form.hambatans.push({hambatan: '', dukungan: ''})" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center justify-center gap-1 mx-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Tambah Hambatan
                                </button>
                            </div>
                        </div>

                        <!-- 3. Aksi Konkrit -->
                        <h4 class="font-bold text-slate-800 mb-2 flex items-center gap-2 uppercase text-sm">
                            <span class="bg-indigo-100 text-indigo-700 w-6 h-6 rounded-full inline-flex items-center justify-center text-xs">3</span> 
                            Aksi Konkrit Untuk Minggu Depan
                        </h4>
                        <p class="text-xs text-slate-500 mb-3 italic">Komitmen spesifik, terukur, dan terbatas waktu untuk mencapai target LM.</p>
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden mb-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-slate-100 border-b border-slate-200 text-slate-700">
                                        <tr>
                                            <th class="py-2 px-3 text-center font-semibold w-10 border-r border-slate-200">No</th>
                                            <th class="py-2 px-3 text-left font-semibold border-r border-slate-200">Aksi</th>
                                            <th class="py-2 px-3 text-left font-semibold w-24 border-r border-slate-200">Target</th>
                                            <th class="py-2 px-3 text-left font-semibold w-32 border-r border-slate-200">Deadline</th>
                                            <th class="py-2 px-3 text-left font-semibold">Komitmen (Detil)</th>
                                            <th class="py-2 px-2 text-center w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="(a, index) in form.aksi_konkrits" :key="index">
                                            <tr>
                                                <td class="py-2 px-2 text-center font-bold text-slate-400 border-r border-slate-200" x-text="index + 1"></td>
                                                <td class="py-1 px-2 border-r border-slate-200">
                                                    <textarea x-model="a.aksi" :disabled="params.readonly" class="w-full p-1 border-0 bg-transparent resize-none focus:ring-0 text-sm h-10" placeholder="..."></textarea>
                                                </td>
                                                <td class="py-1 px-2 border-r border-slate-200">
                                                    <input type="text" x-model="a.target" :disabled="params.readonly" class="w-full p-1 border-0 bg-transparent focus:ring-0 text-sm" placeholder="...">
                                                </td>
                                                <td class="py-1 px-2 border-r border-slate-200">
                                                    <input type="date" x-model="a.deadline" :disabled="params.readonly" class="w-full p-1 border-0 bg-transparent focus:ring-0 text-xs text-slate-700">
                                                </td>
                                                <td class="py-1 px-2">
                                                    <textarea x-model="a.detail_komitmen" :disabled="params.readonly" class="w-full p-1 border-0 bg-transparent resize-none focus:ring-0 text-sm h-10" placeholder="..."></textarea>
                                                </td>
                                                <td class="py-2 px-2 text-center text-red-400 hover:text-red-600">
                                                    <button type="button" x-show="!params.readonly" @click="form.aksi_konkrits.splice(index, 1)" tabindex="-1" title="Hapus Baris">
                                                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div class="bg-slate-50 border-t border-slate-200 p-2 text-center">
                                <button type="button" x-show="!params.readonly" @click="form.aksi_konkrits.push({aksi: '', target: '', deadline: '', detail_komitmen: ''})" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center justify-center gap-1 mx-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Tambah Aksi Konkrit
                                </button>
                            </div>
                        </div>
                        
                        <div class="bg-blue-50 text-blue-800 p-3 rounded-lg text-xs border border-blue-200 shadow-sm">
                            <span class="font-bold">Catatan:</span> Form ini diisi untuk setiap LM yang belum mencapai 100% realisasi. Simpan sebagai tracking & accountability untuk mencapai target minggu depan.
                        </div>
                    </div>

                    <div class="bg-white border-t border-slate-200 px-6 py-4 rounded-b-xl flex justify-end gap-3 shrink-0">
                        <button type="button" @click="closeModal()" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition-colors">Batal</button>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md transition-all flex items-center gap-2" :class="isSaving ? 'opacity-75 cursor-wait' : ''" :disabled="isSaving">
                            <svg x-show="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Komitmen'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function komitmenModal() {
            return {
                isOpen: false,
                isSaving: false,
                params: {
                    sesi: null, lm: null, unit: null, target: 0, realisasi: 0, capai: 0, unitName: '', lmName: '', wigName: '', date: ''
                },
                form: {
                    pic_lm: '',
                    komitmen: '',
                    hambatans: [{hambatan: '', dukungan: ''}],
                    aksi_konkrits: [{aksi: '', target: '', deadline: '', detail_komitmen: ''}]
                },
                formatNumber(num) {
                    return Number(num).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                },
                openModal(detail) {
                    this.params = detail;
                    this.form.pic_lm = '';
                    this.form.komitmen = '';
                    this.form.hambatans = [];
                    this.form.aksi_konkrits = [];
                    
                    // Fetch existing data
                    fetch(`/sesi-wigs/${this.params.sesi}/komitmen/${this.params.lm}/${this.params.unit}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.data) {
                                this.form.pic_lm = data.data.pic_lm || '';
                                this.form.komitmen = data.data.komitmen || '';
                                this.form.hambatans = data.data.hambatans || [{hambatan: '', dukungan: ''}];
                                this.form.aksi_konkrits = data.data.aksi_konkrits || [{aksi: '', target: '', deadline: '', detail_komitmen: ''}];
                            } else {
                                this.form.komitmen = '';
                                this.form.hambatans = [{hambatan: '', dukungan: ''}];
                                this.form.aksi_konkrits = [{aksi: '', target: '', deadline: '', detail_komitmen: ''}];
                            }
                            this.isOpen = true;
                        });
                },
                closeModal() {
                    this.isOpen = false;
                },
                saveKomitmen() {
                    this.isSaving = true;
                    
                    fetch(`/sesi-wigs/${this.params.sesi}/komitmen/${this.params.lm}/${this.params.unit}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.isSaving = false;
                        if(data.status === 'success') {
                            this.closeModal();
                            // Optional: show toast or just reload to update the matrix colors
                            window.location.reload();
                        } else {
                            alert('Gagal menyimpan komitmen.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        this.isSaving = false;
                        alert('Terjadi kesalahan jaringan.');
                    });
                }
            }
        }
    </script>
</x-app-layout>

