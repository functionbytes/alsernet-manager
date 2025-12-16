<?php

namespace Database\Seeders;

use App\Models\Mail\MailLayout;
use App\Models\Mail\MailLayoutLang;
use Illuminate\Database\Seeder;

class EmailTemplateLayoutSeeder extends Seeder
{
    /**
     * Crear plantillas base de header y footer para email templates
     */
    public function run()
    {
        // Obtener el primer idioma disponible
        $defaultLangId = \App\Models\Lang::first()->id ?? 1;

        // HEADER: Plantilla de encabezado para correos electrónicos
        $headerLayout = MailLayout::updateOrCreate(
            ['alias' => 'email_template_header'],
            [
                'group_name' => 'email_templates',
                'code' => 'email_header',
                'type' => 'partial',
                'is_protected' => true,
                'is_enabled' => true,
            ]
        );

        // Crear/actualizar traducción
        MailLayoutLang::updateOrCreate(
            ['layout_id' => $headerLayout->id, 'lang_id' => $defaultLangId],
            [
                'subject' => 'Encabezado de la plantilla de correo electrónico',
                'content' => $this->getHeaderContent(),
            ]
        );

        // FOOTER: Plantilla de pie de página para correos electrónicos
        $footerLayout = MailLayout::updateOrCreate(
            ['alias' => 'email_template_footer'],
            [
                'group_name' => 'email_templates',
                'code' => 'email_footer',
                'type' => 'partial',
                'is_protected' => true,
                'is_enabled' => true,
            ]
        );

        // Crear/actualizar traducción
        MailLayoutLang::updateOrCreate(
            ['layout_id' => $footerLayout->id, 'lang_id' => $defaultLangId],
            [
                'subject' => 'Pie de página de la plantilla de correo electrónico',
                'content' => $this->getFooterContent(),
            ]
        );

        // LAYOUT COMPLETO: Wrapper que incluye header y footer
        $fullLayout = MailLayout::updateOrCreate(
            ['alias' => 'email_template_wrapper'],
            [
                'group_name' => 'email_templates',
                'code' => 'email_wrapper',
                'type' => 'layout',
                'is_protected' => true,
                'is_enabled' => true,
            ]
        );

        // Crear/actualizar traducción
        MailLayoutLang::updateOrCreate(
            ['layout_id' => $fullLayout->id, 'lang_id' => $defaultLangId],
            [
                'subject' => 'Plantilla completa de correo electrónico',
                'content' => $this->getWrapperContent(),
            ]
        );

        $this->command->info('✓ Plantillas base de email creadas exitosamente');
        $this->command->info("  - Header ID: {$headerLayout->id}");
        $this->command->info("  - Footer ID: {$footerLayout->id}");
        $this->command->info("  - Wrapper ID: {$fullLayout->id}");

        // Crear plantilla de ejemplo: Reset Password
        $this->createResetPasswordTemplate($fullLayout->id);
    }

    /**
     * Crear plantilla de ejemplo para reset password
     */
    private function createResetPasswordTemplate($layoutId)
    {
        $template = \App\Models\Mail\MailTemplate::updateOrCreate(
            ['key' => 'password_reset'],
            [
                'name' => 'Restablecer Contraseña',
                'subject' => 'Instrucciones para restablecer tu contraseña',
                'module' => 'core',
                'description' => 'Email enviado cuando un usuario solicita restablecer su contraseña',
                'layout_id' => null, // Usa el sistema automático de header/footer
                'is_enabled' => true,
                'variables' => null,
                'content' => $this->getResetPasswordContent(),
            ]
        );

        $this->command->info("✓ Plantilla de ejemplo creada: Reset Password (ID: {$template->id})");
    }

    /**
     * Contenido de la plantilla de reset password
     */
    private function getResetPasswordContent(): string
    {
        return <<<'HTML'
<tr>
    <td class="bb-content bb-pb-0" align="center">
        <table class="bb-icon bb-icon-lg bb-bg-blue" cellspacing="0" cellpadding="0">
            <tbody>
            <tr>
                <td valign="middle" align="center">
                    <img src="https://cdn-icons-png.flaticon.com/512/6195/6195699.png" class="bb-va-middle" width="40" height="40" alt="Icono">
                </td>
            </tr>
            </tbody>
        </table>

        <h1 class="bb-text-center bb-m-0 bb-mt-md">Instrucciones para restablecer la contraseña</h1>
    </td>
</tr>
<tr>
    <td class="bb-content bb-text-center">
        <p>Hola {CUSTOMER_NAME},</p>
        <p>Recibirá este correo electrónico porque recibimos una solicitud de restablecimiento de contraseña para su cuenta.</p>
    </td>
</tr>
<tr>
    <td class="bb-content bb-text-center bb-pt-0 bb-pb-xl">
        <table cellspacing="0" cellpadding="0">
            <tbody>
                <tr>
                    <td align="center">
                        <table cellpadding="0" cellspacing="0" border="0" class="bb-bg-blue bb-rounded bb-w-auto">
                            <tbody>
                            <tr>
                                <td align="center" valign="top" class="lh-1">
                                    <a href="{RESET_LINK}" class="bb-btn bb-bg-blue bb-border-blue">
                                        <span class="btn-span">Restablecer contraseña</span>
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </td>
</tr>
<tr>
    <td class="bb-content bb-text-muted bb-pt-0 bb-text-center">
        <p style="font-size: 14px; color: #6b7280; line-height: 1.6;">
            Si tiene problemas para hacer clic en el botón "Restablecer contraseña", copie y pegue la siguiente URL en su navegador web:
        </p>
        <p style="font-size: 13px; word-break: break-all;">
            <a href="{RESET_LINK}" style="color: #4F46E5;">{RESET_LINK}</a>
        </p>
        <p style="font-size: 14px; color: #6b7280; margin-top: 20px;">
            Si no solicitó un restablecimiento de contraseña, ignore este mensaje o contáctenos si tiene alguna pregunta.
        </p>
    </td>
</tr>
HTML;
    }

    /**
     * Contenido del Header basado en el diseño de Mercosan
     */
    private function getHeaderContent(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{EMAIL_SUBJECT}</title>
    <style>
        /* Reset básico */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }

        /* Variables de color */
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f7fa;
        }

        /* Contenedor principal */
        .bb-main-content {
            width: 100%;
            background-color: #f4f7fa;
            padding: 20px 0;
        }

        .bb-box {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .bb-content {
            padding: 30px 40px;
        }

        .bb-pb-0 { padding-bottom: 0 !important; }
        .bb-pt-0 { padding-top: 0 !important; }
        .bb-pb-xl { padding-bottom: 40px !important; }
        .bb-mt-md { margin-top: 20px !important; }
        .bb-m-0 { margin: 0 !important; }

        /* Iconos */
        .bb-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 20px;
        }

        .bb-icon-lg {
            width: 80px;
            height: 80px;
        }

        .bb-bg-blue {
            background-color: #4F46E5;
        }

        .bb-va-middle {
            vertical-align: middle;
        }

        /* Tipografía */
        h1 {
            font-size: 24px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 16px;
            line-height: 1.3;
        }

        p {
            font-size: 16px;
            color: #4b5563;
            line-height: 1.6;
            margin: 0 0 16px;
        }

        .bb-text-center {
            text-align: center;
        }

        .bb-text-muted {
            color: #6b7280;
            font-size: 14px;
        }

        /* Botones */
        .bb-btn {
            display: inline-block;
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            color: #ffffff !important;
            border-radius: 6px;
            text-align: center;
        }

        .bb-border-blue {
            border: 2px solid #4F46E5;
        }

        .bb-rounded {
            border-radius: 6px;
        }

        .bb-w-auto {
            width: auto;
        }

        .btn-span {
            color: #ffffff;
        }

        .lh-1 {
            line-height: 1;
        }

        /* Logo Header */
        .bb-header {
            background-color: #1f2937;
            padding: 20px 40px;
            text-align: center;
        }

        .bb-logo {
            max-width: 180px;
            height: auto;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .bb-box {
                width: 100% !important;
                border-radius: 0 !important;
            }

            .bb-content {
                padding: 20px !important;
            }

            h1 {
                font-size: 20px !important;
            }

            .bb-btn {
                padding: 12px 24px !important;
                font-size: 14px !important;
            }
        }
    </style>
</head>
<body>
    <table class="bb-main-content" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">
                <table class="bb-box" cellpadding="0" cellspacing="0">
                    <!-- Header con Logo (Opcional) -->
                    <tr>
                        <td class="bb-header">
                            <img src="{LOGO_URL}" alt="{SITE_NAME}" class="bb-logo">
                        </td>
                    </tr>
HTML;
    }

    /**
     * Contenido del Footer basado en el diseño de Mercosan
     */
    private function getFooterContent(): string
    {
        return <<<'HTML'
                    <!-- Footer -->
                    <tr>
                        <td class="bb-content" style="background-color: #f9fafb; border-top: 1px solid #e5e7eb; padding: 30px 40px; text-align: center;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        <p style="margin: 0; font-size: 14px; color: #6b7280; line-height: 1.5;">
                                            <strong>{SITE_NAME}</strong><br>
                                            {COMPANY_ADDRESS}<br>
                                            {COMPANY_CITY}, {COMPANY_COUNTRY}
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        <p style="margin: 0; font-size: 14px; color: #6b7280;">
                                            Teléfono: {COMPANY_PHONE} | Email: {COMPANY_EMAIL}
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border-top: 1px solid #e5e7eb; padding-top: 20px;">
                                        <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                            © {CURRENT_YEAR} {SITE_NAME}. Todos los derechos reservados.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 15px;">
                                        <p style="margin: 0; font-size: 11px; color: #9ca3af; line-height: 1.5;">
                                            Este correo fue enviado a {RECIPIENT_EMAIL}. Si tienes preguntas,
                                            por favor contacta a nuestro equipo de soporte.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    /**
     * Contenido del Wrapper completo (header + content + footer)
     */
    private function getWrapperContent(): string
    {
        return <<<'HTML'
{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
            {CONTENT}
        </tbody>
    </table>
</div>

{{ footer }}
HTML;
    }
}
