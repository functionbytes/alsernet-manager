<?php

namespace Database\Seeders;

use App\Models\Helpdesk\CampaignTemplate;
use Illuminate\Database\Seeder;

class CampaignTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Bienvenida Simple',
                'description' => 'Mensaje de bienvenida limpio y profesional para nuevos visitantes',
                'category' => 'announcement',
                'type' => 'popup',
                'preview_gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'is_premium' => false,
                'content' => [
                    [
                        'type' => 'heading',
                        'value' => '¡Bienvenido!',
                        'level' => 'h2',
                    ],
                    [
                        'type' => 'text',
                        'value' => 'Gracias por visitarnos. ¿En qué podemos ayudarte hoy?',
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Comenzar',
                        'url' => '#',
                        'style' => 'primary',
                    ],
                ],
                'appearance' => [
                    'background_color' => '#ffffff',
                    'text_color' => '#000000',
                    'primary_color' => '#667eea',
                    'font_size' => 'medium',
                    'position' => 'center',
                    'max_width' => 500,
                    'border_radius' => 12,
                    'padding' => 30,
                ],
                'conditions' => [
                    [
                        'field' => 'visitor_type',
                        'operator' => 'equals',
                        'value' => 'new',
                    ],
                ],
            ],
            [
                'name' => 'Descuento Flash',
                'description' => 'Promoción urgente con temporizador para aumentar conversiones',
                'category' => 'promotion',
                'type' => 'banner',
                'preview_gradient' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                'is_premium' => false,
                'content' => [
                    [
                        'type' => 'heading',
                        'value' => '🔥 ¡Oferta Flash! 30% de Descuento',
                        'level' => 'h3',
                    ],
                    [
                        'type' => 'text',
                        'value' => 'Solo por tiempo limitado. Usa el código: FLASH30',
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Ver Ofertas',
                        'url' => '/ofertas',
                        'style' => 'danger',
                    ],
                ],
                'appearance' => [
                    'background_color' => '#f5576c',
                    'text_color' => '#ffffff',
                    'primary_color' => '#ffffff',
                    'font_size' => 'medium',
                    'position' => 'top-center',
                    'max_width' => 1200,
                    'border_radius' => 0,
                    'padding' => 15,
                ],
                'conditions' => [],
            ],
            [
                'name' => 'Captura de Email',
                'description' => 'Formulario simple para suscripción a newsletter',
                'category' => 'newsletter',
                'type' => 'slide-in',
                'preview_gradient' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
                'is_premium' => false,
                'content' => [
                    [
                        'type' => 'heading',
                        'value' => 'Mantente Informado',
                        'level' => 'h3',
                    ],
                    [
                        'type' => 'text',
                        'value' => 'Recibe nuestras mejores ofertas y novedades directamente en tu correo.',
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Suscribirme',
                        'url' => '#newsletter',
                        'style' => 'success',
                    ],
                ],
                'appearance' => [
                    'background_color' => '#ffffff',
                    'text_color' => '#333333',
                    'primary_color' => '#13C672',
                    'font_size' => 'medium',
                    'position' => 'bottom-right',
                    'max_width' => 400,
                    'border_radius' => 12,
                    'padding' => 25,
                ],
                'conditions' => [
                    [
                        'field' => 'time_on_site',
                        'operator' => 'greater_than',
                        'value' => '30',
                    ],
                ],
            ],
            [
                'name' => 'Encuesta de Satisfacción',
                'description' => 'Recopila feedback de tus usuarios de manera efectiva',
                'category' => 'survey',
                'type' => 'popup',
                'preview_gradient' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                'is_premium' => false,
                'content' => [
                    [
                        'type' => 'heading',
                        'value' => '¿Cómo fue tu experiencia?',
                        'level' => 'h3',
                    ],
                    [
                        'type' => 'text',
                        'value' => 'Tu opinión nos ayuda a mejorar. Solo tomará 1 minuto.',
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Responder Encuesta',
                        'url' => '/encuesta',
                        'style' => 'primary',
                    ],
                ],
                'appearance' => [
                    'background_color' => '#ffffff',
                    'text_color' => '#000000',
                    'primary_color' => '#4facfe',
                    'font_size' => 'medium',
                    'position' => 'center',
                    'max_width' => 550,
                    'border_radius' => 16,
                    'padding' => 35,
                ],
                'conditions' => [
                    [
                        'field' => 'visit_count',
                        'operator' => 'greater_than',
                        'value' => '3',
                    ],
                ],
            ],
            [
                'name' => 'Anuncio Premium',
                'description' => 'Diseño elegante para anuncios importantes con imagen de fondo',
                'category' => 'announcement',
                'type' => 'full-screen',
                'preview_gradient' => 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
                'is_premium' => true,
                'content' => [
                    [
                        'type' => 'heading',
                        'value' => 'Gran Lanzamiento',
                        'level' => 'h1',
                    ],
                    [
                        'type' => 'text',
                        'value' => 'Presentamos nuestra nueva línea de productos premium. Disponible ahora.',
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Explorar Ahora',
                        'url' => '/nuevo',
                        'style' => 'primary',
                    ],
                ],
                'appearance' => [
                    'background_color' => '#1a1a1a',
                    'text_color' => '#ffffff',
                    'primary_color' => '#90bb13',
                    'font_size' => 'large',
                    'position' => 'center',
                    'max_width' => 800,
                    'border_radius' => 20,
                    'padding' => 50,
                ],
                'conditions' => [],
            ],
            [
                'name' => 'Intención de Salida',
                'description' => 'Captura visitantes antes de que abandonen tu sitio',
                'category' => 'custom',
                'type' => 'popup',
                'preview_gradient' => 'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)',
                'is_premium' => false,
                'content' => [
                    [
                        'type' => 'heading',
                        'value' => '¡Espera! No te vayas aún',
                        'level' => 'h2',
                    ],
                    [
                        'type' => 'text',
                        'value' => 'Obtén un 15% de descuento en tu primera compra. Solo por hoy.',
                    ],
                    [
                        'type' => 'button',
                        'label' => 'Reclamar Descuento',
                        'url' => '#discount',
                        'style' => 'success',
                    ],
                ],
                'appearance' => [
                    'background_color' => '#ffffff',
                    'text_color' => '#333333',
                    'primary_color' => '#ff9a9e',
                    'font_size' => 'medium',
                    'position' => 'center',
                    'max_width' => 600,
                    'border_radius' => 12,
                    'padding' => 30,
                ],
                'conditions' => [
                    [
                        'field' => 'exit_intent',
                        'operator' => 'equals',
                        'value' => 'true',
                    ],
                ],
            ],
        ];

        foreach ($templates as $template) {
            CampaignTemplate::create($template);
        }

        $this->command->info('✓ Created '.count($templates).' campaign templates');
    }
}
