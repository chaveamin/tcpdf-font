<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تبدیل فونت به tcpdf</title>
    <link rel="shortcut icon" href="{{ url('favicon.png') }}" type="image/x-icon">
    @vite('resources/css/app.css')
</head>
<body class="bg-zinc-50 min-h-screen flex flex-col items-center justify-center p-6 relative font-vazirmatn">
    <header class=" absolute top-0 right-0 p-6">
        <a target="_blank" href="https://github.com/chaveamin/tcpdf-font">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <g clip-path="url(#clip0_4482_11501)">
                <path d="M7 21.99H8.67V20.84C8.67 20.84 8.65 19.04 9.64 17.52C7.38 17.36 5.59 15.35 5.59 12.86C5.59 11.99 5.92 11.17 6.49 10.47L6.55 8.32C6.55 7.77 7.11 7.4 7.62 7.59L9.93 8.42C10.58 8.26 11.28 8.17 12 8.17C12.72 8.17 13.42 8.26 14.07 8.43L16.38 7.6C16.89 7.41 17.44 7.79 17.45 8.33L17.51 10.48C18.08 11.18 18.41 11.99 18.41 12.87C18.41 15.36 16.62 17.37 14.36 17.53C15.35 19.05 15.33 20.85 15.33 20.85V22H17C19.76 22 22 19.76 22 17.01V7C22 4.24 19.76 2 17 2H7C4.24 2 2 4.24 2 7V17C2 19.76 4.24 22 7 22" stroke="black" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                </g>
                <defs>
                <clipPath id="clip0_4482_11501">
                <rect width="24" height="24" fill="black"/>
                </clipPath>
                </defs>
            </svg>
        </a>
    </header>
    <main class="max-w-3xl w-full bg-white rounded-2xl shadow-lg shadow-zinc-800/5 ring ring-zinc-900/5 p-8">
        <div class="text-center mb-8">
            <h1 class="text-xl sm:text-2xl font-extrabold text-zinc-800">تبدیل فونت به tcpdf</h1>
            <p class="text-zinc-500 mt-2 sm:text-base text-sm">برای دریافت فایل‌های سازگار با TCPDF فونت خود را با فرمت ttf یا otf آپلود کنید.</p>
        </div>

        @if(session('error'))
            <div class="mb-4 p-4 text-xs sm:text-sm text-red-700 bg-red-500/15 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('converter.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div class="flex items-center justify-center w-full">
                <label for="dropzone-file" class="p-4 flex flex-col items-center justify-center w-full h-48 border-2 border-zinc-300 border-dashed rounded-lg cursor-pointer bg-zinc-50 hover:bg-zinc-100 transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg aria-hidden="true" class="w-10 h-10 mb-3 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <p class="mb-2 sm:text-base text-sm text-zinc-500"><span class="font-semibold">برا آپلود کلیک کنید</span> یا فایل فونت را بکشید و رها کنید</p>
                        <p class="sm:text-sm text-xs text-zinc-500">.TTF یا .OTF (حداکثر 10 مگابایت)</p>
                    </div>
                    <input id="dropzone-file" type="file" name="font" class="hidden" accept=".ttf,.otf" required />
                </label>
            </div>
            @error('font')
                <p class="sm:text-sm text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror

            <button type="submit" class="w-full sm:text-base text-sm text-white ring ring-green-700 border-t border-t-white/10 bg-green-600 font-medium sm:font-bold rounded-xl px-5 py-3 text-center transition-colors">
                تبدیل و دانلود فونت
            </button>
        </form>
    </main>
    <script>
        document.getElementById('dropzone-file').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            var textElement = e.target.previousElementSibling.querySelector('p.mb-2');
            textElement.innerHTML = '<span class="font-semibold text-green-600">' + fileName + '</span> selected';
        });
    </script>
</body>
</html>