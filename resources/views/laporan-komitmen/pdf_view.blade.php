<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Komitmen 4DX</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 8px; }
        .mb-4 { margin-bottom: 16px; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }
        th {
            background-color: #f3f4f6;
        }
        
        /* Layout utilities */
        .w-5 { width: 5%; }
        .w-20 { width: 20%; }
        .w-35 { width: 35%; }
        .w-40 { width: 40%; }
        
        .up3-title {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .list-item {
            margin-bottom: 4px;
            padding-bottom: 4px;
            border-bottom: 1px dashed #ccc;
        }
        .list-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
    </style>
</head>
<body>

    <div class="text-center mb-4">
        <h2 class="font-bold mb-2">KOMITMEN 4DX MINGGU KE-{{ $sesi->minggu_ke }} BULAN {{ strtoupper(date('F', mktime(0, 0, 0, $sesi->bulan, 10))) }} TAHUN {{ $sesi->tahun }}</h2>
        <h3 class="font-bold">{{ strtoupper($wig->judul) }} - {{ $wig->deskripsi }}</h3>
    </div>

    @php $up3Index = 1; @endphp
    
    @forelse($data as $up3Name => $ulps)
        <div class="up3-title">{{ $up3Index++ }}. {{ strtoupper($up3Name) }}</div>
        
        <table>
            <thead>
                <tr>
                    <th class="w-5 text-center">No.</th>
                    <th class="w-20 text-center">Unit / ULP</th>
                    <th class="w-35 text-center">Lead Measure (LM)</th>
                    <th class="w-20 text-center">Kendala & Dukungan</th>
                    <th class="w-20 text-center">Aksi Konkrit / Usulan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $letters = range('a', 'z');
                    $ulpIndex = 0;
                @endphp
                
                @foreach($ulps as $ulpName => $komitmens)
                    @php
                        // Rowspan is based on the number of komitmens (1 komitmen = 1 LM typically)
                        $rowCount = count($komitmens);
                        if ($rowCount == 0) continue;
                    @endphp
                    
                    @foreach($komitmens as $i => $k)
                    <tr>
                        @if($i == 0)
                            <td class="text-center" rowspan="{{ $rowCount }}">{{ $letters[$ulpIndex] ?? '-' }}.</td>
                            <td rowspan="{{ $rowCount }}">{{ $ulpName }}</td>
                        @endif
                        
                        <td>
                            <strong>{{ $k->masterLm->judul_lm ?? 'LM' }}</strong>
                            @if(!empty($k->komitmen))
                                <br><span style="color: #666; font-size: 10px;">{{ $k->komitmen }}</span>
                            @endif
                        </td>
                        
                        <td>
                            @if(is_array($k->hambatans) && count($k->hambatans) > 0)
                                @foreach($k->hambatans as $h)
                                    @if(!empty($h['hambatan']))
                                        <div class="list-item">
                                            <strong>H:</strong> {{ $h['hambatan'] }}<br>
                                            @if(!empty($h['dukungan']))
                                                <strong>D:</strong> {{ $h['dukungan'] }}
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                        
                        <td>
                            @if(is_array($k->aksi_konkrits) && count($k->aksi_konkrits) > 0)
                                @foreach($k->aksi_konkrits as $a)
                                    @if(!empty($a['aksi']))
                                        <div class="list-item">
                                            <strong>{{ $a['aksi'] }}</strong> 
                                            @if(!empty($a['target'])) (Tgt: {{ $a['target'] }}) @endif<br>
                                            @if(!empty($a['detail_komitmen']))
                                                <span style="font-style: italic;">{{ $a['detail_komitmen'] }}</span>
                                            @endif
                                        </div>
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
            </tbody>
        </table>
    @empty
        <div class="text-center" style="margin-top: 50px;">
            <p>Tidak ada data komitmen yang ditemukan untuk kriteria ini.</p>
        </div>
    @endforelse

</body>
</html>


