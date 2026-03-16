
<x-layout>
    <x-slot:title>
        Welcome Books
    </x-slot:title>
    <div class="max-w-2xl mx-auto">
        @foreach ($customBooks as $book)
            <div class="card bg-base-100 shadow mt-8">
                <div class="card-body">
                    <div>
                        <div class="font-semibold">{{ $book['author'] }}</div>
                        <div class="mt-1">{{ $book['title'] }}</div>
                        <div class="text-sm text-gray-500 mt-2">{{ $book['genre'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-layout>