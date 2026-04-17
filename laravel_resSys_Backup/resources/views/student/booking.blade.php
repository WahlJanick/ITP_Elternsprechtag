<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termin buchen - Elternsprechtag</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="max-w-4xl mx-auto p-6">
        <header class="flex justify-between items-center mb-8 bg-white p-4 rounded-lg shadow-sm">
    <div>
        <h1 class="text-xl font-bold">Elternsprechtag Buchung</h1>
        <p class="text-sm text-gray-500">
            Angemeldet als: <strong>{{ Auth::user()->name }}</strong> 
            (Klasse: {{ Auth::user()->klasse ?? 'Nicht zugewiesen' }})
        </p>
    </div>
    <form action="{{ url('/logout') }}" method="GET">
        <button type="submit" class="text-red-500 text-sm hover:underline">Abmelden</button>
    </form>
</header>

        <section class="grid gap-6">
    <h2 class="text-lg font-semibold">Verfügbare Lehrer & Termine</h2>
    
    @forelse($teachers as $teacher)
        <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-blue-600">{{ $teacher->name }}</h3>
                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">
                    Raum: {{ $teacher->raum ?? 'N.N.' }}
                </span>
            </div>
            
            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                {{-- Hier kommen später die echten Slots rein --}}
                <p class="text-sm text-gray-400 italic">Lade Termine...</p>
            </div>
        </div>
    @empty
        <div class="text-center text-gray-400 py-10 border-2 border-dashed rounded-lg">
            Momentan sind keine Lehrer im System registriert.
        </div>
    @endforelse
</section>
    </div>
</body>
</html>