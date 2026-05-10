@extends('layouts.portal', [
    'pageTitle' => 'Schüler Gebuchte Termine',
    'roleTitle' => 'Schüler-Ansicht',
    'theme' => 'student',
    'homeRoute' => 'student.booking',
    'navLinks' => [
        ['label' => 'Start', 'route' => 'student.booking', 'active' => 'student.booking'],
        ['label' => 'Buchen', 'route' => 'student.teachers.index', 'active' => 'student.teachers.*'],
    ],
])

@section('content')
    <div class="stack">
        <section class="panel">
            <div class="panel-header">
                <div>
                    <span class="eyebrow">Meine Termine</span>
                    <h2 class="panel-title">Gebuchte Termine</h2>
                    <p class="panel-subtitle">
                        Hier sieht der Schüler Lehrer, Raum und Uhrzeit und kann Termine wieder stornieren.
                    </p>
                </div>

                <button type="button" class="print-button" onclick="window.print()">Termine drucken</button>
            </div>

            @if($bookings->isEmpty())
                <p class="empty-copy">Du hast aktuell noch keine gebuchten Termine.</p>
            @else
                <div class="list-stack">
                    @foreach($bookings as $booking)
                        <article class="list-row">
                            <div class="list-row-copy">
                                <p class="list-row-title">{{ $booking['teacher_name'] }} / {{ $booking['room'] }} / {{ $booking['time_label'] }}</p>
                                <p class="meta-copy">{{ $booking['date_label'] }} / Klasse {{ $booking['class_name'] }}</p>
                            </div>

                            <div class="list-row-actions">
                                <span class="status-chip is-booked">Gebucht</span>
                                <form method="POST" action="{{ route('student.bookings.cancel', $booking['id']) }}">
                                    @csrf
                                    <button type="submit" class="danger-button">Stornieren</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
