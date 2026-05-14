@extends('layouts.email')

@section('title', 'Recuperar Contraseña - TelamoNet')

@section('content')
    <p>¡Hola!</p>

    <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong>TelamoNet</strong>. Utiliza el siguiente código de verificación para continuar con el proceso:</p>

    <div class="code-box">
        <div class="code">{{ $verificationCode }}</div>
        <div class="validity">Este código expira en 10 minutos</div>
    </div>

    <div class="warning">
        <strong>Importante:</strong> Si no solicitaste restablecer tu contraseña, puedes ignorar este correo de forma segura. No compartas este código con nadie.
    </div>

    <p>Una vez ingreses este código, podrás crear una nueva contraseña y recuperar el acceso a tu cuenta.</p>

    <p>
        ¿Tienes problemas? <a href="{{ config('app.frontend_url') }}">Inténtalo nuevamente</a>
    </p>
@endsection
