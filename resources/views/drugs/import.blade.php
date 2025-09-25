<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Drugs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6 text-center">Import Drugs</h1>
            
            <div class="mb-4">
                <a href="{{ route('drugs.template') }}" 
                   class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">
                    Download Template
                </a>
            </div>

            <form action="{{ route('drugs.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="mb-4">
                    <label for="file" class="block text-gray-700 text-sm font-bold mb-2">
                        Excel File:
                    </label>
                    <input type="file" 
                           name="file" 
                           id="file" 
                           accept=".xlsx,.xls"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           required>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" 
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Import Drugs
                    </button>
                </div>
            </form>

            <div id="result" class="mt-4 hidden">
                <div id="success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded hidden">
                    <strong>Success!</strong> <span id="successMessage"></span>
                </div>
                <div id="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded hidden">
                    <strong>Error!</strong> <span id="errorMessage"></span>
                    <ul id="errorList" class="mt-2 list-disc list-inside"></ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('importForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const resultDiv = document.getElementById('result');
            const successDiv = document.getElementById('success');
            const errorDiv = document.getElementById('error');
            
            // Hide previous results
            resultDiv.classList.add('hidden');
            successDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                resultDiv.classList.remove('hidden');
                
                if (data.success) {
                    successDiv.classList.remove('hidden');
                    document.getElementById('successMessage').textContent = data.message;
                    document.getElementById('importForm').reset();
                } else {
                    errorDiv.classList.remove('hidden');
                    document.getElementById('errorMessage').textContent = data.message;
                    
                    if (data.errors && data.errors.length > 0) {
                        const errorList = document.getElementById('errorList');
                        errorList.innerHTML = '';
                        data.errors.forEach(error => {
                            const li = document.createElement('li');
                            li.textContent = error;
                            errorList.appendChild(li);
                        });
                    }
                }
            })
            .catch(error => {
                resultDiv.classList.remove('hidden');
                errorDiv.classList.remove('hidden');
                document.getElementById('errorMessage').textContent = 'An unexpected error occurred.';
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>