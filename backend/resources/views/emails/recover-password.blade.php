<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Código de Verificación - TelamoNet</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(140deg, #326465, #1d2e3e);
            color: #ffffff;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 20px;
        }
        .content p {
            color: #333333;
            font-size: 16px;
            line-height: 1.6;
            margin: 15px 0;
        }
        .code-box {
            background-color: #f0f0f0;
            border-left: 4px solid #326465;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
            border-radius: 6px;
        }
        .code-box .code {
            font-size: 36px;
            font-weight: 700;
            color: #326465;
            letter-spacing: 2px;
            font-family: 'Courier New', monospace;
        }
        .code-box .validity {
            font-size: 12px;
            color: #666666;
            margin-top: 10px;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #999999;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
        <div class="header">
            <h1>TelamoNet</h1>
        </div>

        <div class="content">
            <p>¡Hola!</p>

            <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong>TelamoNet</strong>. Utiliza el siguiente código de verificación para continuar con el proceso:</p>

            <div class="code-box">
                <div class="code">{{ $verificationCode }}</div>
                <div class="validity">Este código expira en 10 minutos</div>
            </div>

            <div>
                <strong>Importante:</strong> Si no solicitaste restablecer tu contraseña, puedes ignorar este correo de forma segura. No compartas este código con nadie.
            </div>

            <p>Una vez ingreses este código, podrás crear una nueva contraseña y recuperar el acceso a tu cuenta.</p>

            <p>
                ¿Tienes problemas? <a href="{{ config('app.frontend_url') }}">Inténtalo nuevamente</a>
            </p>

            <p>
                Saludos,<br>
                <strong>El equipo de TelamoNet</strong>
            </p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} TelamoNet. Todos los derechos reservados.</p>
            <p>Este es un correo automático, por favor no responder a este mensaje.</p>
        </div>
    </div>
</body>
</html>
