<?php
$file = 'resources/views/sesi-wigs/show.blade.php';
$content = file_get_contents($file);

// Fix $komitmenData initialization in ULP loop
$content = preg_replace(
    '/\$komitmenData\s*=\s*\$matrixKomitmen\[\$lm->id\]\[\$u->id\]\[\$sw->id\]\s*\?\?\s*\[\'komitmen\'\s*=>\s*\'\',\s*\'carry_over\'\s*=>\s*\'\'\];\s*\$komitmenVal\s*=\s*\$komitmenData\[\'komitmen\'\];\s*\$carryOverVal\s*=\s*\$komitmenData\[\'carry_over\'\];/',
    '$komitmenData = $matrixKomitmen[$lm->id][$u->id][$sw->id] ?? null;
                                                                $komitmenVal = $komitmenData ? $komitmenData[\'komitmen\'] : \'\';
                                                                $carryOverVal = $komitmenData ? $komitmenData[\'carry_over\'] : \'\';',
    $content
);

// We should just manually find and replace the cells using str_replace to be 100% safe.
// 1. UP3 Komitmen cell
$up3Search = '<td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">
                                                            @php $hasKom = isset($matrixKomitmen[$lm->id][$up3->id][$sw->id]); @endphp
                                                            <button type="button" 
                                                                @click="window.dispatchEvent(new CustomEvent(\'open-komitmen\', { detail: { sesi: {{ $sw->id }}, lm: {{ $lm->id }}, unit: {{ $up3->id }}, target: {{ $up3Target }}, realisasi: {{ $up3Realisasi }}, capai: {{ $up3Pencapaian }}, unitName: \'{{ addslashes($up3->name) }}\', lmName: \'{{ addslashes($lm->judul_lm) }}\', wigName: \'{{ addslashes($wig->judul) }}\', date: \'{{ \Carbon\Carbon::parse($sw->tanggal_pelaksanaan)->format(\'d/m/Y\') }}\' } }))"
                                                                class="inline-flex items-center justify-center w-7 h-7 rounded-full transition-all shadow-sm focus:outline-none {{ $hasKom ? \'bg-green-100 text-green-600 hover:bg-green-200 border border-green-200\' : \'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-200\' }}">
                                                                @if($hasKom)
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                @else
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                                @endif
                                                            </button>
                                                        </td>';
$up3Replace = '<td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">
                                                            @php 
                                                                $komData = $matrixKomitmen[$lm->id][$up3->id][$sw->id] ?? null;
                                                                $hasKom = $komData !== null;
                                                                $komitmenVal = $hasKom ? $komData[\'komitmen\'] : \'\';
                                                            @endphp
                                                            <div class="flex items-center justify-center gap-1">
                                                                <span class="text-xs font-semibold text-gray-700">{{ $komitmenVal !== \'\' && $komitmenVal !== null ? number_format((float)$komitmenVal, 2) : \'-\' }}</span>
                                                                <button type="button" 
                                                                    @click="window.dispatchEvent(new CustomEvent(\'open-komitmen\', { detail: { sesi: {{ $sw->id }}, lm: {{ $lm->id }}, unit: {{ $up3->id }}, target: {{ $up3Target }}, realisasi: {{ $up3Realisasi }}, capai: {{ $up3Pencapaian }}, unitName: \'{{ addslashes($up3->name) }}\', lmName: \'{{ addslashes($lm->judul_lm) }}\', wigName: \'{{ addslashes($wig->judul) }}\', date: \'{{ \Carbon\Carbon::parse($sw->tanggal_pelaksanaan)->format(\'d/m/Y\') }}\' } }))"
                                                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full transition-all shadow-sm focus:outline-none {{ $hasKom ? \'bg-green-100 text-green-600 hover:bg-green-200 border border-green-200\' : \'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-200\' }}"
                                                                    title="{{ $hasKom ? \'Edit Form Komitmen\' : \'Isi Form Komitmen\' }}">
                                                                    @if($hasKom)
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                    @else
                                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                                    @endif
                                                                </button>
                                                            </div>
                                                        </td>';
$content = str_replace($up3Search, $up3Replace, $content);

// 2. ULP Komitmen cell
$ulpSearch = '<td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">
                                                                @php $hasKom = isset($matrixKomitmen[$lm->id][$u->id][$sw->id]); @endphp
                                                                <button type="button" 
                                                                    @click="window.dispatchEvent(new CustomEvent(\'open-komitmen\', { detail: { sesi: {{ $sw->id }}, lm: {{ $lm->id }}, unit: {{ $u->id }}, target: {{ $target }}, realisasi: {{ $realisasi }}, capai: {{ $pencapaian }}, unitName: \'{{ addslashes($u->name) }}\', lmName: \'{{ addslashes($lm->judul_lm) }}\', wigName: \'{{ addslashes($wig->judul) }}\', date: \'{{ \Carbon\Carbon::parse($sw->tanggal_pelaksanaan)->format(\'d/m/Y\') }}\' } }))"
                                                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full transition-all shadow-sm focus:outline-none {{ $hasKom ? \'bg-green-100 text-green-600 hover:bg-green-200 border border-green-200\' : \'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-200\' }}"
                                                                    title="{{ $hasKom ? \'Edit Form Komitmen\' : \'Isi Form Komitmen\' }}">
                                                                    @if($hasKom)
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                    @else
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                                    @endif
                                                                </button>
                                                            </td>';
$ulpReplace = '<td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">
                                                                @php 
                                                                    $komData = $matrixKomitmen[$lm->id][$u->id][$sw->id] ?? null;
                                                                    $hasKom = $komData !== null;
                                                                    $komitmenVal = $hasKom ? $komData[\'komitmen\'] : \'\';
                                                                @endphp
                                                                <div class="flex items-center justify-center gap-1">
                                                                    @if($canEdit)
                                                                        <input type="number" step="any" class="w-16 text-xs p-1 border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 komitmen-input" 
                                                                            data-lm="{{ $lm->id }}" data-unit="{{ $u->id }}" data-sesi="{{ $sw->id }}" data-type="komitmen"
                                                                            value="{{ $komitmenVal }}" placeholder="-">
                                                                    @else
                                                                        <span class="text-xs font-semibold text-gray-700">{{ $komitmenVal !== \'\' && $komitmenVal !== null ? number_format((float)$komitmenVal, 2) : \'-\' }}</span>
                                                                    @endif
                                                                    <button type="button" 
                                                                        @click="window.dispatchEvent(new CustomEvent(\'open-komitmen\', { detail: { sesi: {{ $sw->id }}, lm: {{ $lm->id }}, unit: {{ $u->id }}, target: {{ $target }}, realisasi: {{ $realisasi }}, capai: {{ $pencapaian }}, unitName: \'{{ addslashes($u->name) }}\', lmName: \'{{ addslashes($lm->judul_lm) }}\', wigName: \'{{ addslashes($wig->judul) }}\', date: \'{{ \Carbon\Carbon::parse($sw->tanggal_pelaksanaan)->format(\'d/m/Y\') }}\' } }))"
                                                                        class="inline-flex items-center justify-center w-6 h-6 shrink-0 rounded-full transition-all shadow-sm focus:outline-none {{ $hasKom ? \'bg-green-100 text-green-600 hover:bg-green-200 border border-green-200\' : \'bg-gray-100 text-gray-500 hover:bg-gray-200 border border-gray-200\' }}"
                                                                        title="{{ $hasKom ? \'Edit Form Komitmen\' : \'Isi Form Komitmen\' }}">
                                                                        @if($hasKom)
                                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                        @else
                                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                                        @endif
                                                                    </button>
                                                                </div>
                                                            </td>';
$content = str_replace($ulpSearch, $ulpReplace, $content);


// 3. ULP Carry Over cell
$ulpCarrySearch = '<td class="px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50">-</td>
                                                            <td class="px-2 py-2 border border-gray-300 text-center">{!! $trendIcon !!}</td>';
$ulpCarryReplace = '<td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">
                                                                @php 
                                                                    $komData = $matrixKomitmen[$lm->id][$u->id][$sw->id] ?? null;
                                                                    $carryOverVal = $komData ? $komData[\'carry_over\'] : \'\';
                                                                @endphp
                                                                @if($canEdit)
                                                                    <input type="number" step="any" class="w-16 text-xs p-1 border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 komitmen-input" 
                                                                        data-lm="{{ $lm->id }}" data-unit="{{ $u->id }}" data-sesi="{{ $sw->id }}" data-type="carry_over"
                                                                        value="{{ $carryOverVal }}" placeholder="-">
                                                                @else
                                                                    <span class="text-xs font-semibold text-gray-700">{{ $carryOverVal !== \'\' && $carryOverVal !== null ? number_format((float)$carryOverVal, 2) : \'-\' }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-2 py-2 border border-gray-300 text-center">{!! $trendIcon !!}</td>';
$content = str_replace($ulpCarrySearch, $ulpCarryReplace, $content);

file_put_contents($file, $content);
echo "Updated ULP Inputs.\n";
