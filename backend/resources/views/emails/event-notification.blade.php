@extends('layouts.email')

@section('title', 'Nuevo Evento Académico - TelamoNet')

@section('style')
<style>
    .event-image-container {
        margin: -40px -30px 30px -30px;
        overflow: hidden;
    }
    .event-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        display: block;
    }
    .badge {
        background-color: #e2e8f0;
        color: #475569;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-block;
        margin-bottom: 15px;
    }
    .event-details {
        background-color: #f8fafc;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 25px;
    }
    .detail-item {
        margin-bottom: 10px;
        font-size: 14px;
    }
    .detail-item strong {
        color: #64748b;
        display: inline-block;
        width: 70px;
    }
</style>
@endsection

@section('content')
    @if(isset($eventImageUrl))
        <div class="event-image-container">
            <img src="{{ $eventImageUrl }}" alt="{{ $eventTitle }}" class="event-image">
        </div>
    @endif

    <div class="badge">Nuevo Evento</div>
    <h2 style="margin-top: 0;">{{ $eventTitle }}</h2>
    
    <p>Se ha publicado un nuevo evento académico en <strong>TelamoNet</strong> que podría interesarte.</p>

    <div class="event-details">
        <div class="detail-item">
            <strong>Fecha:</strong> <span>{{ $eventDate }}</span>
        </div>
        <div class="detail-item">
            <strong>Lugar:</strong> <span>{{ $eventLocation }}</span>
        </div>
        <div class="detail-item">
            <strong>Centro:</strong> <span>{{ $schoolName }}</span>
        </div>
    </div>

    <div class="btn-container">
        <a href="{{ $actionUrl }}" class="btn">Ver detalles del evento</a>
    </div>
@endsection
