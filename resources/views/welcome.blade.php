<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paystack Payment Gateway</title>
</head>
<body class="antialiased">
    <div
        class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        <div class="max-w-7xl mx-auto p-6 lg:p-8">
            @if (session('status') === 'success')
                <div class="justify-center text-center">
                    <h2 class="mt-6 text-xl font-semibold" style="color: #1fde78">{{ session('message') }}</h2>
                </div>
            @else
                <div class="justify-center text-center">
                    <h2 class="mt-6 text-xl font-semibold" style="color: #de251f">{{ session('message') }}</h2>
                </div>
            @endif
        </div>
    </div>
</body>

</html>
