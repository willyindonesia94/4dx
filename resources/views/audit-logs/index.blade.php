<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Audit Log') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex justify-between items-center mb-4">
                        <form action="{{ route('audit-logs.index') }}" method="GET" class="flex items-center">
                            <x-text-input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aktivitas..." class="mr-2" />
                            <x-primary-button type="submit">Cari</x-primary-button>
                            @if(request('search'))
                                <a href="{{ route('audit-logs.index') }}" class="ml-2 text-gray-500 hover:text-gray-700">Reset</a>
                            @endif
                        </form>
                    </div>

                    <div class="overflow-x-auto relative">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Waktu</th>
                                    <th scope="col" class="py-3 px-6">Pengguna</th>
                                    <th scope="col" class="py-3 px-6">Event</th>
                                    <th scope="col" class="py-3 px-6">Modul / Model</th>
                                    <th scope="col" class="py-3 px-6">Deskripsi</th>
                                    <th scope="col" class="py-3 px-6">Properti (Perubahan)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr class="bg-white border-b">
                                        <td class="py-4 px-6 whitespace-nowrap">
                                            {{ $log->created_at->format('d M Y H:i:s') }}
                                        </td>
                                        <td class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap">
                                            {{ optional($log->causer)->name ?? 'System / Anonymous' }}
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="px-2 py-1 rounded text-xs font-semibold
                                                {{ $log->event == 'created' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $log->event == 'updated' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $log->event == 'deleted' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $log->event == 'login' ? 'bg-purple-100 text-purple-800' : '' }}">
                                                {{ ucfirst($log->event) }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            {{ class_basename($log->subject_type) ?: '-' }}
                                        </td>
                                        <td class="py-4 px-6">
                                            {{ $log->description }}
                                        </td>
                                        <td class="py-4 px-6">
                                            @if($log->properties && count($log->properties) > 0)
                                                <div x-data="{ open: false }">
                                                    <button @click="open = !open" class="text-blue-600 hover:underline text-xs">Lihat Detail</button>
                                                    <div x-show="open" class="mt-2 text-xs bg-gray-100 p-2 rounded max-h-32 overflow-y-auto" style="display: none;">
                                                        @if(isset($log->properties['old']))
                                                            <strong>Old:</strong> <br>
                                                            <pre>{{ json_encode($log->properties['old'], JSON_PRETTY_PRINT) }}</pre>
                                                        @endif
                                                        @if(isset($log->properties['attributes']))
                                                            <strong>New/Attributes:</strong> <br>
                                                            <pre>{{ json_encode($log->properties['attributes'], JSON_PRETTY_PRINT) }}</pre>
                                                        @endif
                                                        @if(!isset($log->properties['old']) && !isset($log->properties['attributes']))
                                                            <pre>{{ json_encode($log->properties, JSON_PRETTY_PRINT) }}</pre>
                                                        @endif
                                                    </div>
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 px-6 text-center">Belum ada data aktivitas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
