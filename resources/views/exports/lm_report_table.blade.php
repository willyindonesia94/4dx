<table>
    <tr>
        <td colspan="14" style="font-size: 14px; font-weight: bold;">DATA LM - {{ strtoupper($bulan) }} {{ $tahun }}</td>
    </tr>
    <tr>
        <td colspan="14"></td>
    </tr>
</table>
<table border="1" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th rowspan="2" style="background-color: #1F497D; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">NO</th>
            <th rowspan="2" style="background-color: #1F497D; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">WIG</th>
            <th rowspan="2" style="background-color: #1F497D; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">INDIKATOR KINERJA (LM)</th>
            <th rowspan="2" style="background-color: #1F497D; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">POLARITAS</th>
            <th rowspan="2" style="background-color: #1F497D; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">SATUAN</th>
            <th rowspan="2" style="background-color: #1F497D; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">UNIT</th>
            <th rowspan="2" style="background-color: #E26B0A; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">TARGET BULAN INI</th>
            <th colspan="5" style="background-color: #385D22; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">REALISASI MINGGUAN</th>
            <th rowspan="2" style="background-color: #595959; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">TOTAL REALISASI</th>
            <th rowspan="2" style="background-color: #595959; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">% CAPAIAN</th>
        </tr>
        <tr>
            <th style="background-color: #385D22; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">M1</th>
            <th style="background-color: #385D22; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">M2</th>
            <th style="background-color: #385D22; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">M3</th>
            <th style="background-color: #385D22; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">M4</th>
            <th style="background-color: #385D22; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center; vertical-align: middle;">M5</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @forelse($data as $row)
            @php
                $isUid = isset($row['is_uid']) && $row['is_uid'];
                $bgRow = $isUid ? 'background-color: #FFC000; font-weight: bold;' : '';
            @endphp
            <tr style="{{ $bgRow }}">
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; {{ $bgRow }}">{{ $no++ }}</td>
                <td style="border: 1px solid #000000; text-align: left; vertical-align: middle; {{ $bgRow }}">{{ $row['wig'] }}</td>
                <td style="border: 1px solid #000000; text-align: left; vertical-align: middle; {{ $bgRow }}">{{ $row['lm'] }}</td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; {{ $bgRow }}">{{ strtoupper($row['polaritas'] ?? 'POSITIF') }}</td>
                <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; {{ $bgRow }}">{{ $row['satuan'] }}</td>
                <td style="border: 1px solid #000000; text-align: left; vertical-align: middle; {{ $bgRow }}">{{ $row['unit'] }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle; {{ $bgRow }}">{{ is_numeric($row['target']) ? number_format($row['target'], 2) : $row['target'] }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle; {{ $bgRow }}">{{ is_numeric($row['r1']) ? number_format($row['r1'], 2) : $row['r1'] }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle; {{ $bgRow }}">{{ is_numeric($row['r2']) ? number_format($row['r2'], 2) : $row['r2'] }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle; {{ $bgRow }}">{{ is_numeric($row['r3']) ? number_format($row['r3'], 2) : $row['r3'] }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle; {{ $bgRow }}">{{ is_numeric($row['r4']) ? number_format($row['r4'], 2) : $row['r4'] }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle; {{ $bgRow }}">{{ is_numeric($row['r5']) ? number_format($row['r5'], 2) : $row['r5'] }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold; background-color: #F2F2F2; {{ $bgRow }}">{{ is_numeric($row['total']) ? number_format($row['total'], 2) : $row['total'] }}</td>
                <td style="border: 1px solid #000000; text-align: right; vertical-align: middle; font-weight: bold; background-color: #F2F2F2; {{ $bgRow }}">{{ $row['capaian'] }}%</td>
            </tr>
        @empty
            <tr>
                <td colspan="14" style="border: 1px solid #000000; text-align: center; font-style: italic; padding: 12px;">Tidak ada data LM untuk periode dan lingkup Bidang/Unit ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>
