@props(['judul', 'deskripsi', 'warna' => 'emerald', 'babList' => []])

<div x-data="{ 
        activeBab: 0, 
        modalUpload: false, 
        modalReview: false,
        selectedItem: '',
        currentUserRole: @js(auth()->user()->hasRole('superadmin') ? 'superadmin' : (auth()->user()->hasRole('admin') ? 'koordinator' : 'guru'))
    }" class="py-8">
    
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Banner Header & Export -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 lg:p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-6 relative overflow-hidden">
            <div class="absolute right-0 top-0 w-32 h-32 bg-{{ $warna }}-500/10 rounded-bl-[100px] -z-0"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full bg-{{ $warna }}-100 text-{{ $warna }}-700 border border-{{ $warna }}-200">Pengerjaan Dokumen</span>
                    <span class="text-xs font-semibold text-gray-500">Tahun Ajaran 2026/2027</span>
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white">{{ $judul }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-2xl">{{ $deskripsi }}</p>
            </div>
            
            <!-- Aksi Global (Admin / Kepsek) -->
            <div class="flex flex-col sm:flex-row items-center gap-3 relative z-10 shrink-0">
                <button type="button" class="w-full sm:w-auto px-5 py-2.5 text-sm font-semibold rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 transition flex items-center justify-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Unduh Seluruh Berkas (ZIP)
                </button>
                <button x-show="currentUserRole === 'superadmin'" type="button" class="w-full sm:w-auto px-5 py-2.5 text-sm font-bold rounded-xl bg-gray-900 dark:bg-blue-600 text-white hover:bg-gray-800 dark:hover:bg-blue-700 transition flex items-center justify-center gap-2 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Final Lock Dokumen
                </button>
            </div>
        </div>

        <!-- Layout Navigasi Bab & Daftar Dokumen -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
            
            <!-- KOLOM KIRI: Daftar Bab -->
            <div class="lg:col-span-4 space-y-3">
                @foreach ($babList as $index => $bab)
                <button type="button"
                        @click="activeBab = {{ $index }}"
                        :class="activeBab === {{ $index }} ? 'ring-2 ring-{{ $warna }}-500 bg-white dark:bg-gray-800 shadow-md' : 'bg-white dark:bg-gray-800 hover:bg-gray-50 border border-gray-100 dark:border-gray-700 opacity-80 hover:opacity-100'"
                        class="w-full text-left p-5 rounded-2xl transition-all duration-200 flex items-start gap-4">
                    
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5"
                         :class="activeBab === {{ $index }} ? 'bg-{{ $warna }}-100 text-{{ $warna }}-600' : 'bg-gray-100 text-gray-500 dark:bg-gray-700'">
                        <span class="font-black text-lg">{{ $index + 1 }}</span>
                    </div>
                    
                    <div class="flex-1">
                        <div class="font-bold text-sm text-gray-900 dark:text-white leading-snug mb-1"
                             :class="activeBab === {{ $index }} ? 'text-{{ $warna }}-700' : ''">
                            {{ $bab['judul'] }}
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-500">{{ count($bab['items']) }} Item Dokumen</span>
                            <!-- Progress Bar Simulasi -->
                            <div class="flex-1 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-{{ $warna }}-500 rounded-full" style="width: 40%"></div>
                            </div>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>

            <!-- KOLOM KANAN: Daftar Sub-Dokumen -->
            <div class="lg:col-span-8">
                @foreach ($babList as $index => $bab)
                <div x-show="activeBab === {{ $index }}" x-transition.opacity.duration.300ms class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden" style="display: none;">
                    
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        <span class="text-xs font-black uppercase tracking-wider text-{{ $warna }}-600">Bagian {{ $index + 1 }}</span>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $bab['judul'] }}</h4>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($bab['items'] as $itemIdx => $item)
                        <!-- Item List Dokumen -->
                        <div class="p-6 hover:bg-gray-50/40 dark:hover:bg-gray-700/10 transition-colors">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-5">
                                
                                <!-- Info Dokumen -->
                                <div class="flex-1 space-y-2">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="text-sm font-bold text-gray-400 dark:text-gray-500">#{{ $index + 1 }}.{{ $itemIdx + 1 }}</span>
                                        
                                        <!-- Simulasi Status Berkas -->
                                        @if($itemIdx === 0)
                                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md bg-emerald-100 text-emerald-700 border border-emerald-200">Approved</span>
                                        @elseif($itemIdx === 1)
                                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md bg-amber-100 text-amber-700 border border-amber-200">Menunggu Review</span>
                                        @else
                                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md bg-gray-100 text-gray-600 border border-gray-200">Draft / Kosong</span>
                                        @endif
                                    </div>
                                    
                                    <p class="font-bold text-gray-900 dark:text-white text-base leading-snug">{{ $item }}</p>
                                    <p class="text-xs text-gray-500 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                        Wajib unggah softcopy (PDF) atau foto fisik
                                    </p>

                                    <!-- Tampilan Berkas yang Sudah Diunggah (Simulasi jika itemIdx == 0 atau 1) -->
                                    @if($itemIdx === 0 || $itemIdx === 1)
                                    <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded bg-red-100 text-red-600 flex items-center justify-center">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path></svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-gray-900 dark:text-white">Dokumen_Bukti_{{ $index + 1 }}_{{ $itemIdx + 1 }}.pdf</p>
                                                <p class="text-[10px] text-gray-500">Diunggah oleh: Guru Pelaksana • 2 Nov 2026</p>
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <button class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition" title="Lihat/Unduh Dokumen">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Aksi (Guru & Admin) -->
                                <div class="flex flex-col gap-2 shrink-0 sm:min-w-[140px]">
                                    
                                    <!-- Aksi Guru: Tombol Unggah -->
                                    <button x-show="currentUserRole === 'guru' || currentUserRole === 'anggota'" 
                                            @click="selectedItem = '{{ addslashes($item) }}'; modalUpload = true" 
                                            class="w-full px-4 py-2 text-xs font-bold bg-white border-2 border-gray-200 hover:border-{{ $warna }}-500 text-gray-700 hover:text-{{ $warna }}-700 rounded-xl transition flex items-center justify-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        Upload
                                    </button>

                                    <!-- Aksi Admin/Koordinator: Tombol Verifikasi -->
                                    <button x-show="currentUserRole === 'koordinator' || currentUserRole === 'superadmin'" 
                                            @click="selectedItem = '{{ addslashes($item) }}'; modalReview = true" 
                                            class="w-full px-4 py-2 text-xs font-bold bg-gray-900 dark:bg-gray-700 hover:bg-blue-600 text-white rounded-xl transition flex items-center justify-center gap-1.5 shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Verifikasi
                                    </button>

                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- MODAL 1: FORM UPLOAD (GURU/USER) -->
    <div x-show="modalUpload" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4" style="display: none;">
        <div @click.away="modalUpload = false" x-show="modalUpload" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
                <h4 class="font-black text-gray-900 dark:text-white">Unggah Bukti Dokumen</h4>
                <button @click="modalUpload = false" class="text-gray-400 hover:text-red-500 bg-white dark:bg-gray-700 rounded-full p-1 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form class="p-6 space-y-5" onsubmit="event.preventDefault(); alert('Berkas berhasil diunggah dengan status Drafting!'); modalUpload = false;">
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
                    <p class="text-[11px] font-bold text-blue-800 dark:text-blue-300 uppercase mb-1">Target Dokumen:</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight" x-text="selectedItem"></p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">File Bukti (PDF / JPG / PNG)</label>
                        <input type="file" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 border border-gray-200 dark:border-gray-700 rounded-xl">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Kegiatan</label>
                            <input type="date" class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Penanggung Jawab</label>
                            <input type="text" placeholder="Nama Guru / Tim" class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Nama Aksi / Kegiatan</label>
                        <input type="text" placeholder="Contoh: Pelaksanaan Jumat Bersih" class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Teks Laporan / Deskripsi Aksi</label>
                        <textarea rows="3" placeholder="Tulis narasi atau ringkasan hasil laporan..." class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition">
                        Simpan & Ajukan Review
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: FORM REVIEW / VERIFIKASI (ADMIN/KOORDINATOR) -->
    <div x-show="modalReview" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4" style="display: none;">
        <div @click.away="modalReview = false" x-show="modalReview" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-white dark:bg-gray-800 rounded-3xl max-w-lg w-full shadow-2xl overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 bg-gray-900 text-white flex justify-between items-center">
                <h4 class="font-black">Verifikasi & Penilaian Dokumen</h4>
                <button @click="modalReview = false" class="text-gray-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form class="p-6 space-y-6" onsubmit="event.preventDefault(); alert('Status dokumen berhasil diperbarui!'); modalReview = false;">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Dokumen yang Direview:</p>
                    <p class="text-sm font-bold text-gray-900 dark:text-white" x-text="selectedItem"></p>
                </div>

                <!-- Aksi Lihat Berkas -->
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Dokumen_Bukti.pdf</p>
                            <p class="text-xs text-gray-500">Diunggah: 2 Nov 2026</p>
                        </div>
                    </div>
                    <button type="button" class="text-sm font-bold text-blue-600 hover:underline">Unduh Berkas</button>
                </div>

                <!-- Update Status & Catatan -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Ubah Status Validasi</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="status" class="peer sr-only" value="revisi">
                                <div class="text-center px-2 py-3 rounded-xl border-2 border-gray-200 text-gray-500 font-bold text-xs peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 transition">
                                    Tolak / Revisi
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="status" class="peer sr-only" value="review" checked>
                                <div class="text-center px-2 py-3 rounded-xl border-2 border-gray-200 text-gray-500 font-bold text-xs peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700 transition">
                                    Tahap Review
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="status" class="peer sr-only" value="approve">
                                <div class="text-center px-2 py-3 rounded-xl border-2 border-gray-200 text-gray-500 font-bold text-xs peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 transition">
                                    Approve (Valid)
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">Catatan Revisi / Evaluasi (Opsional)</label>
                        <textarea rows="3" placeholder="Berikan instruksi jika dokumen perlu diperbaiki..." class="w-full text-sm rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <div class="pt-2 flex justify-end gap-3">
                    <button type="button" @click="modalReview = false" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-gray-900 hover:bg-black rounded-xl transition shadow-md">Simpan Keputusan</button>
                </div>
            </form>
        </div>
    </div>

</div>