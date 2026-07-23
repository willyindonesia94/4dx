<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Distribusi Target (Matriks)') }}
            </h2>
            @hasrole('superadmin|admin_uid')
            <a href="{{ route('targets.create', ['type' => 'master_wig']) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                + Buat Master WIG
            </a>
            @endhasrole
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @forelse($masterWigs as $wig)
                <div class="bg-white rounded-md shadow-sm border border-gray-200 overflow-hidden mb-6">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Master WIG &bull; Divisi {{ $wig->division->name ?? 'UID' }} &bull; Periode {{ $wig->period }}</div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $wig->name }}</h3>
                            <div class="text-sm text-gray-600 mt-1">Target UID: <span class="font-bold text-gray-900">{{ $wig->target_value }} {{ $wig->metric->unit ?? '' }}</span></div>
                        </div>
                        @hasrole('superadmin|admin_uid')
                        <div>
                            <a href="{{ route('targets.create', ['type' => 'master_lm', 'master_wig_id' => $wig->id]) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">+ Tambah Master LM</a>
                        </div>
                        @endhasrole
                    </div>

                    <div class="p-6">
                        @if($wig->masterLms->count() > 0)
                            <div class="space-y-6">
                                @foreach($wig->masterLms as $lm)
                                    <div class="border border-blue-100 bg-blue-50/30 rounded-lg p-4">
                                        <div class="flex justify-between items-center mb-4">
                                            <div>
                                                <div class="text-[10px] font-bold text-blue-500 uppercase tracking-wider mb-1">Master Lead Measure</div>
                                                <h4 class="font-bold text-blue-900">{{ $lm->name }}</h4>
                                                <p class="text-xs text-gray-600 mt-1">Satuan: {{ $lm->metric->unit ?? '' }}</p>
                                            </div>
                                            @hasrole('superadmin|admin_uid|admin_up3')
                                            <div>
                                                <a href="{{ route('targets.create', ['type' => 'target', 'master_lm_id' => $lm->id]) }}" class="text-xs bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded hover:bg-gray-50 transition-colors">
                                                    + Alokasi Unit
                                                </a>
                                            </div>
                                            @endhasrole
                                        </div>

                                        @if($lm->targets->count() > 0)
                                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit / Lokasi</th>
                                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Alokasi Target</th>
                                                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        @foreach($lm->targets as $target)
                                                            <tr class="hover:bg-gray-50">
                                                                <td class="px-4 py-3 whitespace-nowrap">
                                                                    <div class="font-medium text-gray-900">{{ $target->location->name ?? 'N/A' }}</div>
                                                                    <div class="text-xs text-gray-500">{{ $target->location->type ?? '' }}</div>
                                                                </td>
                                                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                                                    <div class="font-bold text-gray-900">{{ number_format($target->target_value, 2) }}</div>
                                                                </td>
                                                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                                                    <span class="px-2.5 py-1 inline-flex text-[11px] leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                                        {{ $target->status ?? 'Belum Mulai' }}
                                                                    </span>
                                                                </td>
                                                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                                    <a href="{{ route('targets.edit', $target->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                                    <form action="{{ route('targets.destroy', $target->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus alokasi ini?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4 bg-white border border-gray-200 rounded-lg text-sm text-gray-500">
                                                Belum ada alokasi target ke unit.
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                Belum ada Master Lead Measure untuk WIG ini.
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-md shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada Master WIG</h3>
                    <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat target utama korporat (WIG UID).</p>
                    @hasrole('superadmin|admin_uid')
                    <div class="mt-6">
                        <a href="{{ route('targets.create', ['type' => 'master_wig']) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Buat Master WIG
                        </a>
                    </div>
                    @endhasrole
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
