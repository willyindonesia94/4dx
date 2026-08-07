<?php
$file = 'resources/views/sesi-wigs/show.blade.php';
$content = file_get_contents($file);

// 1. MINGGU X header (colspan 6 -> 7)
$content = str_replace('<th colspan="6" class="px-4 py-2 border border-gray-300 text-center font-bold text-gray-800 bg-indigo-50">', '<th colspan="7" class="px-4 py-2 border border-gray-300 text-center font-bold text-gray-800 bg-indigo-50">', $content);

// 2. KOMITMEN header
$headerSearch = '<th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50 w-24">Form Komitmen</th>';
$headerReplace = '<th colspan="2" class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">KOMITMEN</th>';
$content = str_replace($headerSearch, $headerReplace, $content);

// 3. UID Level
$uidCellSearch = '<td class="px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50">-</td>';
$uidCellReplace = '<td class="px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50">-</td>
                                                    <td class="px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50">-</td>';
// Using preg_replace with limit 1 just in case, but actually there are 2 such cells in the UID block (one for Komitmen, one for Carry Over... wait!).
// In my check:
// <td class="px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50">-</td>  (komitmen)
// <td class="px-2 py-2 border border-gray-300 text-right font-black text-indigo-900">{{ number_format($uidRealisasi, 2) }}</td>
// <td class="px-2 py-2 border border-gray-300 text-right font-black {{ $uidBgColor }}">{{ $uidPencapaian }}%</td>
// <td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">{{ $uidCarryOver > 0 ? number_format($uidCarryOver, 2) : '0' }}</td>
// So the only one with text-gray-400 and - is the Komitmen cell!
$content = preg_replace('/<td class="px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50">-<\/td>/', '<td class="px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50">-</td><td class="px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50">-</td>', $content, 1);


// 4. UP3 Level
$up3Search = '<div class="flex items-center justify-center gap-1">
                                                                <span class="text-xs font-semibold text-gray-700">{{ $komitmenVal !== \'\' && $komitmenVal !== null ? number_format((float)$komitmenVal, 2) : \'-\' }}</span>
                                                                <button type="button"';

$up3Replace = '<span class="text-xs font-semibold text-gray-700">{{ $komitmenVal !== \'\' && $komitmenVal !== null ? number_format((float)$komitmenVal, 2) : \'-\' }}</span>
                                                            </td>
                                                            <td class="px-2 py-2 border border-gray-300 text-center bg-slate-50 w-10">
                                                                <button type="button"';

$content = str_replace($up3Search, $up3Replace, $content);
// Also need to remove the closing </div> of that flex container
// It is right before </td>
$up3EndSearch = '                                                                </button>
                                                            </div>
                                                        </td>';
$up3EndReplace = '                                                                </button>
                                                        </td>';
$content = str_replace($up3EndSearch, $up3EndReplace, $content);


// 5. ULP Level
$ulpSearch = '<div class="flex items-center justify-center gap-1">
                                                                    @if($canEdit)
                                                                        <input type="number" step="any" class="w-16 text-xs p-1 border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 komitmen-input" 
                                                                            data-lm="{{ $lm->id }}" data-unit="{{ $u->id }}" data-sesi="{{ $sw->id }}" data-type="komitmen"
                                                                            value="{{ $komitmenVal }}" placeholder="-">
                                                                    @else
                                                                        <span class="text-xs font-semibold text-gray-700">{{ $komitmenVal !== \'\' && $komitmenVal !== null ? number_format((float)$komitmenVal, 2) : \'-\' }}</span>
                                                                    @endif
                                                                    <button type="button"';

$ulpReplace = '@if($canEdit)
                                                                        <input type="number" step="any" class="w-16 text-xs p-1 border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 komitmen-input" 
                                                                            data-lm="{{ $lm->id }}" data-unit="{{ $u->id }}" data-sesi="{{ $sw->id }}" data-type="komitmen"
                                                                            value="{{ $komitmenVal }}" placeholder="-">
                                                                    @else
                                                                        <span class="text-xs font-semibold text-gray-700">{{ $komitmenVal !== \'\' && $komitmenVal !== null ? number_format((float)$komitmenVal, 2) : \'-\' }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="px-2 py-2 border border-gray-300 text-center bg-slate-50 w-10">
                                                                    <button type="button"';

$content = str_replace($ulpSearch, $ulpReplace, $content);
$ulpEndSearch = '                                                                    @endif
                                                                    </button>
                                                                </div>
                                                            </td>';
$ulpEndReplace = '                                                                    @endif
                                                                    </button>
                                                            </td>';
$content = str_replace($ulpEndSearch, $ulpEndReplace, $content);

file_put_contents($file, $content);
echo "View updated to split Komitmen column!\n";
