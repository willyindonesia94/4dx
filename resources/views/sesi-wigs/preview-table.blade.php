<div class="bg-white rounded-lg border border-slate-200 mt-4 overflow-hidden">
    <!-- Chart Section -->
    <div class="p-4 border-b border-slate-200">
        <h4 class="text-sm font-bold text-slate-800 mb-4">Grafik Realisasi WIG: {{ $wig->judul ?? '' }}</h4>
        <div class="relative h-[300px] w-full">
            <canvas id="previewRealisasiChart"></canvas>
        </div>
    </div>

    <!-- Table Section -->
    <div class="p-4 bg-slate-50 overflow-x-auto">
        <h4 class="text-sm font-bold text-slate-800 mb-3">Rekapitulasi Capaian LM</h4>
        <table class="min-w-full divide-y divide-slate-200 border bg-white">
            <thead class="bg-slate-100">
                <tr>
                    <th class="px-4 py-2 text-left text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Lead Measure</th>
                    <th class="px-4 py-2 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Target</th>
                    <th class="px-4 py-2 text-right text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Realisasi (Σ)</th>
                    <th class="px-4 py-2 text-center text-[11px] font-semibold text-slate-500 uppercase tracking-wider">% Capaian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($tableData as $row)
                <tr>
                    <td class="px-4 py-2 text-xs text-slate-700 max-w-[200px] truncate" title="{{ $row['lm'] }}">{{ $row['lm'] }}</td>
                    <td class="px-4 py-2 text-xs font-semibold text-slate-900 text-right">{{ number_format($row['target'], 2) }} {{ $row['satuan'] }}</td>
                    <td class="px-4 py-2 text-xs font-bold text-indigo-600 text-right">{{ number_format($row['realisasi'], 2) }} {{ $row['satuan'] }}</td>
                    <td class="px-4 py-2 text-xs font-medium text-center">
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ $row['capaian'] >= 100 ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $row['capaian'] }}%
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-2 text-center text-xs text-slate-500">Tidak ada data realisasi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    (function() {
        // Destroy existing chart if it exists
        if (window.previewChartInstance) {
            window.previewChartInstance.destroy();
        }
        
        const ctx = document.getElementById('previewRealisasiChart');
        if (ctx) {
            const chartData = @json($chartData);
            window.previewChartInstance = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: chartData.datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { font: { size: 10 } } },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }
    })();
</script>
