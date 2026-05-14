@extends('layouts.email')

@section('title', 'Código de Verificación - TelamoNet')

@section('content')
    <h2>Verifica tu cuenta</h2>

    <p>¡Hola!</p>

    <p>Has iniciado el proceso de registro en <strong>TelamoNet</strong>. Para confirmar tu identidad y completar el registro, utiliza el siguiente código de verificación:</p>

    <div class="code-box">
        <div class="code">{{ $verificationCode }}</div>
        <div class="validity">Este código expira en 10 minutos</div>
    </div>

    <div class="warning">
        <strong>Importante:</strong> Si no has iniciado este registro, ignora este email. No compartas este código con nadie.
    </div>

    <p>Una vez ingreses este código, podrás completar tu registro y acceder a TelamoNet.</p>

    <p>
        ¿Tienes problemas? <a href="{{ config('app.frontend_url') }}">Vuelve a intentarlo</a>
    </p>
@endsection
