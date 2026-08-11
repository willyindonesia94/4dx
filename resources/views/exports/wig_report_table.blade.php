<table style="border: 1px solid #000000;">
    <thead>
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 14px; text-align: center;">LAPORAN CAPAIAN WIG BULAN {{ strtoupper($bulan) }} TAHUN {{ $tahun }}</th>
        </tr>
        <tr>
            <th colspan="7"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #d9edf7; text-align: center; border: 1px solid #000000;">WIG</th>
            <th style="font-weight: bold; background-color: #d9edf7; text-align: center; border: 1px solid #000000;">SATUAN</th>
            <th style="font-weight: bold; background-color: #d9edf7; text-align: center; border: 1px solid #000000;">POLARITAS</th>
            <th style="font-weight: bold; background-color: #d9edf7; text-align: center; border: 1px solid #000000;">UNIT</th>
            <th style="font-weight: bold; background-color: #d9edf7; text-align: center; border: 1px solid #000000;">TARGET</th>
            <th style="font-weight: bold; background-color: #d9edf7; text-align: center; border: 1px solid #000000;">REALISASI</th>
            <th style="font-weight: bold; background-color: #d9edf7; text-align: center; border: 1px solid #000000;">CAPAIAN (%)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($wigs as $wig)
            @php
                $wData = $reportData[$wig->id];
            @endphp
            @if(!$isUlpLevel && !$isUp3Level)
            <tr>
                <td style="border: 1px solid #000000; font-weight: bold;">{{ $wig->judul }}</td>
                <td style="border: 1px solid #000000; font-weight: bold;">{{ $wig->satuan->name ?? '' }}</td>
                <td style="border: 1px solid #000000; font-weight: bold;">{{ ucfirst($wig->polaritas ?? 'positif') }}</td>
                <td style="border: 1px solid #000000; font-weight: bold; background-color: #fcf8e3;">UID Jawa Barat</td>
                <td style="border: 1px solid #000000; font-weight: bold; background-color: #fcf8e3;" data-format="0.00">{{ $wData['target'] }}</td>
                <td style="border: 1px solid #000000; font-weight: bold; background-color: #fcf8e3;" data-format="0.00">{{ $wData['realisasi'] }}</td>
                <td style="border: 1px solid #000000; font-weight: bold; background-color: #fcf8e3;" data-format="0.00">{{ round($wData['pct'], 2) }}</td>
            </tr>
            @endif

            @foreach($units as $unit)
                @if(isset($wData['units'][$unit->id]))
                    @php
                        $uData = $wData['units'][$unit->id];
                    @endphp
                    <tr>
                        <td style="border: 1px solid #000000;">{{ $wig->judul }}</td>
                        <td style="border: 1px solid #000000;">{{ $wig->satuan->name ?? '' }}</td>
                        <td style="border: 1px solid #000000;">{{ ucfirst($wig->polaritas ?? 'positif') }}</td>
                        <td style="border: 1px solid #000000;">{{ $unit->name }}</td>
                        <td style="border: 1px solid #000000;" data-format="0.00">{{ $uData['t'] }}</td>
                        <td style="border: 1px solid #000000;" data-format="0.00">{{ $uData['r'] }}</td>
                        <td style="border: 1px solid #000000;" data-format="0.00">{{ round($uData['pct'], 2) }}</td>
                    </tr>
                @endif
            @endforeach
        @endforeach
    </tbody>
</table>
