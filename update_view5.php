<?php
$file = 'resources/views/sesi-wigs/show.blade.php';
$content = file_get_contents($file);

// 1. Add Carry Over input to the Modal Top Info
$komitmenSearch = '<div class="md:col-span-2 flex items-center gap-2 mt-2">
                                    <span class="font-semibold text-slate-500 inline-block w-24">Angka Komitmen</span> 
                                    <span>:</span>
                                    <input type="number" step="any" x-model="form.komitmen" class="w-32 p-1.5 border border-slate-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white" placeholder="Target Angka">
                                </div>';
$komitmenReplace = '<div class="md:col-span-2 flex items-center gap-4 mt-2">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-500 inline-block w-24">Angka Komitmen</span> 
                                        <span>:</span>
                                        <input type="number" step="any" x-model="form.komitmen" class="w-32 p-1.5 border border-slate-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white" placeholder="Target Angka">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-500">Carry Over :</span> 
                                        <input type="number" step="any" x-model="form.carry_over" class="w-32 p-1.5 border border-slate-300 rounded text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white" placeholder="Carry Over">
                                    </div>
                                </div>';
$content = str_replace($komitmenSearch, $komitmenReplace, $content);

// 2. Update Alpine form payload to include carry_over
$alpineFormSearch = "form: {
                    pic_lm: '',
                    komitmen: '',
                    hambatans: [{hambatan: '', dukungan: ''}],
                    aksi_konkrits: [{aksi: '', target: '', deadline: '', detail_komitmen: ''}]
                },";
$alpineFormReplace = "form: {
                    pic_lm: '',
                    komitmen: '',
                    carry_over: '',
                    hambatans: [{hambatan: '', dukungan: ''}],
                    aksi_konkrits: [{aksi: '', target: '', deadline: '', detail_komitmen: ''}]
                },";
$content = str_replace($alpineFormSearch, $alpineFormReplace, $content);

// 3. Update Alpine openModal logic to populate carry_over
$openModalSearch = "this.form.komitmen = data.data.komitmen || '';";
$openModalReplace = "this.form.komitmen = data.data.komitmen || '';
                                this.form.carry_over = data.data.carry_over || '';";
$content = str_replace($openModalSearch, $openModalReplace, $content);

// 4. In case it doesn't have data, reset carry_over
$openModalElseSearch = "this.form.komitmen = '';
                                this.form.hambatans = [{hambatan: '', dukungan: ''}];";
$openModalElseReplace = "this.form.komitmen = '';
                                this.form.carry_over = '';
                                this.form.hambatans = [{hambatan: '', dukungan: ''}];";
$content = str_replace($openModalElseSearch, $openModalElseReplace, $content);

// 5. Custom event payload reset
$resetSearch = "this.form.komitmen = '';
                    this.form.hambatans = [];";
$resetReplace = "this.form.komitmen = '';
                    this.form.carry_over = '';
                    this.form.hambatans = [];";
$content = str_replace($resetSearch, $resetReplace, $content);

// 6. Replace UP3 Carry Over cell to display carry_over if entered
$up3CarrySearch = '<td class="px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50">-</td>
                                                        <td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">{!! $up3TrendIcon !!}</td>';
$up3CarryReplace = '<td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">
                                                            @php 
                                                                $komData = $matrixKomitmen[$lm->id][$up3->id][$sw->id] ?? null;
                                                                $carryOverVal = $komData ? $komData[\'carry_over\'] : \'\';
                                                            @endphp
                                                            <span class="text-xs font-semibold text-gray-700">{{ $carryOverVal !== \'\' && $carryOverVal !== null ? number_format((float)$carryOverVal, 2) : \'-\' }}</span>
                                                        </td>
                                                        <td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">{!! $up3TrendIcon !!}</td>';
$content = str_replace($up3CarrySearch, $up3CarryReplace, $content);

file_put_contents($file, $content);
echo "Updated Modal and UP3 cell for Carry Over.\n";
