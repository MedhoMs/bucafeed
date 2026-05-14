@extends('layouts.email')

@section('title', $title ?? 'Notificación - TelamoNet')

@section('content')
    <h2>{{ $title ?? 'Nueva notificación' }}</h2>
    
    <p>¡Hola, <strong>{{ $user->name }}</strong>!</p>
    
    <p>{{ $body }}</p>

    @if ($actionUrl)
        <div class="btn-container">
            <a href="{{ $actionUrl }}" class="btn">{{ $actionText ?? 'Ver en TelamoNet' }}</a>
        </div>
    @endif

    <p style="font-size: 14px; color: #718096; margin-top: 30px; font-style: italic;">
        Has recibido esta notificación porque está relacionada con tu actividad en la plataforma.
    </p>
@endsection
