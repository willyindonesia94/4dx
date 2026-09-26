<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<style>
    @page Section1 {
        size: 1008pt 612pt; /* Legal landscape, approx 14in x 8.5in */
        mso-page-orientation: landscape;
        margin: 0.5in 0.5in 0.5in 0.5in;
    }
    div.Section1 { page: Section1; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 5px; vertical-align: top; }
</style>
</head>
<body>
<div class="Section1"><table>
    <thead>
        <tr>
            <th colspan="5" style="font-weight: bold; text-align: center; font-size: 14px;">
                KOMITMEN 4DX MINGGU KE-{{ $sesi->minggu_ke }} BULAN {{ strtoupper(date('F', mktime(0, 0, 0, $sesi->bulan, 10))) }} TAHUN {{ $sesi->tahun }}
            </th>
        </tr>
        <tr>
            <th colspan="5" style="font-weight: bold; text-align: center; font-size: 14px;">
                {{ strtoupper($wig->judul) }} - {{ $wig->deskripsi }}
            </th>
        </tr>
        <tr>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @php $up3Index = 1; @endphp
        
        @forelse($data as $up3Name => $ulps)
            <tr>
                <td colspan="5" style="font-weight: bold; font-size: 12px;">
                    {{ $up3Index++ }}. {{ strtoupper($up3Name) }}
                </td>
            </tr>
            <tr>
                <th style="font-weight: bold; background-color: #f3f4f6; text-align: center; border: 1px solid #000;">No.</th>
                <th style="font-weight: bold; background-color: #f3f4f6; text-align: center; border: 1px solid #000;">Unit / ULP</th>
                <th style="font-weight: bold; background-color: #f3f4f6; text-align: center; border: 1px solid #000;">Lead Measure (LM)</th>
                <th style="font-weight: bold; background-color: #f3f4f6; text-align: center; border: 1px solid #000;">Kendala &amp; Dukungan</th>
                <th style="font-weight: bold; background-color: #f3f4f6; text-align: center; border: 1px solid #000;">Aksi Konkrit / Usulan</th>
            </tr>
            
            @php
                $letters = range('a', 'z');
                $ulpIndex = 0;
            @endphp
            
            @foreach($ulps as $ulpName => $komitmens)
                @php
                    $rowCount = count($komitmens);
                    if ($rowCount == 0) continue;
                @endphp
                
                @foreach($komitmens as $i => $k)
                <tr>
                    @if($i == 0)
                        <td rowspan="{{ $rowCount }}" style="text-align: center; border: 1px solid #000; vertical-align: top;">{{ $letters[$ulpIndex] ?? '-' }}.</td>
                        <td rowspan="{{ $rowCount }}" style="border: 1px solid #000; vertical-align: top;">{{ $ulpName }}</td>
                    @endif
                    
                    <td style="border: 1px solid #000; vertical-align: top;">
                        <strong>{{ $k->masterLm->judul_lm ?? 'LM' }}</strong><br>
                        @if(!empty($k->komitmen))
                            <span style="color: #666666;">{{ $k->komitmen }}</span>
                        @endif
                    </td>
                    
                    <td style="border: 1px solid #000; vertical-align: top;">
                        @if(is_array($k->hambatans) && count($k->hambatans) > 0)
                            @foreach($k->hambatans as $h)
                                @if(!empty($h['hambatan']))
                                    H: {{ $h['hambatan'] }}<br>
                                    @if(!empty($h['dukungan']))
                                        D: {{ $h['dukungan'] }}<br>
                                    @endif
                                @endif
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    
                    <td style="border: 1px solid #000; vertical-align: top;">
                        @if(is_array($k->aksi_konkrits) && count($k->aksi_konkrits) > 0)
                            @foreach($k->aksi_konkrits as $a)
                                @if(!empty($a['aksi']))
                                    {{ $a['aksi'] }} 
                                    @if(!empty($a['target'])) (Tgt: {{ $a['target'] }}) @endif<br>
                                    @if(!empty($a['detail_komitmen']))
                                        {{ $a['detail_komitmen'] }}<br>
                                    @endif
                                @endif
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
                
                @php $ulpIndex++; @endphp
            @endforeach
            
            <tr>
                <td colspan="5"></td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center;">Tidak ada data komitmen yang ditemukan untuk kriteria ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>

</div>
</body>
</html>
