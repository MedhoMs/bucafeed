<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TelamoNet')</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f0f4f4;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f0f4f4;
            padding-bottom: 40px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(15, 40, 40, 0.08);
            overflow: hidden;
            margin-top: 40px;
            border: 1px solid rgba(0,0,0,0.05);
        }
        .header {
            background: linear-gradient(135deg, #1f5252 0%, #0f2828 100%);
            color: #ffffff;
            padding: 60px 40px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: -0.04em;
            text-transform: uppercase;
        }
        .header .subtitle {
            font-size: 14px;
            opacity: 0.6;
            margin-top: 8px;
            font-weight: 600;
            letter-spacing: 0.1em;
        }
        .content {
            padding: 50px 40px;
        }
        .content h2 {
            color: #0f2828;
            font-size: 24px;
            font-weight: 800;
            margin-top: 0;
            margin-bottom: 20px;
            letter-spacing: -0.02em;
        }
        .content p {
            color: #4a5568;
            font-size: 16px;
            line-height: 1.7;
            margin: 18px 0;
        }
        .code-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 16px;
            padding: 32px;
            margin: 35px 0;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        .code-box .code {
            font-size: 42px;
            font-weight: 900;
            color: #326465;
            letter-spacing: 4px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .code-box .validity {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .btn-container {
            text-align: center;
            margin: 40px 0;
        }
        .btn {
            background: linear-gradient(135deg, #326465 0%, #1f5252 100%);
            color: #ffffff !important;
            padding: 16px 40px;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 800;
            font-size: 16px;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(50, 100, 101, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .footer {
            padding: 40px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
            line-height: 1.6;
        }
        .footer p { margin: 8px 0; }
        .footer a {
            color: #326465;
            text-decoration: none;
            font-weight: 700;
        }
        .divider {
            height: 1px;
            background-color: #edf2f7;
            margin: 40px 0;
        }
        .signature {
            margin-top: 40px;
            color: #1a202c;
        }
        .signature strong {
            color: #0f2828;
            font-weight: 800;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin-top: 0;
                border-radius: 0;
            }
            .header, .content {
                padding: 40px 25px;
            }
        }
    </style>
    @yield('style')
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>TelamoNet</h1>
            </div>

            <div class="content">
                @yield('content')

                <div class="signature">
                    <p>Saludos,<br>
                    <strong>El equipo de TelamoNet</strong></p>
                </div>
            </div>

            <div class="footer">
                <p>&copy; {{ date('Y') }} TelamoNet. Todos los derechos reservados.</p>
                <p>Este es un correo automático generado por el sistema.<br>Por favor, no respondas directamente a esta dirección.</p>
                <div style="margin-top: 20px;">
                    @yield('footer_links')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
