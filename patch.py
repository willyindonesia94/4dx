import re

path = r"resources\views\sesi-wigs\show.blade.php"
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update colspan from 7 to 8
content = re.sub(r'<th colspan="7" class="px-4 py-2 border border-gray-300 text-center font-bold text-gray-800 bg-indigo-50">', 
                 r'<th colspan="8" class="px-4 py-2 border border-gray-300 text-center font-bold text-gray-800 bg-indigo-50">', content)

# 2. Add TARGET+CARRY OVER header
header_target = r'<th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">TARGET</th>'
header_target_new = r'<th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">TARGET</th>' + '\n' + \
                    r'                                                    <th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">TARGET+<br>CARRY OVER</th>'
content = content.replace(header_target, header_target_new)

# 3. UID row logic
uid_logic = r'''$uidCarryOver = max(0, $uidTarget - $uidRealisasi);
                                                        
                                                        $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                        $prevUidRealisasi = 0;'''
uid_logic_new = r'''$uidCarryOver = max(0, $uidTarget - $uidRealisasi);
                                                        
                                                        $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                        $prevUidTarget = 0;
                                                        $prevUidRealisasi = 0;
                                                        if ($prevSw) {
                                                            foreach($up3s as $up3Unit) {
                                                                $prevUidTarget += $matrixTargets[$lm->id][$up3Unit->id][$prevSw->id] ?? 0;
                                                                $prevUidRealisasi += $matrixRealisasi[$lm->id][$up3Unit->id][$prevSw->id] ?? 0;
                                                            }
                                                        }
                                                        $prevUidCarryOver = max(0, $prevUidTarget - $prevUidRealisasi);
                                                        $uidTargetPlusCarryOver = $uidTarget + $prevUidCarryOver;
                                                        
                                                        $prevUidRealisasi = 0;'''
content = content.replace(uid_logic, uid_logic_new)

# UID td
uid_td = r'<td class="px-2 py-2 border border-gray-300 text-right font-black text-indigo-900">{{ number_format($uidTarget, 2) }}</td>'
uid_td_new = r'<td class="px-2 py-2 border border-gray-300 text-right font-black text-indigo-900">{{ number_format($uidTarget, 0, ",", ".") }}</td>' + '\n' + \
             r'                                                    <td class="px-2 py-2 border border-gray-300 text-right font-black text-purple-900 bg-purple-50">{{ number_format($uidTargetPlusCarryOver, 0, ",", ".") }}</td>'
content = content.replace(uid_td, uid_td_new)

# 4. UP3 row logic
up3_logic = r'''$up3CarryOver = max(0, $up3Target - $up3Realisasi);
                                                            
                                                            $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();'''
up3_logic_new = r'''$up3CarryOver = max(0, $up3Target - $up3Realisasi);
                                                            
                                                            $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                            $prevUp3Target = 0;
                                                            $prevUp3Realisasi = 0;
                                                            if ($prevSw) {
                                                                $prevUp3Target = $matrixTargets[$lm->id][$up3->id][$prevSw->id] ?? 0;
                                                                $prevUp3Realisasi = $matrixRealisasi[$lm->id][$up3->id][$prevSw->id] ?? 0;
                                                            }
                                                            $prevUp3CarryOver = max(0, $prevUp3Target - $prevUp3Realisasi);
                                                            $up3TargetPlusCarryOver = $up3Target + $prevUp3CarryOver;'''
content = content.replace(up3_logic, up3_logic_new)

up3_td = r'<td class="px-2 py-2 border border-gray-300 text-right font-semibold">{{ number_format($up3Target, 2) }}</td>'
up3_td_new = r'<td class="px-2 py-2 border border-gray-300 text-right font-semibold">{{ number_format($up3Target, 0, ",", ".") }}</td>' + '\n' + \
             r'                                                        <td class="px-2 py-2 border border-gray-300 text-right font-semibold text-purple-900 bg-purple-50">{{ number_format($up3TargetPlusCarryOver, 0, ",", ".") }}</td>'
content = content.replace(up3_td, up3_td_new)

# 5. ULP row logic
ulp_logic = r'''$ulpCarryOver = max(0, $target - $realisasi);
                                                                @endphp'''
ulp_logic_new = r'''$ulpCarryOver = max(0, $target - $realisasi);
                                                                    
                                                                    $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                                    $prevUlpTarget = 0;
                                                                    $prevUlpRealisasi = 0;
                                                                    if ($prevSw) {
                                                                        $prevUlpTarget = $matrixTargets[$lm->id][$u->id][$prevSw->id] ?? 0;
                                                                        $prevUlpRealisasi = $matrixRealisasi[$lm->id][$u->id][$prevSw->id] ?? 0;
                                                                    }
                                                                    $prevUlpCarryOver = max(0, $prevUlpTarget - $prevUlpRealisasi);
                                                                    $ulpTargetPlusCarryOver = $target + $prevUlpCarryOver;
                                                                @endphp'''
content = content.replace(ulp_logic, ulp_logic_new)

ulp_td = r'<td class="px-2 py-2 border border-gray-300 text-right">{{ number_format($target, 2) }}</td>'
ulp_td_new = r'<td class="px-2 py-2 border border-gray-300 text-right">{{ number_format($target, 0, ",", ".") }}</td>' + '\n' + \
             r'                                                                <td class="px-2 py-2 border border-gray-300 text-right text-purple-900 bg-purple-50">{{ number_format($ulpTargetPlusCarryOver, 0, ",", ".") }}</td>'
content = content.replace(ulp_td, ulp_td_new)

# 6. Replace remaining number_format(..., 2) with number_format(..., 0, ',', '.')
content = re.sub(r'number_format\(([^,]+?),\s*2\)', r'number_format(\1, 0, ",", ".")', content)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Patch applied!")
