@if($breakdowns->count() > 0)
<div class="overflow-x-auto border-t {{ $type === 'uid' ? 'border-indigo-100' : ($type === 'up3' ? 'border-emerald-100' : 'border-amber-100') }}">
    <table class="min-w-full text-xs text-left">
        <thead class="{{ $type === 'uid' ? 'text-indigo-900 border-indigo-100 bg-indigo-50/50' : ($type === 'up3' ? 'text-emerald-900 border-emerald-100 bg-emerald-50/50' : 'text-amber-900 border-amber-100 bg-amber-50/50') }} border-b">
            <tr>
                <th class="px-4 py-2 font-medium w-10 text-center">Pilih</th>
                <th class="px-4 py-2 font-medium">Unit</th>
                <th class="px-4 py-2 font-medium">Bidang</th>
                <th class="px-4 py-2 font-medium text-right">Target</th>
                <th class="px-4 py-2 font-medium">Periode</th>
                @php
                    $canAction = false;
                    if ($type === 'uid') {
                        $canAction = !empty($canBreakdownToUid) || (isset($canApproveLm) && $canApproveLm);
                    } elseif ($type === 'up3') {
                        $canAction = !empty($canBreakdownToUp3) || (isset($canApproveLm) && $canApproveLm);
                    } elseif ($type === 'ulp') {
                        $canAction = !empty($canBreakdownToUlp) || (isset($canApproveLm) && $canApproveLm);
                    }
                @endphp
                @if($canAction)
                <th class="px-4 py-2 font-medium text-center">Aksi</th>
                @endif
            </tr>
        </thead>
        @php
            $groupedBreakdowns = collect($breakdowns)->sortBy('periode_start')->groupBy(function($item) {
                if ($item->bulan && $item->tahun) {
                    return strtoupper($item->bulan_indo) . ' ' . $item->tahun;
                }
                return strtoupper(\Carbon\Carbon::parse($item->periode_start)->locale('id')->translatedFormat('F Y'));
            });

            $formatLmValue = function($value, $satuan) {
                if ($value === null || $value === '') return '-';
                if (trim($satuan) === '%') {
                    $formatted = number_format((float)$value, 2, ",", ".");
                    $formatted = rtrim(rtrim($formatted, '0'), ',');
                    return $formatted . ' %';
                }
                return number_format((float)$value, 2, ",", ".") . ' ' . $satuan;
            };
        @endphp
        @foreach($groupedBreakdowns as $month => $items)
        @php
            $hasMonthHighlight = (request('status') === 'draft' && $items->contains('is_approved', false)) || (request('highlight_unit') && $items->contains(function($b) { return request('highlight_unit') == $b->unit_id && !$b->is_approved; }));
            $accentColor = $type === 'uid' ? 'indigo' : ($type === 'up3' ? 'emerald' : 'amber');
            $rowColor = $type === 'uid' ? 'divide-indigo-50 border-indigo-100/50' : ($type === 'up3' ? 'divide-emerald-50 border-emerald-100/50' : 'divide-amber-50 border-amber-100/50');
            // Urutkan item terlebih dahulu agar mudah dilooping dengan index yang benar
            $sortedItems = $items->sortBy(function($b) { 
                $isMonthly = \Carbon\Carbon::parse($b->periode_start)->diffInDays(\Carbon\Carbon::parse($b->periode_end)) >= 20 ? 0 : 1;
                return ($b->unit->name ?? '') . '_' . $isMonthly . '_' . $b->periode_start; 
            })->values();
        @endphp
        <tbody x-data="{ openMonth: {{ $hasMonthHighlight ? 'true' : 'false' }}, page: 1, perPage: 15 }" class="divide-y bg-white border-b {{ $rowColor }}">
            <tr class="bg-slate-50 border-y border-slate-200 cursor-pointer hover:bg-slate-100 transition-colors" @click="openMonth = !openMonth">
                <td colspan="{{ $canAction ? '6' : '5' }}" class="px-4 py-2.5 font-bold text-slate-700 text-xs uppercase tracking-wider">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <input type="checkbox" @click.stop="selectAll({{ $items->pluck('id')->toJson() }})" :checked="[...{{ $items->pluck('id')->toJson() }}].every(id => selectedBreakdowns.includes(String(id)))" class="rounded border-gray-300 text-{{ $accentColor }}-600 focus:ring-{{ $accentColor }}-500 cursor-pointer">
                            <span>Target {{ $month }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            @if(isset($canApproveLm) && $canApproveLm)
                            <button 
                                @click.stop="approveMonth({{ $items->pluck('id')->toJson() }}, '{{ $month }}')"
                                x-show="{{ $items->pluck('id')->toJson() }}.some(id => selectedBreakdowns.includes(String(id)))"
                                x-cloak
                                class="text-emerald-500 hover:text-emerald-700 text-[11px] font-bold flex items-center gap-1 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Setujui (<span x-text="{{ $items->pluck('id')->toJson() }}.filter(id => selectedBreakdowns.includes(String(id))).length"></span>)
                            </button>
                            @endif
                            <button 
                                @click.stop="deleteMonth({{ $items->pluck('id')->toJson() }}, '{{ $month }}')"
                                x-show="{{ $items->pluck('id')->toJson() }}.some(id => selectedBreakdowns.includes(String(id)))"
                                x-cloak
                                class="text-red-500 hover:text-red-700 text-[11px] font-bold flex items-center gap-1 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Hapus (<span x-text="{{ $items->pluck('id')->toJson() }}.filter(id => selectedBreakdowns.includes(String(id))).length"></span>)
                            </button>
                            <svg class="w-4 h-4 transform transition-transform duration-200" :class="{'rotate-180': openMonth}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </td>
            </tr>
            @foreach($sortedItems as $index => $breakdown)
            @php $isRowHighlighted = request('highlight_unit') == $breakdown->unit_id && !$breakdown->is_approved; @endphp
            <tr x-show="openMonth && ({{ $index }} >= (page - 1) * perPage && {{ $index }} < page * perPage)" 
                class="transition-all duration-1000 {{ $isRowHighlighted ? 'bg-yellow-50 outline outline-2 outline-yellow-400 z-10 relative' : '' }}"
                @if($isRowHighlighted) x-init="setTimeout(() => { openMonth = true; page = Math.floor({{ $index }} / perPage) + 1; setTimeout(() => { $el.scrollIntoView({behavior: 'smooth', block: 'center'}); }, 100); }, 500);" @endif
            >
                <td class="px-4 py-2 text-center" @click.stop>
                    <input type="checkbox" value="{{ $breakdown->id }}" x-model="selectedBreakdowns" class="rounded border-gray-300 text-{{ $accentColor }}-600 focus:ring-{{ $accentColor }}-500 cursor-pointer">
                </td>
                <td class="px-4 py-2 font-semibold text-gray-700">
                    {{ $breakdown->unit->name ?? '-' }}
                    @if(!$breakdown->is_approved)
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">Draft</span>
                    @endif
                </td>
                <td class="px-4 py-2 text-gray-600">{{ $breakdown->bidang ?? '-' }}</td>
                <td class="px-4 py-2 text-right font-bold text-gray-800">{{ $formatLmValue($breakdown->angka_target, $lm->satuan->name ?? '') }}</td>
                <td class="px-4 py-2 text-gray-500">
                    @if (\Carbon\Carbon::parse($breakdown->periode_start)->diffInDays(\Carbon\Carbon::parse($breakdown->periode_end)) >= 20)
                        <span class="font-bold text-{{ $accentColor }}-700">Target Total Bulanan</span>
                    @else
                        <span class="font-semibold text-gray-700">{{ $breakdown->minggu_label }}</span>
                        <span class="text-xs text-gray-400 block">{{ \Carbon\Carbon::parse($breakdown->periode_start)->locale('id')->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($breakdown->periode_end)->locale('id')->translatedFormat('d M Y') }}</span>
                    @endif
                </td>
                @if($canAction)
                <td class="px-4 py-2 text-center whitespace-nowrap">
                    <div class="flex justify-center items-center gap-3">
                        @if(!$breakdown->is_approved && isset($canApproveLm) && $canApproveLm)
                        <form action="{{ route('cascading.breakdown.approve', $breakdown->id) }}" method="POST" class="inline m-0">
                            @csrf
                            <button type="submit" class="text-emerald-500 hover:text-emerald-700 font-bold transition-colors text-xs">Setujui</button>
                        </form>
                        @endif
                        <button type="button" @click='openEditModal({{ $breakdown->toJson() }}, "{{ addslashes($lm->judul_lm) }}", "{{ $type }}", "{{ addslashes($lm->satuan->name ?? '') }}")' class="text-blue-500 hover:text-blue-700 font-bold transition-colors text-xs">Edit</button>
                        @if($canEditDelete)
                        <form id="deleteForm-{{ $breakdown->id }}" action="{{ route('cascading.breakdown.destroy', $breakdown->id) }}" method="POST" class="inline m-0">
                            @csrf
                            @method('DELETE')
                            <button type="button" @click="openConfirm([], 'Konfirmasi Hapus Data', 'Apakah Anda yakin ingin menghapus target ini secara permanen?', 'delete', 'deleteForm-{{ $breakdown->id }}')" class="text-red-500 hover:text-red-700 font-bold transition-colors text-xs">Hapus</button>
                        </form>
                        @endif
                    </div>
                </td>
                @endif
            </tr>
            @endforeach
            <!-- Pagination Controls (Per Month) -->
            <tr x-show="openMonth && {{ $sortedItems->count() }} > perPage" class="bg-gray-50/30">
                <td colspan="{{ $canAction ? '6' : '5' }}" class="px-4 py-2 text-center">
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Showing <span x-text="(page - 1) * perPage + 1"></span> to <span x-text="Math.min(page * perPage, {{ $sortedItems->count() }})"></span> of {{ $sortedItems->count() }} results</span>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="if(page > 1) page--" class="px-2 py-1 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-50 disabled:opacity-50 transition-colors text-slate-700" :disabled="page == 1">&laquo; Prev</button>
                            <span class="font-medium text-slate-700">Page <span x-text="page"></span> of <span x-text="Math.ceil({{ $sortedItems->count() }} / perPage)"></span></span>
                            <button type="button" @click="if(page < Math.ceil({{ $sortedItems->count() }} / perPage)) page++" class="px-2 py-1 bg-white border border-gray-300 rounded shadow-sm hover:bg-gray-50 disabled:opacity-50 transition-colors text-slate-700" :disabled="page == Math.ceil({{ $sortedItems->count() }} / perPage)">Next &raquo;</button>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
        @endforeach
    </table>
</div>

@else
<div class="px-4 py-3 text-xs text-gray-500 italic bg-white border-t border-gray-100">Belum ada target {{ strtoupper($type) }}</div>
@endif
