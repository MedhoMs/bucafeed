<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 40px; }
        .container { background: #ffffff; max-width: 480px; margin: 0 auto; border-radius: 10px; padding: 40px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .code { font-size: 42px; font-weight: bold; letter-spacing: 10px; color: #326465; margin: 30px 0; }
        .footer { font-size: 12px; color: #999; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verifica tu cuenta</h2>
        <p>Usa el siguiente código para completar tu registro. Expira en <strong>10 minutos</strong>.</p>
        <div class="code">{{ $code }}</div>
        <p>Si no has solicitado este código, ignora este mensaje.</p>
        <div class="footer">Este es un mensaje automático, por favor no respondas a este correo.</div>
    </div>
</body>
</html>
