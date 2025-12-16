<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Notificación de Alsernet')</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .wrapper {
            width: 100%;
            background-color: #f5f5f5;
            padding: 20px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-bottom: 3px solid #90bb13;
        }
        .header-logo {
            max-height: 40px;
            margin-bottom: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .header-title {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-subtitle {
            margin: 10px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #333;
        }
        .message-content {
            background-color: #f9f9f9;
            border-left: 4px solid #90bb13;
            padding: 20px;
            margin: 20px 0;
            line-height: 1.8;
            color: #333;
        }
        .info-box {
            background-color: #f0f7ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .info-box strong {
            color: #0056b3;
        }
        .button-group {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #90bb13;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 0 10px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #7fa312;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .status-processing { background-color: #cce5ff; color: #004085; }
        .status-completed { background-color: #d1ecf1; color: #0c5460; }
        .status-warning { background-color: #fff3cd; color: #856404; }
        .divider {
            height: 1px;
            background-color: #e0e0e0;
            margin: 20px 0;
        }
        .order-info {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 13px;
        }
        .order-info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .order-info-row:last-child {
            border-bottom: none;
        }
        .order-info-label {
            font-weight: bold;
            color: #555;
        }
        .order-info-value {
            color: #333;
            text-align: right;
        }
        .alert {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .alert-info {
            background-color: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .footer {
            background-color: #f9f9f9;
            border-top: 1px solid #e0e0e0;
            padding: 20px 30px;
            text-align: center;
            color: #666;
            font-size: 12px;
            line-height: 1.8;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer-link {
            color: #90bb13;
            text-decoration: none;
        }
        .footer-link:hover {
            text-decoration: underline;
        }
        .text-muted {
            color: #999;
        }
        .text-center {
            text-align: center;
        }
        .mt-20 {
            margin-top: 20px;
        }
        .mb-20 {
            margin-bottom: 20px;
        }
        .ml-10 {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1 class="header-title">@yield('header', 'Alsernet')</h1>
                <p class="header-subtitle">@yield('header-subtitle', 'Gestión de Documentos')</p>
            </div>

            <!-- Main Content -->
            <div class="content">
                @yield('content')
            </div>

            <!-- Footer -->
            <div class="footer">
                <p><strong>Alsernet - Gestión de Documentos</strong></p>
                <p>Este es un correo automático enviado desde nuestro sistema de gestión.</p>
                <p style="margin-top: 10px; opacity: 0.7;">
                    © {{ date('Y') }} Alsernet. Todos los derechos reservados.
                </p>
                <p style="margin-top: 15px; border-top: 1px solid #e0e0e0; padding-top: 15px;">
                    Si tienes preguntas o necesitas ayuda,
                    <a href="mailto:support@Alsernet.com" class="footer-link">contacta con nuestro equipo</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
