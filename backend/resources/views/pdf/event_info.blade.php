<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $event->title }} — TelamoNet</title>
    <style>

        @page { margin: 0; size: A4; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Calibri', 'Candara', 'Segoe UI', 'Arial', sans-serif;
            background-color: #ffffff;
            color: #1d2e3e;
            font-size: 14px;
            line-height: 1.4;
        }

        /* CABECERA */
        .header {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 2.2cm;
            background-color: #1f5252;
            border-bottom: 3px solid #448888;
        }

        /* PIE */
        .footer {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: 1.1cm;
            background-color: #1f5252;
            border-top: 3px solid #448888;
        }

        /* CONTENIDO */
        .content {
            margin-top: 2.2cm;
            margin-bottom: 1.1cm;
            padding: 0.6cm 1cm 0.6cm 1cm;
        }

        /* Imagen */
        .event-image-wrap {
            width: 100%;
            height: 13cm;
            border-radius: 18px;
            overflow: hidden;
            margin-bottom: 0.6cm;
        }
        .event-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Fallback */
        .event-image-fallback {
            width: 100%;
            height: 13cm;
            background-color: #1f5252;
            border-radius: 18px;
            margin-bottom: 0.6cm;
            overflow: hidden;
        }

        /* Título */
        .event-title {
            font-size: 26px;
            font-weight: bold;
            color: #1d2e3e;
            letter-spacing: -0.5px;
            line-height: 1.2;
            margin-bottom: 5px;
        }
        .title-accent {
            width: 45px;
            height: 4px;
            background-color: #179cf0;
            border-radius: 2px;
            margin-bottom: 0.5cm;
        }

        /* Descripción */
        .section-label {
            font-size: 10px;
            font-weight: bold;
            color: #448888;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 8px;
        }
        .desc-text {
            font-size: 13px;
            color: #406071;
            line-height: 1.7;
            text-align: justify;
            margin-bottom: 0.6cm;
        }

        /* Cards Layout - 2x2 Grid Uniform and Aligned */
        .cards-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .cards-grid td {
            width: 50%;
            padding: 12px 0; /* Remove horizontal padding to allow margin alignment */
            vertical-align: top;
        }
        .cell-left { text-align: left; }
        .cell-right { text-align: right; }

        .detail-card {
            display: inline-block;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 20px;
            width: 8.2cm; /* Uniform size */
            text-align: left;
        }

        .card-label {
            font-size: 9px;
            font-weight: bold; /* Titulo mas distintivo */
            color: #448888;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 4px;
            display: block;
        }
        .card-value {
            font-size: 14px;
            font-weight: bold;
            color: #1d2e3e;
            line-height: 1.1;
            white-space: nowrap;
        }

    </style>
</head>
<body>

    {{-- CABECERA --}}
    <div class="header">
        <table width="100%" style="border-collapse:collapse; height:2.2cm;">
            <tr>
                <td width="50%" style="height:2.2cm; vertical-align:middle; padding-left:1cm;">
                    <table style="border-collapse:collapse;">
                        <tr>
                            <td style="vertical-align:middle; padding-right:12px;">
                                @php $logoPath = public_path('images/logo_telamonet.png'); @endphp
                                @if(file_exists($logoPath))
                                    <img src="{{ $logoPath }}" height="42" style="display:block;" alt="Logo">
                                @endif
                            </td>
                            <td style="vertical-align:middle;">
                                <div style="font-size:24px; font-weight:bold; color:#ffffff; line-height:0.9;">
                                    Telamo<span style="color:#a0c4d4;">Net</span>
                                </div>
                                <div style="font-size:10px; font-weight:bold; color:#7abcbc; text-transform:uppercase; letter-spacing:1.5px; margin-top:3px;">
                                    Red Educativa
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="50%" style="height:2.2cm; vertical-align:middle; text-align:right; padding-right:1cm;">
                    <div style="font-size:13px; font-weight:bold; color:#ffffff; line-height:1.2;">
                        {{ $event->educationalCenter->name ?? 'Portal Educativo' }}
                    </div>
                    <div style="font-size:9px; font-weight:bold; color:#7abcbc; text-transform:uppercase; letter-spacing:1px; margin-top:4px;">
                        Dossier de Evento
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- PIE --}}
    <div class="footer">
        <table width="100%" style="border-collapse:collapse; height:1.1cm;">
            <tr>
                <td style="height:1.1cm; text-align:center; vertical-align:middle; font-size:10px; font-weight:bold; color:rgba(255,255,255,0.7); letter-spacing:0.5px;">
                    © {{ date('Y') }} TelamoNet &nbsp;|&nbsp; Conectando la comunidad educativa &nbsp;|&nbsp; Generado el {{ \Carbon\Carbon::now()->translatedFormat('d \d\e F, Y') }}
                </td>
            </tr>
        </table>
    </div>

    {{-- CONTENIDO --}}
    <div class="content">

        @php
            $logoPath      = public_path('images/logo_telamonet.png');
            $eventImageUrl = null;
            if ($event->image) {
                $imgPath = public_path(ltrim($event->image, '/'));
                if (file_exists($imgPath)) { $eventImageUrl = $imgPath; }
            }
        @endphp

        {{-- Imagen o Fallback --}}
        @if($eventImageUrl)
            <div class="event-image-wrap">
                <img src="{{ $eventImageUrl }}" alt="Evento">
            </div>
        @else
            <div class="event-image-fallback">
                <table width="100%" style="border-collapse:collapse; height:13cm;">
                    <tr>
                        <td style="height:13cm; text-align:center; vertical-align:middle;">
                            @if(file_exists($logoPath))
                                <img src="{{ $logoPath }}" style="max-height:5.5cm; max-width:55%; opacity:0.25;" alt="TelamoNet">
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        @endif

        <div class="event-title">{{ $event->title }}</div>
        <div class="title-accent"></div>

        @if($event->description)
            <div class="section-label">Descripción</div>
            <div class="desc-text">{{ $event->description }}</div>
        @endif

        <div class="section-label" style="margin-top: 5px;">Detalles del Evento</div>
        
        <table class="cards-grid">
            <tr>
                <td class="cell-left">
                    <div class="detail-card">
                        <span class="card-label">Fecha</span>
                        <div class="card-value">{{ \Carbon\Carbon::parse($event->date)->translatedFormat('d \d\e F, Y') }}</div>
                    </div>
                </td>
                <td class="cell-right">
                    <div class="detail-card">
                        <span class="card-label">Horario</span>
                        <div class="card-value">
                            {{ substr($event->start_time, 0, 5) }}{{ $event->end_time ? ' – ' . substr($event->end_time, 0, 5) : '' }}
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="cell-left">
                    <div class="detail-card">
                        <span class="card-label">Ubicación</span>
                        <div class="card-value">{{ $event->location ?? 'Por determinar' }}</div>
                    </div>
                </td>
                <td class="cell-right">
                    <div class="detail-card">
                        <span class="card-label">Dirigido a</span>
                        <div class="card-value">{{ $event->target_role ?? 'Todos' }}</div>
                    </div>
                </td>
            </tr>
        </table>

    </div>

</body>
</html>