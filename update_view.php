<?php
$file = 'resources/views/sesi-wigs/show.blade.php';
$content = file_get_contents($file);

// 1. Update the header: "Komitmen" -> "Form Komitmen"
$content = str_replace(
    '<th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50 w-24">Komitmen</th>',
    '<th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50 w-24">Form Komitmen</th>',
    $content
);

// 2. We'll leave "Carry Over" as it is, but we'll remove the input for ULP and just display '-'
$content = preg_replace(
    '/<td class="px-2 py-2 border border-gray-300 text-center">\s*@if\(\$canEdit\)\s*<input[^>]+komitmen-input[^>]+>\s*@else\s*\{\{\s*\$komitmenVal[^}]+\}\}\s*@endif\s*<\/td>/',
    '<td class="px-2 py-2 border border-gray-300 text-center">
        @php $hasKom = isset($matrixKomitmen[$lm->id][$u->id][$sw->id]); @endphp
        <button type="button" 
            @click="window.dispatchEvent(new CustomEvent(\'open-komitmen\', { detail: { sesi: {{ $sw->id }}, lm: {{ $lm->id }}, unit: {{ $u->id }}, target: {{ $target }}, realisasi: {{ $realisasi }}, capai: {{ $pencapaian }}, unitName: \'{{ addslashes($u->name) }}\', lmName: \'{{ addslashes($lm->judul_lm) }}\', date: \'{{ \Carbon\Carbon::parse($sw->tanggal_pelaksanaan)->format(\'d/m/Y\') }}\' } }))"
            class="inline-flex items-center justify-center w-7 h-7 rounded-full transition-all shadow-sm focus:outline-none {{ $hasKom ? \'bg-green-100 text-green-600 hover:bg-green-200 border border-green-200\' : \'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-200\' }}"
            title="{{ $hasKom ? \'Edit Form Komitmen\' : \'Isi Form Komitmen\' }}">
            @if($hasKom)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            @endif
        </button>
    </td>',
    $content
);

// 3. For Carry Over (ULP), just put "-"
$content = preg_replace(
    '/<td class="px-2 py-2 border border-gray-300 text-center">\s*@if\(\$canEdit\)\s*<input[^>]+carry_over"[^>]+>\s*@else\s*\{\{\s*\$carryOverVal[^}]+\}\}\s*@endif\s*<\/td>/',
    '<td class="px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50">-</td>',
    $content
);

// 4. Update UP3 Komitmen cell
$content = str_replace(
    '<td class="px-2 py-2 border border-gray-300 text-right font-semibold">{{ number_format($up3Target, 2) }}</td>
                                                        <td class="px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50">-</td>',
    '<td class="px-2 py-2 border border-gray-300 text-right font-semibold">{{ number_format($up3Target, 2) }}</td>
                                                        <td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">
                                                            @php $hasKom = isset($matrixKomitmen[$lm->id][$up3->id][$sw->id]); @endphp
                                                            <button type="button" 
                                                                @click="window.dispatchEvent(new CustomEvent(\'open-komitmen\', { detail: { sesi: {{ $sw->id }}, lm: {{ $lm->id }}, unit: {{ $up3->id }}, target: {{ $up3Target }}, realisasi: {{ $up3Realisasi }}, capai: {{ $up3Pencapaian }}, unitName: \'{{ addslashes($up3->name) }}\', lmName: \'{{ addslashes($lm->judul_lm) }}\', date: \'{{ \Carbon\Carbon::parse($sw->tanggal_pelaksanaan)->format(\'d/m/Y\') }}\' } }))"
                                                                class="inline-flex items-center justify-center w-7 h-7 rounded-full transition-all shadow-sm focus:outline-none {{ $hasKom ? \'bg-green-100 text-green-600 hover:bg-green-200 border border-green-200\' : \'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-200\' }}">
                                                                @if($hasKom)
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                @else
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                                @endif
                                                            </button>
                                                        </td>',
    $content
);

// We need to inject the AlpineJS Component at the bottom of the page before </x-app-layout>
$alpineComponent = <<<'EOD'

    <!-- Modal Form Komitmen LM -->
    <div x-data="komitmenModal()" @open-komitmen.window="openModal($event.detail)" x-show="isOpen" style="display: none;" class="fixed inset-0 z-[120] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="isOpen" x-transition class="relative z-10 inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle w-full max-w-4xl border border-slate-100 flex flex-col">
                <form @submit.prevent="saveKomitmen" class="flex flex-col">
                    <div class="bg-gradient-to-r from-blue-700 to-indigo-800 px-6 py-4 flex items-center justify-between shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-10 -mt-10 pointer-events-none"></div>
                        <h3 class="text-lg font-bold text-white tracking-wide relative z-10 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            FORM KOMITMEN LEAD MEASURE (LM)
                        </h3>
                        <button type="button" @click="closeModal()" class="text-white/80 hover:text-white hover:bg-white/20 rounded-full p-1.5 transition-colors focus:outline-none relative z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div class="p-6 bg-slate-50 overflow-y-auto max-h-[75vh]">
                        <!-- Top Info -->
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-4 mb-6 text-sm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div><span class="font-semibold text-slate-500 inline-block w-24">Unit</span> <span class="font-bold text-slate-800">: <span x-text="params.unitName"></span></span></div>
                                <div><span class="font-semibold text-slate-500 inline-block w-24">Tanggal</span> <span class="font-bold text-slate-800">: <span x-text="params.date"></span></span></div>
                                <div class="md:col-span-2"><span class="font-semibold text-slate-500 inline-block w-24">Lead Measure</span> <span class="font-bold text-indigo-700">: <span x-text="params.lmName"></span></span></div>
                                <div class="md:col-span-2 flex items-center gap-2">
                                    <span class="font-semibold text-slate-500 inline-block w-24">PIC LM</span> 
                                    <span>:</span>
                                    <input type="text" x-model="form.pic_lm" class="w-full md:w-1/2 p-1.5 border border-slate-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white" placeholder="Nama PIC LM">
                                </div>
                            </div>
                        </div>

                        <!-- 1. Target & Realisasi -->
                        <h4 class="font-bold text-slate-800 mb-3 flex items-center gap-2 uppercase text-sm">
                            <span class="bg-indigo-100 text-indigo-700 w-6 h-6 rounded-full inline-flex items-center justify-center text-xs">1</span> 
                            Target & Realisasi
                        </h4>
                        <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden mb-6">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-100 border-b border-slate-200 text-slate-700">
                                    <tr>
                                        <th class="py-2 px-4 text-left font-semibold">Target LM</th>
                                        <th class="py-2 px-4 text-left font-semibold">Realisasi Minggu Ini</th>
                                        <th class="py-2 px-4 text-left font-semibold">Gap</th>
                                        <th class="py-2 px-4 text-left font-semibold">% Pencapaian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="py-3 px-4 font-bold text-indigo-900" x-text="formatNumber(params.target)"></td>
                                        <td class="py-3 px-4 font-bold text-slate-800" x-text="formatNumber(params.realisasi)"></td>
                                        <td class="py-3 px-4 font-bold text-red-600" x-text="formatNumber(params.target - params.realisasi)"></td>
                                        <td class="py-3 px-4 font-bold">
                                            <span class="px-2 py-1 rounded text-xs" 
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
                                                    <textarea x-model="a.aksi" class="w-full p-1 border-0 bg-transparent resize-none focus:ring-0 text-sm h-10" placeholder="..."></textarea>
                                                </td>
                                                <td class="py-1 px-2 border-r border-slate-200">
                                                    <input type="text" x-model="a.target" class="w-full p-1 border-0 bg-transparent focus:ring-0 text-sm" placeholder="...">
                                                </td>
                                                <td class="py-1 px-2 border-r border-slate-200">
                                                    <input type="date" x-model="a.deadline" class="w-full p-1 border-0 bg-transparent focus:ring-0 text-xs text-slate-700">
                                                </td>
                                                <td class="py-1 px-2">
                                                    <textarea x-model="a.detail_komitmen" class="w-full p-1 border-0 bg-transparent resize-none focus:ring-0 text-sm h-10" placeholder="..."></textarea>
                                                </td>
                                                <td class="py-2 px-2 text-center text-red-400 hover:text-red-600">
                                                    <button type="button" @click="form.aksi_konkrits.splice(index, 1)" tabindex="-1" title="Hapus Baris">
                                                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div class="bg-slate-50 border-t border-slate-200 p-2 text-center">
                                <button type="button" @click="form.aksi_konkrits.push({aksi: '', target: '', deadline: '', detail_komitmen: ''})" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center justify-center gap-1 mx-auto">
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
                    sesi: null, lm: null, unit: null, target: 0, realisasi: 0, capai: 0, unitName: '', lmName: '', date: ''
                },
                form: {
                    pic_lm: '',
                    hambatans: [{hambatan: '', dukungan: ''}],
                    aksi_konkrits: [{aksi: '', target: '', deadline: '', detail_komitmen: ''}]
                },
                formatNumber(num) {
                    return Number(num).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
                },
                openModal(detail) {
                    this.params = detail;
                    this.form.pic_lm = '';
                    this.form.hambatans = [];
                    this.form.aksi_konkrits = [];
                    
                    // Fetch existing data
                    fetch(`/sesi-wigs/${this.params.sesi}/komitmen/${this.params.lm}/${this.params.unit}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.data) {
                                this.form.pic_lm = data.data.pic_lm || '';
                                this.form.hambatans = data.data.hambatans || [{hambatan: '', dukungan: ''}];
                                this.form.aksi_konkrits = data.data.aksi_konkrits || [{aksi: '', target: '', deadline: '', detail_komitmen: ''}];
                            } else {
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
EOD;

$content = str_replace('</x-app-layout>', $alpineComponent . "\n</x-app-layout>", $content);

file_put_contents($file, $content);
echo "View updated successfully.\n";
