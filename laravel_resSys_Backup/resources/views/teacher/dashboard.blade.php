<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lehrer Dashboard - Elternsprechtag</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="max-w-5xl mx-auto p-6">
        <header class="bg-blue-600 text-white p-6 rounded-b-2xl shadow-lg mb-8">
            <h1 class="text-2xl font-bold">Lehrer-Bereich</h1>
            <p class="opacity-90">Ihre Termine für den heutigen Elternsprechtag</p>
        </header>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-500">
                <p class="text-sm text-gray-500">Gebuchte Slots</p>
                <p class="text-2xl font-bold">12 / 20</p>
            </div>

            <div class="md:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-4 font-semibold text-sm">Zeit</th>
                            <th class="p-4 font-semibold text-sm">Schüler</th>
                            <th class="p-4 font-semibold text-sm">Klasse</th>
                            <th class="p-4 font-semibold text-sm">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-4 font-mono text-blue-600">17:00</td>
                            <td class="p-4 font-medium">Peter Teufel</td>
                            <td class="p-4 text-gray-600">3AHIT</td>
                            <td class="p-4"><span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Bestätigt</span></td>
                        </tr>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-4 font-mono text-blue-600">17:10</td>
                            <td class="p-4 italic text-gray-400">Noch verfügbar</td>
                            <td class="p-4">-</td>
                            <td class="p-4"><span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-xs">Offen</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>