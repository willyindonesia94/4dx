<?php
$path = 'resources/views/sesi-wigs/show.blade.php';
$content = file_get_contents($path);

// 1. Update colspan from 7 to 8
$content = str_replace(
    '<th colspan="7" class="px-4 py-2 border border-gray-300 text-center font-bold text-gray-800 bg-indigo-50">',
    '<th colspan="8" class="px-4 py-2 border border-gray-300 text-center font-bold text-gray-800 bg-indigo-50">',
    $content
);

// 2. Add TARGET+CARRY OVER header
$header_target = '<th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">TARGET</th>';
$header_target_new = $header_target . "\n" . '                                                    <th class="px-2 py-2 border border-gray-300 text-center font-semibold text-gray-700 bg-gray-50">TARGET+<br>CARRY OVER</th>';
$content = str_replace($header_target, $header_target_new, $content);

// 3. UID row logic
$uid_logic = <<<'EOD'
$uidCarryOver = max(0, $uidTarget - $uidRealisasi);
                                                        
                                                        $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
                                                        $prevUidRealisasi = 0;
EOD;
$uid_logic_new = <<<'EOD'
$uidCarryOver = max(0, $uidTarget - $uidRealisasi);
                                                        
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
                                                        
                                                        $prevUidRealisasi = 0;
EOD;
$content = str_replace($uid_logic, $uid_logic_new, $content);

$uid_td = '<td class="px-2 py-2 border border-gray-300 text-right font-black text-indigo-900">{{ number_format($uidTarget, 2) }}</td>';
$uid_td_new = '<td class="px-2 py-2 border border-gray-300 text-right font-black text-indigo-900">{{ number_format($uidTarget, 0, ",", ".") }}</td>' . "\n" . 
              '                                                    <td class="px-2 py-2 border border-gray-300 text-right font-black text-purple-900 bg-purple-50">{{ number_format($uidTargetPlusCarryOver, 0, ",", ".") }}</td>';
$content = str_replace($uid_td, $uid_td_new, $content);

// 4. UP3 row logic
$up3_logic = <<<'EOD'
$up3CarryOver = max(0, $up3Target - $up3Realisasi);
                                                            
                                                            $prevSw = $sesi_wigs_month->where('minggu_ke', $sw->minggu_ke - 1)->first();
EOD;
$up3_logic_new = <<<'EOD'
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
EOD;
$content = str_replace($up3_logic, $up3_logic_new, $content);

$up3_td = '<td class="px-2 py-2 border border-gray-300 text-right font-semibold">{{ number_format($up3Target, 2) }}</td>';
$up3_td_new = '<td class="px-2 py-2 border border-gray-300 text-right font-semibold">{{ number_format($up3Target, 0, ",", ".") }}</td>' . "\n" . 
              '                                                        <td class="px-2 py-2 border border-gray-300 text-right font-semibold text-purple-900 bg-purple-50">{{ number_format($up3TargetPlusCarryOver, 0, ",", ".") }}</td>';
$content = str_replace($up3_td, $up3_td_new, $content);

// 5. ULP row logic
$ulp_logic = <<<'EOD'
$ulpCarryOver = max(0, $target - $realisasi);
                                                                @endphp
EOD;
$ulp_logic_new = <<<'EOD'
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
EOD;
$content = str_replace($ulp_logic, $ulp_logic_new, $content);

$ulp_td = '<td class="px-2 py-2 border border-gray-300 text-right">{{ number_format($target, 2) }}</td>';
$ulp_td_new = '<td class="px-2 py-2 border border-gray-300 text-right">{{ number_format($target, 0, ",", ".") }}</td>' . "\n" . 
              '                                                                <td class="px-2 py-2 border border-gray-300 text-right text-purple-900 bg-purple-50">{{ number_format($ulpTargetPlusCarryOver, 0, ",", ".") }}</td>';
$content = str_replace($ulp_td, $ulp_td_new, $content);

// 6. Replace remaining number_format(..., 2) with number_format(..., 0, ',', '.')
$content = preg_replace('/number_format\(([^,]+?),\s*2\)/', 'number_format($1, 0, ",", ".")', $content);

file_put_contents($path, $content);
echo "Patch applied!\n";
