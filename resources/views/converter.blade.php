<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TCPDF Font Converter</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-800">TCPDF Font Converter</h1>
            <p class="text-sm text-gray-500 mt-2">Upload a .TTF or .OTF file to get your TCPDF compatible files (.php, .z).</p>
        </div>

        @if(session('error'))
            <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('converter.process') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div class="flex items-center justify-center w-full">
                <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg aria-hidden="true" class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                        <p class="text-xs text-gray-500">.TTF or .OTF (MAX. 10MB)</p>
                    </div>
                    <input id="dropzone-file" type="file" name="font" class="hidden" accept=".ttf,.otf" required />
                </label>
            </div>
            @error('font')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror

            <button type="submit" class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-3 text-center transition-colors">
                Convert & Download ZIP
            </button>
        </form>
    </div>

    <script>
        document.getElementById('dropzone-file').addEventListener('change', function(e) {
            var fileName = e.target.files[0].name;
            var textElement = e.target.previousElementSibling.querySelector('p.mb-2');
            textElement.innerHTML = '<span class="font-semibold text-blue-600">' + fileName + '</span> selected';
        });
    </script>
</body>
</html>