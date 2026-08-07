<?php
$file = 'resources/views/sesi-wigs/show.blade.php';
$content = file_get_contents($file);

// 1. Replace Header styling
$headerSearch = '<div class="bg-gradient-to-r from-blue-700 to-indigo-800 px-6 py-4 flex items-center justify-between shadow-sm relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-5 rounded-full -mr-10 -mt-10 pointer-events-none"></div>
                        <h3 class="text-lg font-bold text-white tracking-wide relative z-10 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            FORM KOMITMEN LEAD MEASURE (LM)
                        </h3>
                        <button type="button" @click="closeModal()" class="text-white/80 hover:text-white hover:bg-white/20 rounded-full p-1.5 transition-colors focus:outline-none relative z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>';

$headerReplace = '<div class="bg-gradient-to-r from-blue-50 to-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-extrabold text-gray-900 flex items-center gap-2" id="modal-title">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            FORM KOMITMEN LEAD MEASURE (LM)
                        </h3>
                        <button @click="closeModal()" type="button" class="text-gray-400 hover:text-gray-600 transition bg-white hover:bg-gray-100 rounded-full p-1 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>';

$content = str_replace($headerSearch, $headerReplace, $content);

// 2. Add Angka Komitmen Input below PIC LM
$picLmSearch = '<div class="md:col-span-2 flex items-center gap-2">
                                    <span class="font-semibold text-slate-500 inline-block w-24">PIC LM</span> 
                                    <span>:</span>
                                    <input type="text" x-model="form.pic_lm" class="w-full md:w-1/2 p-1.5 border border-slate-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white" placeholder="Nama PIC LM">
                                </div>';
$picLmReplace = '<div class="md:col-span-2 flex items-center gap-2">
                                    <span class="font-semibold text-slate-500 inline-block w-24">PIC LM</span> 
                                    <span>:</span>
                                    <input type="text" x-model="form.pic_lm" class="w-full md:w-1/2 p-1.5 border border-slate-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white" placeholder="Nama PIC LM">
                                </div>
                                <div class="md:col-span-2 flex items-center gap-2 mt-2">
                                    <span class="font-semibold text-slate-500 inline-block w-24">Angka Komitmen</span> 
                                    <span>:</span>
                                    <input type="number" step="any" x-model="form.komitmen" class="w-32 p-1.5 border border-slate-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white" placeholder="Target Angka">
                                </div>';
$content = str_replace($picLmSearch, $picLmReplace, $content);

// 3. Update Alpine form payload to include komitmen
$alpineFormSearch = "form: {
                    pic_lm: '',
                    hambatans: [{hambatan: '', dukungan: ''}],
                    aksi_konkrits: [{aksi: '', target: '', deadline: '', detail_komitmen: ''}]
                },";
$alpineFormReplace = "form: {
                    pic_lm: '',
                    komitmen: '',
                    hambatans: [{hambatan: '', dukungan: ''}],
                    aksi_konkrits: [{aksi: '', target: '', deadline: '', detail_komitmen: ''}]
                },";
$content = str_replace($alpineFormSearch, $alpineFormReplace, $content);

// 4. Update Alpine openModal logic to populate komitmen
$openModalSearch = "this.form.pic_lm = data.data.pic_lm || '';";
$openModalReplace = "this.form.pic_lm = data.data.pic_lm || '';
                                this.form.komitmen = data.data.komitmen || '';";
$content = str_replace($openModalSearch, $openModalReplace, $content);

// 5. In case it doesn't have data, reset komitmen
$openModalElseSearch = "this.form.hambatans = [{hambatan: '', dukungan: ''}];";
$openModalElseReplace = "this.form.komitmen = '';
                                this.form.hambatans = [{hambatan: '', dukungan: ''}];";
$content = str_replace($openModalElseSearch, $openModalElseReplace, $content);

// 6. Also, in the custom event payload, pass komitmen if we can, or since we are fetching from backend anyway we don't strictly need to pass it, but we can reset it initially.
$resetSearch = "this.form.pic_lm = '';
                    this.form.hambatans = [];
                    this.form.aksi_konkrits = [];";
$resetReplace = "this.form.pic_lm = '';
                    this.form.komitmen = '';
                    this.form.hambatans = [];
                    this.form.aksi_konkrits = [];";
$content = str_replace($resetSearch, $resetReplace, $content);


// 7. Fix modal background if necessary. The user said "background nya sama seperti form yang lain". Usually forms have bg-white. Let's change bg-slate-50 to bg-white.
$content = str_replace('class="p-6 bg-slate-50 overflow-y-auto max-h-[75vh]"', 'class="p-6 bg-white overflow-y-auto max-h-[75vh]"', $content);

file_put_contents($file, $content);
echo "View updated!\n";
