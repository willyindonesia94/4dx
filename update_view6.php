<?php
$file = 'resources/views/sesi-wigs/show.blade.php';
$content = file_get_contents($file);

// 1. UID Level Logic
$uidSearch = "\$uidPencapaian = \$uidTarget > 0 ? round((\$uidRealisasi / \$uidTarget) * 100, 2) : 0;
                                                        \$uidBgColor = \$uidPencapaian < 100 ? 'bg-red-500 text-white' : 'bg-green-500 text-white';";
$uidReplace = "\$uidPencapaian = \$uidTarget > 0 ? round((\$uidRealisasi / \$uidTarget) * 100, 2) : 0;
                                                        \$uidBgColor = 'bg-red-500 text-white';
                                                        if (\$uidPencapaian >= 100) {
                                                            \$uidBgColor = 'bg-green-500 text-white'; // No komitmen on UID level currently
                                                        }
                                                        \$uidCarryOver = max(0, \$uidTarget - \$uidRealisasi);";
$content = str_replace($uidSearch, $uidReplace, $content);

// UID Carry Over Display
$uidCarryOverSearch = "<td class=\"px-2 py-2 border border-gray-300 text-right font-black {{ \$uidBgColor }}\">{{ \$uidPencapaian }}%</td>
                                                    <td class=\"px-2 py-2 border border-gray-300 text-center text-gray-400 bg-slate-50\">-</td>";
$uidCarryOverReplace = "<td class=\"px-2 py-2 border border-gray-300 text-right font-black {{ \$uidBgColor }}\">{{ \$uidPencapaian }}%</td>
                                                    <td class=\"px-2 py-2 border border-gray-300 text-center bg-slate-50\">{{ \$uidCarryOver > 0 ? number_format(\$uidCarryOver, 2) : '0' }}</td>";
$content = str_replace($uidCarryOverSearch, $uidCarryOverReplace, $content);


// 2. UP3 Level Logic
$up3Search = "\$up3Pencapaian = \$up3Target > 0 ? round((\$up3Realisasi / \$up3Target) * 100, 2) : 0;
                                                            \$up3BgColor = \$up3Pencapaian < 100 ? 'bg-red-500 text-white' : 'bg-green-500 text-white';";
$up3Replace = "\$up3Pencapaian = \$up3Target > 0 ? round((\$up3Realisasi / \$up3Target) * 100, 2) : 0;
                                                            
                                                            \$up3KomData = \$matrixKomitmen[\$lm->id][\$up3->id][\$sw->id] ?? null;
                                                            \$up3KomVal = \$up3KomData !== null ? floatval(\$up3KomData['komitmen']) : 0;
                                                            
                                                            \$up3BgColor = 'bg-red-500 text-white';
                                                            if (\$up3Pencapaian >= 100) {
                                                                if (\$up3KomData !== null && \$up3KomVal > 0 && \$up3Realisasi < \$up3KomVal) {
                                                                    \$up3BgColor = 'bg-orange-500 text-white';
                                                                } else {
                                                                    \$up3BgColor = 'bg-green-500 text-white';
                                                                }
                                                            }
                                                            \$up3CarryOver = max(0, \$up3Target - \$up3Realisasi);";
$content = str_replace($up3Search, $up3Replace, $content);

// UP3 Carry Over Display
$up3CarrySearch = '<td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">
                                                            @php 
                                                                $komData = $matrixKomitmen[$lm->id][$up3->id][$sw->id] ?? null;
                                                                $carryOverVal = $komData ? $komData[\'carry_over\'] : \'\';
                                                            @endphp
                                                            <span class="text-xs font-semibold text-gray-700">{{ $carryOverVal !== \'\' && $carryOverVal !== null ? number_format((float)$carryOverVal, 2) : \'-\' }}</span>
                                                        </td>';
$up3CarryReplace = '<td class="px-2 py-2 border border-gray-300 text-center bg-slate-50 text-xs font-semibold text-gray-700">
                                                            {{ $up3CarryOver > 0 ? number_format($up3CarryOver, 2) : \'0\' }}
                                                        </td>';
$content = str_replace($up3CarrySearch, $up3CarryReplace, $content);


// 3. ULP Level Logic
$ulpSearch = "\$pencapaian = \$target > 0 ? round((\$realisasi / \$target) * 100, 2) : 0;
                                                                \$bgColor = \$pencapaian < 100 ? 'bg-red-500 text-white' : 'bg-green-500 text-white';";
$ulpReplace = "\$pencapaian = \$target > 0 ? round((\$realisasi / \$target) * 100, 2) : 0;
                                                                \$ulpKomData = \$matrixKomitmen[\$lm->id][\$u->id][\$sw->id] ?? null;
                                                                \$ulpKomVal = \$ulpKomData !== null ? floatval(\$ulpKomData['komitmen']) : 0;
                                                                
                                                                \$bgColor = 'bg-red-500 text-white';
                                                                if (\$pencapaian >= 100) {
                                                                    if (\$ulpKomData !== null && \$ulpKomVal > 0 && \$realisasi < \$ulpKomVal) {
                                                                        \$bgColor = 'bg-orange-500 text-white';
                                                                    } else {
                                                                        \$bgColor = 'bg-green-500 text-white';
                                                                    }
                                                                }
                                                                \$ulpCarryOver = max(0, \$target - \$realisasi);";
$content = str_replace($ulpSearch, $ulpReplace, $content);

// ULP Carry Over Display
$ulpCarrySearch = '<td class="px-2 py-2 border border-gray-300 text-center bg-slate-50">
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
                                                            </td>';
$ulpCarryReplace = '<td class="px-2 py-2 border border-gray-300 text-center bg-slate-50 text-xs font-semibold text-gray-700">
                                                                {{ $ulpCarryOver > 0 ? number_format($ulpCarryOver, 2) : \'0\' }}
                                                            </td>';
$content = str_replace($ulpCarrySearch, $ulpCarryReplace, $content);

file_put_contents($file, $content);
echo "View updated with automatic carry over and color logic!\n";
