<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>تبدیل فونت به tcpdf</title>
        <link rel="shortcut icon" href="{{ url('favicon.png') }}" type="image/x-icon">
        @vite('resources/css/app.css')
        <style>
            @keyframes indeterminate {
                0% { transform: translateX(-100%); }
                50% { transform: translateX(0%); }
                100% { transform: translateX(100%); }
            }
            #progress-bar.indeterminate {
                width: 40% !important;
                animation: indeterminate 1.5s ease-in-out infinite !important;
                transition: none !important;
            }
        </style>
    </head>
    <body class="bg-zinc-100 min-h-screen flex flex-col items-center gap-10 p-6 font-vazirmatn">
        <header class="mb-6 p-6 flex items-center w-full justify-between rounded-2xl ring-2 ring-zinc-800/15 bg-white">
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-800">تبدیل فونت به tcpdf</h1>
            <a target="_blank" href="https://github.com/chaveamin/tcpdf-font">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_4482_11501)">
                <path d="M7 21.99H8.67V20.84C8.67 20.84 8.65 19.04 9.64 17.52C7.38 17.36 5.59 15.35 5.59 12.86C5.59 11.99 5.92 11.17 6.49 10.47L6.55 8.32C6.55 7.77 7.11 7.4 7.62 7.59L9.93 8.42C10.58 8.26 11.28 8.17 12 8.17C12.72 8.17 13.42 8.26 14.07 8.43L16.38 7.6C16.89 7.41 17.44 7.79 17.45 8.33L17.51 10.48C18.08 11.18 18.41 11.99 18.41 12.87C18.41 15.36 16.62 17.37 14.36 17.53C15.35 19.05 15.33 20.85 15.33 20.85V22H17C19.76 22 22 19.76 22 17.01V7C22 4.24 19.76 2 17 2H7C4.24 2 2 4.24 2 7V17C2 19.76 4.24 22 7 22" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </g>
                <defs>
                <clipPath id="clip0_4482_11501">
                <rect width="24" height="24" fill="black"/>
                </clipPath>
                </defs>
                </svg>
            </a>
        </header>
        <p class="text-zinc-500 sm:text-base text-sm">برای دریافت فایل‌های سازگار با TCPDF فونت خود را با فرمت ttf آپلود کنید.</p>
        <main class="max-w-3xl w-full bg-white rounded-3xl ring-2 ring-zinc-900/10 p-8">
            <div id="error-container" class="hidden mb-4 p-4 text-xs sm:text-sm text-red-700 bg-red-500/15 rounded-lg"></div>

            {{-- Tabs --}}
            <div class="flex gap-2 mb-6 border-b border-zinc-200 *:px-4 *:py-2.5 *:text-sm *:border-b-2 *:-mb-px *:transition-colors *:cursor-pointer">
                <button type="button" id="tab-upload" class="tab-btn font-semibold border-zinc-900 text-zinc-900">
                    آپلود فایل
                </button>
                <button type="button" id="tab-google" class="tab-btn font-medium border-transparent text-zinc-400 hover:text-zinc-600">
                    گوگل فونت
                </button>
            </div>

            {{-- Upload Tab --}}
            <div class="flex flex-col gap-y-6" id="panel-upload">
                <form id="converter-form" action="{{ route('converter.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="flex items-center justify-center w-full">
                        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-48 border-2 border-zinc-300 border-dashed rounded-xl cursor-pointer bg-zinc-100 hover:bg-zinc-200/75 transition-colors">
                            <div class="flex flex-col items-center gap-y-4 justify-center p-4 text-center">
                                <svg class="size-10" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g class="*:stroke-zinc-500" clip-path="url(#clip0_4482_3996)">
                                    <path d="M13.58 19.6199H16.52C19.26 19.6199 21.49 17.4 21.49 14.66C21.49 12.46 20.09 10.62 18.12 9.95996" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round"/>
                                    <path d="M13.38 10.8103C14.24 10.1103 15.33 9.69028 16.53 9.69028C17.09 9.69028 17.64 9.78028 18.13 9.96028C17.34 6.42028 14.18 3.78027 10.41 3.78027C7.59003 3.78027 5.11996 5.25028 3.70996 7.47028" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round"/>
                                    <path d="M5.65002 20.2098L5.67004 13.7598L8.82996 16.8098L5.67004 13.7598L2.5 16.8098" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
                                    </g>
                                    <defs>
                                    <clipPath id="clip0_4482_3996">
                                    <rect width="24" height="24" fill="white"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                                <p class="sm:text-base text-sm text-zinc-500" id="file-name-display">برای آپلود کلیک کنید یا فایل‌های فونت را بکشید و رها کنید</p>
                                <div class="flex gap-3 *:rounded-md *:px-2 *:py-1 *:bg-white *:ring *:ring-zinc-200">
                                    <p class="text-xs">ttf.</p>
                                    <p class="text-xs">حداکثر 10 مگابایت</p>
                                    <p class="text-xs">انتخاب چندین فونت</p>
                                </div>
                            </div>
                            <input id="dropzone-file" type="file" name="font[]" class="hidden" accept=".ttf" multiple required />
                        </label>
                    </div>

                    <div id="file-list" class="hidden space-y-2"></div>

                    <div id="progress-container" class="hidden space-y-2">
                        <div class="w-full h-2 bg-zinc-200 rounded-full overflow-hidden">
                            <div id="progress-bar" class="h-full bg-zinc-900 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
                        </div>
                        <p id="progress-status" class="text-xs text-zinc-500 text-center"></p>
                    </div>

                    <button type="submit" id="submit-btn" class="w-full flex items-center justify-center gap-x-2 cursor-pointer sm:text-base text-sm text-white ring bg-zinc-900 hover:bg-zinc-800 hover:-translate-y-px focus-visible:ring-4 focus-visible:ring-zinc-500/30 focus:ring-4 focus:ring-zinc-500/30 font-medium sm:font-bold rounded-lg sm:rounded-xl py-2 sm:py-3 text-center transition-all">
                        <span id="btn-text">تبدیل و دانلود فونت‌ها</span>
                    </button>
                </form>

                <div id="preview-section" class="hidden flex flex-col gap-y-12">
                    <div class="space-y-3">
                        <div id="font-info-box" class="w-full p-4 border border-zinc-200 rounded-xl bg-zinc-50 space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-zinc-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <g class="*:stroke-2 *:stroke-zinc-400" clip-path="url(#clip0_4418_9133)">
                                        <path d="M2.67004 7.17027V5.35027C2.67004 4.20027 3.60004 3.28027 4.74004 3.28027H19.26C20.41 3.28027 21.33 4.21027 21.33 5.35027V7.17027" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M12 20.7204V4.11035" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M8.06006 20.7197H15.9401" stroke-linecap="round" stroke-linejoin="round" />
                                        </g>
                                        <defs>
                                        <clipPath id="clip0_4418_9133">
                                        <rect width="24" height="24" fill="white"/>
                                        </clipPath>
                                        </defs>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p id="font-info-name" class="text-sm font-semibold text-zinc-700 truncate">-</p>
                                    <p id="font-info-size" class="text-xs text-zinc-400">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs sm:text-sm text-zinc-500 font-medium">پیش‌نمایش فونت</span>
                            <span id="preview-font-name" class="text-xs sm:text-sm font-semibold text-zinc-700"></span>
                        </div>

                        <div id="preview-box" class="w-full p-5 border border-zinc-200 rounded-xl bg-zinc-50 text-zinc-800 leading-relaxed overflow-auto" style="font-size: 18px;">
                            این یک نوشته آزمایشی است که به برنامه‌نویسان کمک میکند
                            <br>The quick brown fox jumps over the lazy dog
                            <br>0123456789
                        </div>

                        <div>
                            <input id="preview-custom-text" type="text" placeholder="متن خود را اینجا تایپ کنید..." class="w-full px-4 py-2.5 text-sm border border-zinc-200 rounded-lg bg-white text-zinc-800 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-500/30 focus:border-zinc-400 transition-colors">
                        </div>
                    </div>
                </div>

                <div id="success-box" class="hidden p-6 bg-zinc-100 rounded-2xl ring ring-zinc-800/15">
                    <div class="flex items-center gap-3 mb-6">
                        <h3 id="success-font-name" class="text-lg font-bold text-zinc-800"></h3>
                    </div>
                    <div class="border-t border-zinc-700 pt-5">
                        <p class="text-xs font-bold text-zinc-600 uppercase tracking-wider mb-3">استفاده در پروژه</p>
                        <div class="flex items-center justify-between bg-zinc-900 rounded-xl p-4 mb-3">
                            <button id="copy-usage-btn" type="button" class="px-3 py-1.5 text-xs font-semibold text-white bg-zinc-700 hover:bg-zinc-600 rounded-lg transition-colors">کپی</button>
                            <code dir="ltr" id="usage-code" class="text-sm text-green-400 font-mono"></code>
                        </div>
                        <p class="text-xs text-zinc-600 mb-5">فایل زیپ را از حالت فشرده خارج کرده و فایل‌ها را در پوشه /tcpdf/fonts/ کپی کنید.</p>
                        <a id="download-btn" href="#" class="w-fit flex items-center rounded-2xl px-4 py-3.5 text-sm font-semibold text-white bg-zinc-800">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_4418_9710)">
                                <path d="M9 11V17L11 15" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 17L7 15" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 10V15C22 20 20 22 15 22H9C4 22 2 20 2 15V9C2 4 4 2 9 2H14" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 10H18C15 10 14 9 14 6V2L22 10Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </g>
                                <defs>
                                <clipPath id="clip0_4418_9710">
                                <rect width="24" height="24" fill="white"/>
                                </clipPath>
                            </defs>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Google Fonts Tab --}}
            <div id="panel-google" class="hidden space-y-5">
                <div class="relative">
                    <input id="gf-search" type="text" placeholder="جستجوی فونت..." class="w-full px-4 py-3 text-sm border border-zinc-200 rounded-xl bg-white text-zinc-800 placeholder-zinc-400 focus:outline-none focus:ring-3 focus:ring-zinc-500/30 focus:border-zinc-400 transition-colors pr-10">
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 size-5 text-zinc-400 *:stroke-current stroke-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11 20C15.9706 20 20 15.9706 20 11C20 6.02944 15.9706 2 11 2C6.02944 2 2 6.02944 2 11C2 15.9706 6.02944 20 11 20Z" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M18.8978 20.4629C19.1822 22.1242 20.3546 22.4637 21.4838 21.2188C22.5159 20.0805 22.1195 18.9585 20.5969 18.7278C19.4713 18.5472 18.7052 19.3313 18.8978 20.4629Z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <div id="gf-loading" class="hidden text-center py-8">
                    <p class="text-sm text-zinc-500">در حال جستجو...</p>
                </div>

                <div id="gf-error" class="hidden p-4 text-xs sm:text-sm text-red-700 bg-red-500/15 rounded-lg"></div>

                <div id="gf-results" class="hidden scrollbar-thin space-y-2 max-h-96 overflow-y-auto"></div>

                <div id="gf-empty" class="hidden text-center py-8">
                    <p class="text-sm text-zinc-400">نتیجه‌ای یافت نشد.</p>
                </div>

                <div id="gf-progress" class="hidden space-y-2">
                    <div class="w-full h-2 bg-zinc-200 rounded-full overflow-hidden">
                        <div id="gf-progress-bar" class="h-full bg-zinc-900 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
                    </div>
                    <p id="gf-progress-status" class="text-xs text-zinc-500 text-center"></p>
                </div>
            </div>
        </main>

        @if($conversions->count())
        <div class="max-w-3xl w-full mt-6">
            <h2 class="text-lg font-bold text-zinc-800 mb-4">تاریخچه تبدیل‌ها</h2>
            <div class="bg-white rounded-3xl ring-2 ring-zinc-900/10 p-6 space-y-3">
                @foreach($conversions as $conversion)
                <div class="flex items-center px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl hover:bg-zinc-100 transition-colors">
                    <div class="flex flex-col items-start gap-2">
                        <p class="text-sm font-semibold text-zinc-700 truncate">{{ $conversion->font_names }}</p>
                        <p class="text-xs text-zinc-400">{{ $conversion->file_count }} فونت &bull; {{ $conversion->created_at->diffForHumans() }}</p>
                    </div>
                    <a href="{{ route('converter.download', $conversion) }}" class="flex items-center p-2.5 bg-zinc-900 hover:bg-zinc-800 rounded-[10px] transition-colors">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g class="*:stroke-2" clip-path="url(#clip0_4418_9710)">
                            <path d="M9 11V17L11 15" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 17L7 15" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 10V15C22 20 20 22 15 22H9C4 22 2 20 2 15V9C2 4 4 2 9 2H14" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 10H18C15 10 14 9 14 6V2L22 10Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_4418_9710">
                            <rect width="24" height="24" fill="white"/>
                            </clipPath>
                            </defs>
                        </svg>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <script>
            // ── Tabs ──
            const tabUpload = document.getElementById('tab-upload');
            const tabGoogle = document.getElementById('tab-google');
            const panelUpload = document.getElementById('panel-upload');
            const panelGoogle = document.getElementById('panel-google');

            function switchTab(tab) {
                const isUpload = tab === 'upload';
                tabUpload.className = 'tab-btn px-4 py-2.5 text-sm font-semibold border-b-2 ' + (isUpload ? 'border-zinc-900 text-zinc-900' : 'border-transparent text-zinc-400 hover:text-zinc-600') + ' -mb-px transition-colors';
                tabGoogle.className = 'tab-btn px-4 py-2.5 text-sm font-semibold border-b-2 ' + (!isUpload ? 'border-zinc-900 text-zinc-900' : 'border-transparent text-zinc-400 hover:text-zinc-600') + ' -mb-px transition-colors';
                panelUpload.classList.toggle('hidden', !isUpload);
                panelGoogle.classList.toggle('hidden', isUpload);
            }

            tabUpload.addEventListener('click', () => switchTab('upload'));
            tabGoogle.addEventListener('click', () => switchTab('google'));

            // ── Upload Tab Logic ──
            const PREVIEW_FONT_NAME = 'preview-font';
            const defaultText = " این یک نوشته آزمایشی است که به برنامه‌نویسان کمک میکند\nThe quick brown fox jumps over the lazy dog\n0123456789";
            let currentFontFace = null;

            const dropzone = document.getElementById('dropzone-file');
            const fileNameDisplay = document.getElementById('file-name-display');
            const fileListEl = document.getElementById('file-list');
            const previewSection = document.getElementById('preview-section');
            const previewBox = document.getElementById('preview-box');
            const previewFontName = document.getElementById('preview-font-name');
            const customTextInput = document.getElementById('preview-custom-text');
            const fontInfoName = document.getElementById('font-info-name');
            const fontInfoSize = document.getElementById('font-info-size');

            function cleanupPreviewFont() {
                if (currentFontFace) {
                    document.fonts.delete(currentFontFace);
                    currentFontFace = null;
                }
            }

            async function loadFontPreview(file) {
                cleanupPreviewFont();
                const arrayBuffer = await file.arrayBuffer();
                currentFontFace = new FontFace(PREVIEW_FONT_NAME, arrayBuffer);
                await currentFontFace.load();
                document.fonts.add(currentFontFace);

                previewBox.style.fontFamily = `'${PREVIEW_FONT_NAME}', sans-serif`;
                previewBox.style.fontWeight = 'normal';
                previewBox.style.fontStyle = 'normal';
                previewBox.textContent = '';

                const lines = defaultText.split('\n');
                lines.forEach((line, i) => {
                    previewBox.appendChild(document.createTextNode(line));
                    if (i < lines.length - 1) previewBox.appendChild(document.createElement('br'));
                });

                previewFontName.textContent = file.name;
                fontInfoName.textContent = file.name;
                fontInfoSize.textContent = (file.size / 1024).toFixed(1) + ' KB';
                previewSection.classList.remove('hidden');
                previewBox.style.fontSize = '18px';
                customTextInput.value = '';
            }

            const dropzoneLabel = dropzone.closest('label');
            dropzoneLabel.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropzoneLabel.classList.add('border-zinc-500', 'bg-zinc-200/75');
            });
            dropzoneLabel.addEventListener('dragleave', function(e) {
                e.preventDefault();
                dropzoneLabel.classList.remove('border-zinc-500', 'bg-zinc-200/75');
            });
            dropzoneLabel.addEventListener('drop', function(e) {
                e.preventDefault();
                dropzoneLabel.classList.remove('border-zinc-500', 'bg-zinc-200/75');
                if (e.dataTransfer.files.length > 0) {
                    const dt = new DataTransfer();
                    for (const f of e.dataTransfer.files) {
                        if (f.name.toLowerCase().endsWith('.ttf')) dt.items.add(f);
                    }
                    dropzone.files = dt.files;
                    dropzone.dispatchEvent(new Event('change'));
                }
            });

            dropzone.addEventListener('change', async function(e) {
                const files = Array.from(e.target.files);
                if (files.length === 0) return;

                if (files.length === 1) {
                    fileNameDisplay.innerHTML = '<span class="font-semibold text-green-600">' + files[0].name + '</span>';
                    fileListEl.classList.add('hidden');
                    fileListEl.innerHTML = '';
                    fontInfoName.textContent = files[0].name;
                    fontInfoSize.textContent = (files[0].size / 1024).toFixed(1) + ' KB';
                } else {
                    fileNameDisplay.innerHTML = '<span class="font-semibold text-green-600">' + files.length + ' فونت</span>';
                    fileListEl.innerHTML = files.map(function(f, i) {
                        return '<div class="flex items-center justify-between px-3 py-2 bg-zinc-50 border border-zinc-200 rounded-lg text-sm">' +
                            '<span class="text-zinc-700 truncate">' + (i + 1) + '. ' + f.name + '</span>' +
                            '<span class="text-zinc-400 text-xs shrink-0">' + (f.size / 1024).toFixed(1) + ' KB</span>' +
                            '</div>';
                    }).join('');
                    fileListEl.classList.remove('hidden');
                    fontInfoName.textContent = files.length + ' فونت انتخاب شده';
                    fontInfoSize.textContent = files.reduce((sum, f) => sum + f.size, 0).toFixed(1) + ' KB (مجموع)';
                }

                try { await loadFontPreview(files[0]); } catch (err) {
                    previewSection.classList.add('hidden');
                    cleanupPreviewFont();
                }
            });

            customTextInput.addEventListener('input', function() {
                const text = this.value.trim();
                if (text) {
                    previewBox.textContent = text;
                } else {
                    previewBox.textContent = '';
                    const lines = defaultText.split('\n');
                    lines.forEach((line, i) => {
                        previewBox.appendChild(document.createTextNode(line));
                        if (i < lines.length - 1) previewBox.appendChild(document.createElement('br'));
                    });
                }
            });

            document.getElementById('converter-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const submitBtn = document.getElementById('submit-btn');
                const btnText = document.getElementById('btn-text');
                const errorContainer = document.getElementById('error-container');
                const progressContainer = document.getElementById('progress-container');
                const progressBar = document.getElementById('progress-bar');
                const progressStatus = document.getElementById('progress-status');

                submitBtn.disabled = true;
                errorContainer.classList.add('hidden');
                progressContainer.classList.remove('hidden');
                progressBar.style.width = '0%';
                progressStatus.textContent = 'آپلود فایل...';

                const xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const pct = Math.round((e.loaded / e.total) * 100);
                        progressBar.style.width = pct + '%';
                        if (pct < 100) {
                            progressStatus.textContent = 'آپلود فایل... ' + pct + '%';
                        } else {
                            progressStatus.textContent = 'در حال تبدیل فونت...';
                            progressBar.classList.remove('bg-zinc-900');
                            progressBar.classList.add('bg-zinc-400', 'indeterminate');
                        }
                    }
                });

                xhr.addEventListener('load', function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        progressBar.style.width = '100%';
                        progressBar.classList.remove('bg-zinc-900');
                        progressBar.classList.add('bg-green-600');
                        progressStatus.textContent = '';

                        const blob = xhr.response;
                        const disposition = xhr.getResponseHeader('Content-Disposition');
                        let filename = 'tcpdf_fonts.zip';
                        if (disposition && disposition.indexOf('attachment') !== -1) {
                            const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                            if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                        }

                        const downloadUrl = window.URL.createObjectURL(blob);

                        const files = Array.from(dropzone.files);
                        const firstFileName = files.length > 0 ? files[0].name.replace(/\.ttf$/i, '') : 'font';
                        const tcpdfName = firstFileName.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');

                        document.getElementById('success-font-name').textContent = firstFileName + '.ttf با موفقیت تبدیل شد';
                        document.getElementById('usage-code').textContent = "$pdf->setFont('" + tcpdfName + "', '', 10);";

                        const downloadBtn = document.getElementById('download-btn');
                        downloadBtn.href = downloadUrl;
                        downloadBtn.download = filename;

                        document.getElementById('preview-section').classList.add('hidden');
                        document.getElementById('success-box').classList.remove('hidden');

                        progressContainer.classList.add('hidden');
                        progressBar.style.width = '0%';
                        progressBar.style.transition = '';
                        progressBar.style.animation = '';
                        progressBar.classList.remove('bg-green-600', 'bg-zinc-400', 'indeterminate');
                        progressBar.classList.add('bg-zinc-900');
                        btnText.innerText = 'تبدیل و دانلود فونت‌ها';
                        submitBtn.disabled = false;
                    } else {
                        let msg = 'خطای تبدیل.';
                        try { msg = JSON.parse(xhr.responseText).message || msg; } catch (_) {}
                        errorContainer.innerText = msg;
                        errorContainer.classList.remove('hidden');
                    }
                });

                xhr.addEventListener('error', function() {
                    errorContainer.innerText = 'خطای شبکه. اتصال اینترنت خود را بررسی کنید.';
                    errorContainer.classList.remove('hidden');
                });

                xhr.addEventListener('loadend', function() {
                    if (progressStatus.textContent !== '') {
                        submitBtn.disabled = false;
                        progressContainer.classList.add('hidden');
                        progressBar.style.width = '0%';
                        progressBar.style.transition = '';
                        progressBar.style.animation = '';
                        progressBar.classList.remove('bg-green-600', 'bg-zinc-400', 'indeterminate');
                        progressBar.classList.add('bg-zinc-900');
                        progressStatus.textContent = '';
                        btnText.innerText = 'تبدیل و دانلود فونت‌ها';
                    }
                });

                xhr.open('POST', form.action);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json, application/zip');
                xhr.responseType = 'blob';
                xhr.send(new FormData(form));
            });

            document.getElementById('copy-usage-btn').addEventListener('click', function() {
                const code = document.getElementById('usage-code').textContent;
                navigator.clipboard.writeText(code).then(() => {
                    this.textContent = 'کپی شد';
                    setTimeout(() => { this.textContent = 'کپی'; }, 2000);
                });
            });

            // ── Google Fonts Tab Logic ──
            const gfSearch = document.getElementById('gf-search');
            const gfLoading = document.getElementById('gf-loading');
            const gfError = document.getElementById('gf-error');
            const gfResults = document.getElementById('gf-results');
            const gfEmpty = document.getElementById('gf-empty');
            const gfProgress = document.getElementById('gf-progress');
            const gfProgressBar = document.getElementById('gf-progress-bar');
            const gfProgressStatus = document.getElementById('gf-progress-status');
            let gfSearchTimeout = null;

            gfSearch.addEventListener('input', function() {
                clearTimeout(gfSearchTimeout);
                const q = this.value.trim();
                if (q.length < 2) {
                    gfResults.classList.add('hidden');
                    gfEmpty.classList.add('hidden');
                    return;
                }
                gfSearchTimeout = setTimeout(() => searchGoogleFonts(q), 300);
            });

            async function searchGoogleFonts(query) {
                gfLoading.classList.remove('hidden');
                gfResults.classList.add('hidden');
                gfEmpty.classList.add('hidden');
                gfError.classList.add('hidden');

                try {
                    const resp = await fetch('{{ route("google-fonts.search") }}?q=' + encodeURIComponent(query));

                    const text = await resp.text();
                    let data;
                    try { data = JSON.parse(text); } catch (_) {
                        gfError.innerText = 'پاسخ نامعتبر از سرور.';
                        gfError.classList.remove('hidden');
                        gfLoading.classList.add('hidden');
                        return;
                    }

                    if (!resp.ok) {
                        gfError.innerText = data.message || 'خطا در جستجو.';
                        gfError.classList.remove('hidden');
                        gfLoading.classList.add('hidden');
                        return;
                    }

                    gfLoading.classList.add('hidden');

                    if (data.length === 0) {
                        gfEmpty.classList.remove('hidden');
                        return;
                    }

                    gfResults.innerHTML = data.map(font => {
                        const variants = font.variants.filter(v => /^\d+$/.test(v));
                        return '<div class="flex items-center px-4 py-3 bg-zinc-50 border border-zinc-200 rounded-xl hover:bg-zinc-100 transition-colors">' +
                            '<div class="flex items-center gap-4 w-54">' +
                                '<p class="text-sm font-semibold text-zinc-700">' + font.family + '</p>' +
                                '<select class="gf-variant-select mr-auto px-2 py-1 text-sm border border-zinc-200 rounded-lg bg-white text-zinc-700 focus:outline-none focus:ring-1 focus:ring-zinc-400 w-20">' +
                                    variants.map(v => '<option value="' + v + '">' + v + '</option>').join('') +
                                '</select>' +
                            '</div>' +
                            '<button title="تبدیل" type="button" class="gf-convert-btn cursor-pointer mr-auto flex items-center gap-x-2 text-white p-2 bg-zinc-900 hover:bg-zinc-800 rounded-xl transition-colors" data-family="' + font.family + '">' +
                                '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_4482_220)"><path d="M8.5 12H5C3.62 12 2.5 10.88 2.5 9.5V4.5C2.5 3.12 3.62 2 5 2H8.5C9.88 2 11 3.12 11 4.5V9.5C11 10.88 9.88 12 8.5 12Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19.5002 22.35H15.7002C14.3202 22.35 13.2002 21.23 13.2002 19.85V14.5C13.2002 13.12 14.3202 12 15.7002 12H19.5002C20.8802 12 22.0002 13.12 22.0002 14.5V19.85C22.0002 21.23 20.8802 22.35 19.5002 22.35Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M2 15.6399C2 19.1499 4.84999 21.9999 8.35999 21.9999L7.41003 20.4099" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21.9996 8.36C21.9996 4.85 19.1496 2 15.6396 2L16.5896 3.59" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></g><defs><clipPath id="clip0_4482_220"><rect width="24" height="24" fill="white"/></clipPath></defs></svg>' +
                            '</button>' +
                        '</div>';
                    }).join('');

                    gfResults.classList.remove('hidden');

                    gfResults.querySelectorAll('.gf-convert-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const family = this.dataset.family;
                            const select = this.parentElement.querySelector('.gf-variant-select');
                            const variant = select.value;
                            convertGoogleFont(family, variant, this);
                        });
                    });
                } catch (err) {
                    gfLoading.classList.add('hidden');
                    gfError.innerText = 'خطای شبکه: ' + err.message;
                    gfError.classList.remove('hidden');
                }
            }

            async function convertGoogleFont(family, variant, btn) {
                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<svg class="size-5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="1"/></svg>';

                gfProgress.classList.remove('hidden');
                gfProgressBar.style.width = '30%';
                gfProgressStatus.textContent = 'دانلود فونت از گوگل...';
                gfError.classList.add('hidden');

                try {
                    const resp = await fetch('{{ route("google-fonts.convert") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/zip, application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ family, variant })
                    });

                    const contentType = resp.headers.get('content-type') || '';

                    if (!resp.ok || contentType.includes('application/json')) {
                        let msg = 'خطا در تبدیل فونت.';
                        try { const j = await resp.json(); msg = j.message || msg; } catch (_) {}
                        throw new Error(msg);
                    }

                    gfProgressBar.style.width = '80%';
                    gfProgressStatus.textContent = 'تبدیل فونت...';

                    const blob = await resp.blob();
                    const disposition = resp.headers.get('Content-Disposition');
                    let filename = family.replace(/\s+/g, '_') + '.zip';
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                        if (matches != null && matches[1]) filename = matches[1].replace(/['"]/g, '');
                    }

                    gfProgressBar.style.width = '100%';
                    gfProgressStatus.textContent = 'دانلود فایل...';

                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = downloadUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    setTimeout(function() {
                        window.URL.revokeObjectURL(downloadUrl);
                        a.remove();
                        window.location.reload();
                    }, 500);
                } catch (err) {
                    gfError.innerText = err.message;
                    gfError.classList.remove('hidden');
                    gfProgress.classList.add('hidden');
                    gfProgressBar.style.width = '0%';
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            }
        </script>
    </body>
</html>
