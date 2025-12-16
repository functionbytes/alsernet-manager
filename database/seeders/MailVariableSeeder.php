<?php

namespace Database\Seeders;

use App\Models\Lang;
use App\Models\Mail\MailVariable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MailVariableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $langs = Lang::all();

        if ($langs->isEmpty()) {
            $this->command->warn('No language found in database. Please create at least one language first.');

            return;
        }

        $variables = [
            // System Variables
            [
                'key' => 'COMPANY_NAME',
                'name' => 'Nombre de la Empresa',
                'description' => 'Nombre oficial de la empresa',
                'example_value' => 'Alsernet S.L.',
                'category' => 'system',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'SITE_NAME',
                'name' => 'Nombre del Sitio',
                'description' => 'Nombre del sitio web',
                'example_value' => 'Mi Tienda Online',
                'category' => 'system',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'SITE_URL',
                'name' => 'URL del Sitio',
                'description' => 'Dirección web del sitio',
                'example_value' => 'https://www.mitienraonline.com',
                'category' => 'system',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'SUPPORT_EMAIL',
                'name' => 'Email de Soporte',
                'description' => 'Correo electrónico de soporte al cliente',
                'example_value' => 'soporte@mitienraonline.com',
                'category' => 'system',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'SUPPORT_PHONE',
                'name' => 'Teléfono de Soporte',
                'description' => 'Número de teléfono de soporte',
                'example_value' => '+34 900 123 456',
                'category' => 'system',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'CONTACT_EMAIL',
                'name' => 'Email de Contacto',
                'description' => 'Correo electrónico general de contacto',
                'example_value' => 'contacto@mitienraonline.com',
                'category' => 'system',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'CURRENT_YEAR',
                'name' => 'Año Actual',
                'description' => 'Año actual del sistema',
                'example_value' => '2025',
                'category' => 'system',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'CURRENT_DATE',
                'name' => 'Fecha Actual',
                'description' => 'Fecha actual en formato dd/mm/yyyy',
                'example_value' => '15/12/2025',
                'category' => 'system',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'CURRENT_DATETIME',
                'name' => 'Fecha y Hora Actual',
                'description' => 'Fecha y hora actual en formato dd/mm/yyyy HH:ii',
                'example_value' => '15/12/2025 14:30',
                'category' => 'system',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'LANG_CODE',
                'name' => 'Código de Idioma',
                'description' => 'Código del idioma actual (ej: es, en)',
                'example_value' => 'es',
                'category' => 'system',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],

            // Customer Variables
            [
                'key' => 'CUSTOMER_NAME',
                'name' => 'Nombre del Cliente',
                'description' => 'Nombre completo del cliente',
                'example_value' => 'Juan García López',
                'category' => 'customer',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'CUSTOMER_FIRSTNAME',
                'name' => 'Nombre (Pila)',
                'description' => 'Nombre de pila del cliente',
                'example_value' => 'Juan',
                'category' => 'customer',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'CUSTOMER_LASTNAME',
                'name' => 'Apellido',
                'description' => 'Apellido del cliente',
                'example_value' => 'García López',
                'category' => 'customer',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'CUSTOMER_EMAIL',
                'name' => 'Email del Cliente',
                'description' => 'Dirección de correo electrónico del cliente',
                'example_value' => 'juan.garcia@example.com',
                'category' => 'customer',
                'module' => 'core',
                'is_system' => true,
                'is_enabled' => true,
            ],

            // Order Variables
            [
                'key' => 'ORDER_ID',
                'name' => 'ID del Pedido',
                'description' => 'Identificador único del pedido',
                'example_value' => '12345',
                'category' => 'order',
                'module' => 'orders',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'ORDER_REFERENCE',
                'name' => 'Referencia del Pedido',
                'description' => 'Número de referencia del pedido',
                'example_value' => 'PED-2025-001234',
                'category' => 'order',
                'module' => 'orders',
                'is_system' => true,
                'is_enabled' => true,
            ],

            // Document Variables
            [
                'key' => 'DOCUMENT_TYPE',
                'name' => 'Tipo de Documento',
                'description' => 'Código del tipo de documento',
                'example_value' => 'identity_document',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'DOCUMENT_TYPE_LABEL',
                'name' => 'Etiqueta del Tipo de Documento',
                'description' => 'Descripción legible del tipo de documento',
                'example_value' => 'Documento de Identidad',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'DOCUMENT_INSTRUCTIONS',
                'name' => 'Instrucciones del Documento',
                'description' => 'Instrucciones sobre cómo proceder con el documento',
                'example_value' => 'Por favor, cargue una copia clara de su documento de identidad (DNI, pasaporte o carnet de conducir)',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'DOCUMENT_UID',
                'name' => 'UID del Documento',
                'description' => 'Identificador único del documento',
                'example_value' => '68eaa99c-1508-4...',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'UPLOAD_LINK',
                'name' => 'Enlace de Carga',
                'description' => 'URL para cargar documentos',
                'example_value' => 'https://www.mitienraonline.com/upload/68eaa99c',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'UPLOAD_URL',
                'name' => 'URL de Carga',
                'description' => 'URL del portal de carga',
                'example_value' => 'https://www.mitienraonline.com/upload/68eaa99c',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'EXPIRATION_DATE',
                'name' => 'Fecha de Vencimiento',
                'description' => 'Fecha límite para cargar documentos',
                'example_value' => '18/12/2025',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'DEADLINE',
                'name' => 'Plazo',
                'description' => 'Fecha de vencimiento del plazo',
                'example_value' => '18/12/2025',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'MISSING_DOCUMENTS',
                'name' => 'Documentos Faltantes',
                'description' => 'Lista de documentos que faltan',
                'example_value' => '<ul><li>Documento de Identidad</li><li>Comprobante de Domicilio</li></ul>',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'MISSING_DOCUMENTS_LIST',
                'name' => 'Lista de Documentos Faltantes',
                'description' => 'Lista HTML de documentos faltantes',
                'example_value' => '<ul><li>Documento de Identidad</li><li>Comprobante de Domicilio</li></ul>',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'REQUIRED_DOCUMENTS_LIST',
                'name' => 'Lista de Documentos Requeridos',
                'description' => 'Lista HTML de documentos requeridos',
                'example_value' => '<ul><li>Documento de Identidad</li><li>Comprobante de Domicilio</li></ul>',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'NOTES',
                'name' => 'Notas',
                'description' => 'Notas adicionales sobre el documento',
                'example_value' => 'Por favor, asegúrese de que los documentos sean legibles y estén en formato JPG o PDF',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'NOTES_SECTION',
                'name' => 'Sección de Notas',
                'description' => 'Sección HTML con notas adicionales',
                'example_value' => '<div style="background-color: #f3f4f6;">Nota adicional: Por favor revise los requisitos cuidadosamente</div>',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
            [
                'key' => 'REQUEST_REASON',
                'name' => 'Razón de la Solicitud',
                'description' => 'Razón por la que se solicitan documentos',
                'example_value' => 'Necesitamos verificar su identidad para completar el proceso de registro',
                'category' => 'document',
                'module' => 'documents',
                'is_system' => true,
                'is_enabled' => true,
            ],
        ];

        foreach ($variables as $variableData) {
            $variable = MailVariable::firstOrCreate(
                ['key' => $variableData['key']],
                array_merge($variableData, ['uid' => (string) Str::uuid()])
            );

            // Create translations for each language
            foreach ($langs as $lang) {
                $variable->translations()->updateOrCreate(
                    ['lang_id' => $lang->id],
                    [
                        'uid' => (string) Str::uuid(),
                        'name' => $variableData['name'],
                        'description' => $variableData['description'],
                    ]
                );
            }
        }

        $this->command->info('✓ Mail variables seeded successfully');
    }
}
