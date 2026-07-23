<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Realisasi Harian') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-t-3xl sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('realisasis.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Pilih Lead Measure</label>
                            <p class="text-xs text-gray-500 mb-2">Hanya menampilkan LM yang ditugaskan ke area/divisi Anda.</p>
                            <select name="lm_id" class="mt-1 block w-full py-3 px-4 border border-gray-300 bg-gray-50 rounded-xl shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option disabled selected>-- Pilih LM --</option>
                                @foreach($lms ?? [] as $lm)
                                    <option value="{{ $lm->id }}">{{ $lm->judul_lm }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Angka Realisasi</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <input type="number" step="0.01" name="angka_realisasi" class="py-3 px-4 focus:ring-blue-500 focus:border-blue-500 block w-full text-lg font-bold sm:text-sm border-gray-300 rounded-xl placeholder-gray-300" placeholder="0.00">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Tanggal Input</label>
                            <input type="datetime-local" name="tanggal_input" value="{{ now()->format('Y-m-d\TH:i') }}" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full py-3 px-4 sm:text-sm border-gray-300 rounded-xl bg-gray-50">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700">Bukti Fisik (Foto/Dokumen)</label>
                            <p class="text-xs text-red-500 mb-2 font-medium">*Wajib dilampirkan untuk verifikasi</p>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:bg-gray-50 transition">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload a file</span>
                                            <input id="file-upload" name="bukti_file" type="file" class="sr-only">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, PDF up to 2MB</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Kirim Realisasi
                            </button>
                            <p class="text-center text-xs text-gray-400 mt-3 flex justify-center items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Data yang dikirim hanya bisa diedit maksimal 1x24 jam
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
