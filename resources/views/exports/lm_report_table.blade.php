<style>
    .report-table-preview { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; width: 100%; border-collapse: collapse; }
    .report-table-preview th, .report-table-preview td { border: 1px solid #000; padding: 6px; text-align: center; }
    .report-table-preview .bg-blue { background-color: #1f497d; color: #ffffff; }
    .report-table-preview .bg-orange { background-color: #e26b0a; color: #ffffff; }
    .report-table-preview .bg-green { background-color: #385d22; color: #ffffff; }
    .report-table-preview .bg-purple { background-color: #60497a; color: #ffffff; }
    .report-table-preview .bg-gray { background-color: #bfbfbf; }
    .report-table-preview .bg-light-purple { background-color: #e4dfec; }
    .report-table-preview .bg-uid-total { background-color: #ffc000; color: #000000; font-weight: bold; }
    .report-table-preview .text-left { text-align: left; }
    .report-table-preview .text-right { text-align: right; }
    .report-table-preview .font-bold { font-weight: bold; }
</style>

<h2>DATA LM - {{ strtoupper($bulan) }} {{ $tahun }}</h2>
<table class="report-table-preview">
    <thead>
        <tr>
            <th class="bg-blue" rowspan="2">NO</th>
            <th class="bg-blue" rowspan="2">WIG</th>
            <th class="bg-blue" rowspan="2">INDIKATOR KINERJA (LM)</th>
            <th class="bg-blue" rowspan="2">POLARITAS</th>
            <th class="bg-blue" rowspan="2">SATUAN</th>
            <th class="bg-blue" rowspan="2">UNIT</th>
            <th class="bg-orange" rowspan="2">TARGET BULAN INI</th>
            <th class="bg-green" colspan="5">REALISASI MINGGUAN</th>
            <th class="bg-gray font-bold" rowspan="2">TOTAL REALISASI</th>
            <th class="bg-gray font-bold" rowspan="2">% CAPAIAN</th>
        </tr>
        <tr>
            <th class="bg-green">M1</th>
            <th class="bg-green">M2</th>
            <th class="bg-green">M3</th>
            <th class="bg-green">M4</th>
            <th class="bg-green">M5</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @foreach($data as $row)
            @php
                $isUid = isset($row['is_uid']) ? $row['is_uid'] : false;
            @endphp
            <tr class="{{ $isUid ? 'bg-uid-total' : '' }}">
                <td>{{ $no++ }}</td>
                <td class="text-left">{{ $row['wig'] }}</td>
                <td class="text-left">{{ $row['lm'] }}</td>
                <td>{{ $row['polaritas'] }}</td>
                <td>{{ $row['satuan'] }}</td>
                <td class="text-left">{{ $row['unit'] }}</td>
                <td>{{ $row['target'] }}</td>
                <td>{{ $row['r1'] }}</td>
                <td>{{ $row['r2'] }}</td>
                <td>{{ $row['r3'] }}</td>
                <td>{{ $row['r4'] }}</td>
                <td>{{ $row['r5'] }}</td>
                <td class="bg-gray font-bold">{{ $row['total'] }}</td>
                <td class="bg-gray font-bold">{{ $row['capaian'] }}%</td>
            </tr>
        @endforeach
        @if(count($data) === 0)
            <tr>
                <td colspan="14">Tidak ada data LM untuk periode ini.</td>
            </tr>
        @endif
    </tbody>
</table>
