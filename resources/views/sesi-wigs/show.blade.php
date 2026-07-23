<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 mb-6" x-data="presenterDraw()">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-4">
                        <h4 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                            Presenter Sesi Ini
                        </h4>
                        <button @click="openDrawModal()" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-all flex items-center gap-2 mt-4 md:mt-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Pilih / Undi Presenter
                        </button>
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
                            Belum ada presenter yang dipilih untuk sesi ini. Klik tombol undi untuk memilih secara acak.
                        </div>
                    @endif
                </div>

                <!-- Modal Undian -->
                <template x-teleport="body">
                    <div x-show="showDrawModal" style="display: none; background-color: rgba(17, 24, 39, 0.7); backdrop-filter: blur(8px);" class="fixed inset-0 z-[110] flex items-center justify-center p-4 sm:p-6" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div @click.away="!isDrawing ? showDrawModal = false : null" class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col">
                            
                            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                                    Undian Presenter WIG
                                </h3>
                                <button type="button" @click="showDrawModal = false" x-show="!isDrawing" class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <div class="p-8 flex flex-col items-center">
                                
                                <!-- Mode Pilihan -->
                                <div x-show="!isDrawing && !winnerResult" class="w-full">
                                    <p class="text-center text-gray-600 mb-6">Pilih level unit yang akan diundi. Unit yang sudah presentasi di bulan ini tidak akan diikutkan kembali.</p>
                                    <div class="flex justify-center gap-4">
                                        <button @click="startDraw('ULP')" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md transition-all text-lg">
                                            Undi ULP
                                        </button>
                                        <button @click="startDraw('UP3')" class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-lg shadow-md transition-all text-lg">
                                            Undi UP3
                                        </button>
                                    </div>
                                    <div x-show="errorMessage" x-text="errorMessage" class="mt-6 p-4 bg-red-100 text-red-700 rounded-lg text-center font-medium"></div>
                                </div>

                                <!-- Slot Machine Animation -->
                                <div x-show="isDrawing" class="w-full flex flex-col items-center py-10">
                                    <div class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Mengacak Unit...</div>
                                    <div class="bg-gray-100 border-2 border-gray-300 w-full rounded-xl py-8 px-4 text-center shadow-inner overflow-hidden relative">
                                        <div class="text-3xl md:text-5xl font-black text-gray-800 tracking-tight" x-text="currentTickName"></div>
                                    </div>
                                </div>

                                <!-- Result -->
                                <div x-show="winnerResult" class="w-full flex flex-col items-center py-6">
                                    <div class="mb-4">
                                        <svg class="w-16 h-16 text-green-500 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-1">Terpilih sebagai Presenter:</div>
                                    <div class="text-3xl md:text-5xl font-black text-amber-600 text-center mb-6" x-text="winnerResult"></div>
                                    
                                    <button @click="window.location.reload()" class="px-8 py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg shadow-md transition-all text-lg">
                                        Selesai & Tutup
                                    </button>
                                </div>
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
                            Lihat Evaluasi & Komitmen Sebelumnya
                        </button>

                        <!-- Button to write new notes -->
                        <button @click="showModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-md transition-all flex items-center gap-2 w-full sm:w-auto justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            Tulis Notulensi Sesi Ini
                        </button>
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

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <div class="lg:col-span-3 space-y-6">
                    <!-- WIG Cards -->
            <div class="mb-6">
                <div class="flex flex-col md:flex-row justify-between items-center mb-4 px-2">
                    <h3 class="text-xl font-bold text-gray-800">Capaian WIG s.d Tanggal {{ \Carbon\Carbon::parse($sesi_wig->tanggal_pelaksanaan)->format('d/m/Y') }}</h3>
                    <form method="GET" action="{{ route('sesi-wigs.show', $sesi_wig->id) }}" class="flex items-center gap-2 mt-2 md:mt-0">
                        <input type="hidden" name="lm_unit" value="{{ $lm_unit ?? '' }}">
                        <label class="text-sm font-semibold text-gray-600">Filter Bulan WIG:</label>
                        <select name="wig_bulan" onchange="this.form.submit()" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5">
                            <option value="">-- Hingga Tgl Sesi --</option>
                            @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $b)
                                <option value="{{ $index + 1 }}" {{ request('wig_bulan') == ($index + 1) ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @forelse($wigs as $wig)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full transform transition duration-300 hover:shadow-md hover:-translate-y-1">
                        <div class="p-6 border-b border-gray-50 flex-grow">
                            <div class="flex items-start gap-3 mb-4">
                                <div class="bg-indigo-100 text-indigo-700 p-2 rounded-lg mt-1 shrink-0">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="font-bold text-lg text-gray-800 leading-tight">{{ $wig->judul }}</h4>
                                    @if($wig->deskripsi)
                                        <p class="text-sm text-gray-500 mt-1">{{ $wig->deskripsi }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-end mb-2">
                                <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Capaian Keseluruhan</div>
                                <div class="text-2xl font-black {{ $wig->capaian >= 100 ? 'text-emerald-600' : 'text-blue-600' }}">{{ $wig->capaian }}%</div>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-3 mb-4 overflow-hidden">
                                <div class="h-3 rounded-full {{ $wig->capaian >= 100 ? 'bg-emerald-500' : 'bg-gradient-to-r from-blue-500 to-indigo-500' }} transition-all duration-1000 ease-out" style="width: {{ $wig->capaian }}%"></div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 mt-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Target Kumulatif</p>
                                    <p class="font-bold text-slate-800 text-lg">{{ number_format($wig->total_target, 2) }} {{ $wig->satuan->name ?? '' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">Realisasi</p>
                                    <p class="font-bold text-slate-800 text-lg">{{ number_format($wig->total_realisasi, 2) }} {{ $wig->satuan->name ?? '' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-2 text-center py-8 text-gray-500">Belum ada WIG yang dibuat.</div>
                    @endforelse
                </div>
            </div>

            <!-- LM Table -->
            <div class="mb-6">
                <div class="flex flex-col md:flex-row justify-between items-center mb-4 px-2">
                    <h3 class="text-xl font-bold text-gray-800">Capaian Lead Measure s.d Tanggal {{ \Carbon\Carbon::parse($sesi_wig->tanggal_pelaksanaan)->format('d/m/Y') }}</h3>
                    <form method="GET" action="{{ route('sesi-wigs.show', $sesi_wig->id) }}" class="flex items-center gap-2 mt-2 md:mt-0">
                        <input type="hidden" name="wig_bulan" value="{{ $wig_bulan ?? '' }}">
                        <label class="text-sm font-semibold text-gray-600">Filter Unit:</label>
                        <select name="lm_unit" onchange="this.form.submit()" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-1.5">
                            <option value="">-- Semua Unit --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ request('lm_unit') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->type }})</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Judul LM</th>
                                    <th class="px-6 py-4 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Target Kumulatif</th>
                                    <th class="px-6 py-4 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Realisasi</th>
                                    <th class="px-6 py-4 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Capaian (%)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($lms as $lm)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $lm->judul_lm }}</div>
                                        <div class="text-xs text-gray-500 mt-1">WIG: {{ $lm->wig->judul ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-700">
                                        {{ number_format($lm->total_target, 2) }} {{ $lm->satuan->name ?? '' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-indigo-600">
                                        {{ number_format($lm->total_realisasi, 2) }} {{ $lm->satuan->name ?? '' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold {{ $lm->capaian >= 100 ? 'text-emerald-600' : 'text-blue-600' }}">{{ $lm->capaian }}%</span>
                                            <div class="w-16 bg-gray-200 rounded-full h-1.5 overflow-hidden hidden sm:block">
                                                <div class="h-1.5 rounded-full {{ $lm->capaian >= 100 ? 'bg-emerald-500' : 'bg-blue-500' }}" style="width: {{ $lm->capaian }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada Lead Measure.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            </div> <!-- End of lg:col-span-3 -->

            <!-- Side Widgets -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Top Performa ULP Widget -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                        Top Performa ULP
                    </h3>
                    <div class="space-y-4">
                        @foreach($leaderboard as $index => $rank)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center border {{ $index < 3 ? 'border-yellow-400 text-yellow-600 font-bold' : 'border-gray-200 text-gray-500 text-sm' }}">
                                        {{ $index + 1 }}
                                    </div>
                                    <p class="text-sm font-semibold text-gray-800">{{ $rank['unit'] }}</p>
                                </div>
                                <div class="text-sm font-bold {{ $rank['score'] > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ number_format($rank['score'], 1) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Menang / Kalah Widget -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6" x-data="{ tab: 'up3' }">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        Status Menang / Kalah
                    </h3>
                    
                    <!-- Tabs -->
                    <div class="flex space-x-1 bg-gray-100/50 p-1 rounded-lg mb-4 text-xs font-semibold">
                        <button @click="tab = 'divisi'" :class="tab === 'divisi' ? 'bg-white shadow text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-1.5 rounded-md transition-all">Divisi</button>
                        <button @click="tab = 'up3'" :class="tab === 'up3' ? 'bg-white shadow text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-1.5 rounded-md transition-all">UP3</button>
                        <button @click="tab = 'ulp'" :class="tab === 'ulp' ? 'bg-white shadow text-indigo-700' : 'text-gray-500 hover:text-gray-700'" class="flex-1 py-1.5 rounded-md transition-all">ULP</button>
                    </div>
                    
                    <!-- Divisi Tab -->
                    <div x-show="tab === 'divisi'" class="space-y-4">
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-green-50 border border-green-100 rounded-md p-3 text-center">
                                <div class="text-2xl font-black text-green-600">{{ count($menangKalah['divisi']['menang'] ?? []) }}</div>
                                <div class="text-[10px] uppercase font-bold text-green-800">Menang</div>
                            </div>
                            <div class="bg-red-50 border border-red-100 rounded-md p-3 text-center">
                                <div class="text-2xl font-black text-red-600">{{ count($menangKalah['divisi']['kalah'] ?? []) }}</div>
                                <div class="text-[10px] uppercase font-bold text-red-800">Kalah</div>
                            </div>
                        </div>
                        <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2">
                            @foreach($menangKalah['divisi']['menang'] ?? [] as $item)
                                <div class="flex justify-between items-center bg-green-50/50 border border-green-100 px-3 py-2 rounded-md">
                                    <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                    <span class="text-xs font-black text-green-600">{{ $item['score'] }}%</span>
                                </div>
                            @endforeach
                            @foreach($menangKalah['divisi']['kalah'] ?? [] as $item)
                                <div class="flex justify-between items-center bg-red-50/50 border border-red-100 px-3 py-2 rounded-md">
                                    <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                    <span class="text-xs font-black text-red-600">{{ $item['score'] }}%</span>
                                </div>
                            @endforeach
                            @if(empty($menangKalah['divisi']['menang']) && empty($menangKalah['divisi']['kalah']))
                                <div class="text-center text-xs text-gray-400 italic">Belum ada data.</div>
                            @endif
                        </div>
                    </div>

                    <!-- UP3 Tab -->
                    <div x-show="tab === 'up3'" class="space-y-4" style="display: none;">
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-green-50 border border-green-100 rounded-md p-3 text-center">
                                <div class="text-2xl font-black text-green-600">{{ count($menangKalah['up3']['menang'] ?? []) }}</div>
                                <div class="text-[10px] uppercase font-bold text-green-800">Menang</div>
                            </div>
                            <div class="bg-red-50 border border-red-100 rounded-md p-3 text-center">
                                <div class="text-2xl font-black text-red-600">{{ count($menangKalah['up3']['kalah'] ?? []) }}</div>
                                <div class="text-[10px] uppercase font-bold text-red-800">Kalah</div>
                            </div>
                        </div>
                        <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2">
                            @foreach($menangKalah['up3']['menang'] ?? [] as $item)
                                <div class="flex justify-between items-center bg-green-50/50 border border-green-100 px-3 py-2 rounded-md">
                                    <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                    <span class="text-xs font-black text-green-600">{{ $item['score'] }}%</span>
                                </div>
                            @endforeach
                            @foreach($menangKalah['up3']['kalah'] ?? [] as $item)
                                <div class="flex justify-between items-center bg-red-50/50 border border-red-100 px-3 py-2 rounded-md">
                                    <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                    <span class="text-xs font-black text-red-600">{{ $item['score'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- ULP Tab -->
                    <div x-show="tab === 'ulp'" class="space-y-4" style="display: none;">
                        <div class="grid grid-cols-2 gap-3 mb-4">
                            <div class="bg-green-50 border border-green-100 rounded-md p-3 text-center">
                                <div class="text-2xl font-black text-green-600">{{ count($menangKalah['ulp']['menang'] ?? []) }}</div>
                                <div class="text-[10px] uppercase font-bold text-green-800">Menang</div>
                            </div>
                            <div class="bg-red-50 border border-red-100 rounded-md p-3 text-center">
                                <div class="text-2xl font-black text-red-600">{{ count($menangKalah['ulp']['kalah'] ?? []) }}</div>
                                <div class="text-[10px] uppercase font-bold text-red-800">Kalah</div>
                            </div>
                        </div>
                        <div class="space-y-3 max-h-[300px] overflow-y-auto pr-2">
                            @foreach($menangKalah['ulp']['menang'] ?? [] as $item)
                                <div class="flex justify-between items-center bg-green-50/50 border border-green-100 px-3 py-2 rounded-md">
                                    <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                    <span class="text-xs font-black text-green-600">{{ $item['score'] }}%</span>
                                </div>
                            @endforeach
                            @foreach($menangKalah['ulp']['kalah'] ?? [] as $item)
                                <div class="flex justify-between items-center bg-red-50/50 border border-red-100 px-3 py-2 rounded-md">
                                    <span class="text-xs font-bold text-gray-800">{{ $item['name'] }}</span>
                                    <span class="text-xs font-black text-red-600">{{ $item['score'] }}%</span>
                                </div>
                            @endforeach
                        </div>
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
        function presenterDraw() {
            return {
                showDrawModal: false,
                isDrawing: false,
                winnerResult: null,
                errorMessage: null,
                currentTickName: 'SIAP MENGACAK',
                candidates: [],
                
                openDrawModal() {
                    this.showDrawModal = true;
                    this.isDrawing = false;
                    this.winnerResult = null;
                    this.errorMessage = null;
                    this.currentTickName = 'SIAP MENGACAK';
                },
                
                startDraw(type) {
                    this.errorMessage = null;
                    
                    fetch(`{{ route('sesi-wigs.draw-presenter', $sesi_wig->id) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ type: type })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            this.errorMessage = data.message;
                            return;
                        }
                        
                        this.candidates = data.candidates;
                        if(this.candidates.length === 0) this.candidates = [data.winner.name]; // Fallback
                        
                        this.isDrawing = true;
                        
                        // Start roulette animation
                        let ticks = 0;
                        const maxTicks = 35; // Number of times it switches
                        let speed = 40; // Initial speed in ms
                        
                        const tick = () => {
                            ticks++;
                            // Show random candidate during animation
                            this.currentTickName = this.candidates[Math.floor(Math.random() * this.candidates.length)];
                            
                            if (ticks < maxTicks) {
                                // Gradually slow down
                                if (ticks > 25) speed += 30;
                                else if (ticks > 15) speed += 10;
                                setTimeout(tick, speed);
                            } else {
                                // Final result
                                this.isDrawing = false;
                                this.winnerResult = data.winner.name;
                            }
                        };
                        
                        setTimeout(tick, speed);
                    })
                    .catch(err => {
                        this.errorMessage = "Terjadi kesalahan sistem. Silakan coba lagi.";
                        console.error(err);
                    });
                }
            }
        }

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
</x-app-layout>
