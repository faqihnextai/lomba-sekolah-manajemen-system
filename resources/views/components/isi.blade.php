@props(['documents' => collect()])
<section class="space-y-5" x-data="pdfDocumentEditor()">
    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
        <h3 class="font-bold text-gray-900 dark:text-white">Editor Isi Dokumen</h3>
        <p class="text-xs text-gray-500 mt-1">Pilih PDF dari Media Library untuk ditampilkan pada area canvas.</p>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1.5 text-gray-700 dark:text-gray-300">Dokumen PDF dari Media Library</label>
        <select x-model="pdfUrl" @change="loadPdf()" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Pilih dokumen PDF</option>
            @foreach ($documents as $document)
                <option value="{{ $document->getUrl() }}">{{ $document->name ?: $document->file_name }}</option>
            @endforeach
        </select>
        @if($documents->isEmpty())
            <p class="text-xs text-amber-600 dark:text-amber-400 mt-2">Belum ada file PDF yang ditemukan di Media Library.</p>
        @endif
    </div>

    <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 p-3">
        <div class="flex items-center gap-2">
            <button type="button" @click="previousPage()" class="rounded-lg border px-3 py-2 text-sm dark:border-gray-600" aria-label="Halaman sebelumnya">‹</button>
            <span class="text-xs text-gray-600 dark:text-gray-300"><span x-text="pageNum"></span> / <span x-text="pageCount || '-'"></span></span>
            <button type="button" @click="nextPage()" class="rounded-lg border px-3 py-2 text-sm dark:border-gray-600" aria-label="Halaman berikutnya">›</button>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" @click="zoom = Math.max(.5, zoom - .1); renderPage()" class="rounded-lg border px-3 py-2 text-sm dark:border-gray-600">−</button>
            <span class="text-xs text-gray-600 dark:text-gray-300" x-text="Math.round(zoom * 100) + '%'"></span>
            <button type="button" @click="zoom = Math.min(2, zoom + .1); renderPage()" class="rounded-lg border px-3 py-2 text-sm dark:border-gray-600">+</button>
        </div>
    </div>

    <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-950 p-3 min-h-[340px] flex items-center justify-center overflow-auto">
        <div x-show="!pdfUrl" class="text-center py-16">
            <div class="mx-auto w-12 h-12 rounded-xl bg-white dark:bg-gray-800 flex items-center justify-center text-red-500 mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M7 3h7l5 5v13H7a2 2 0 01-2-2V5a2 2 0 012-2zM14 3v5h5M8 14h8M8 17h6"/></svg>
            </div>
            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Canvas PDF</p>
            <p class="text-xs text-gray-500 mt-1">Pilih dokumen untuk memulai pratinjau.</p>
        </div>
        <div x-show="loading" class="text-sm text-gray-500 py-10">Memuat dokumen PDF...</div>
        <canvas x-ref="pdfCanvas" x-show="pdfUrl && !loading" class="max-w-full h-auto shadow-md bg-white"></canvas>
    </div>
    <p x-show="errorMessage" x-text="errorMessage" class="text-xs text-red-600"></p>
    <div class="rounded-lg bg-blue-50 dark:bg-blue-950/30 p-3 text-xs text-blue-800 dark:text-blue-200">
        Canvas ini merupakan pratinjau PDF. Kontrol edit/simpan perubahan belum diaktifkan pada tahap UI/UX.
    </div>
</section>

@once
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
@endonce
<script>
    function pdfDocumentEditor() {
        return {
            pdfUrl: '',
            pdf: null,
            pageNum: 1,
            pageCount: 0,
            zoom: 1,
            loading: false,
            errorMessage: '',
            async loadPdf() {
                this.errorMessage = '';
                this.pdf = null;
                this.pageNum = 1;
                if (!this.pdfUrl) return;
                if (!window.pdfjsLib) {
                    this.errorMessage = 'Library PDF.js gagal dimuat.';
                    return;
                }
                window.pdfjsLib.GlobalWorkerOptions.workerSrc =
                    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                this.loading = true;
                try {
                    this.pdf = await window.pdfjsLib.getDocument(this.pdfUrl).promise;
                    this.pageCount = this.pdf.numPages;
                    await this.renderPage();
                } catch (error) {
                    this.errorMessage = 'PDF tidak dapat dibuka. Periksa URL, akses file, dan konfigurasi CORS.';
                } finally {
                    this.loading = false;
                }
            },
            async renderPage() {
                if (!this.pdf) return;
                const page = await this.pdf.getPage(this.pageNum);
                const viewport = page.getViewport({ scale: this.zoom });
                const canvas = this.$refs.pdfCanvas;
                const context = canvas.getContext('2d');
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                await page.render({ canvasContext: context, viewport }).promise;
            },
            async nextPage() {
                if (this.pdf && this.pageNum < this.pageCount) {
                    this.pageNum++;
                    await this.renderPage();
                }
            },
            async previousPage() {
                if (this.pdf && this.pageNum > 1) {
                    this.pageNum--;
                    await this.renderPage();
                }
            }
        };
    }
</script>
