<?php

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (! defined('_PS_VERSION_')) {
    exit;
}

class Alsernetforms extends Module implements WidgetInterface
{
    public function __construct()
    {

        $this->name = 'alsernetforms';
        $this->author = 'Alsernet';
        $this->version = '1.0.0';
        $this->need_instance = 0;

        parent::__construct();

        $this->controllers = ['verification', 'unsubscribe'];

        $this->displayName = 'Alsernet - Formularios ';
        $this->description = $this->getTranslator()->trans('Make your customers feel at home on your store, invite them to sign in!', [], 'modules.Customersignin.Admin');
        $this->ps_versions_compliancy = ['min' => '1.7.1.0', 'max' => _PS_VERSION_];

    }

    public function install()
    {

        return parent::install() && $this->installDB() && $this->registerHook('displayBeforeBodyClosingTag') && $this->registerHook('displayHome') && $this->registerHook('header');

    }

    private function installDB()
    {
        // Table for storing forms
        $sql1 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'alsernet_forms` (
            `id_form` INT(11) NOT NULL AUTO_INCREMENT,
            `data` JSON NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id_form`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        // Table for storing newsletter subscriptions
        $sql2 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'alsernet_forms_newsletter` (
            `id_susc_newsletter` INT(11) NOT NULL AUTO_INCREMENT,
            `firstname` VARCHAR(255) NOT NULL,
            `lastname` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `ids_sports` VARCHAR(255) NOT NULL,  -- List of sport IDs
            `erp` TINYINT(1) NOT NULL DEFAULT 0,
            `lopd` TINYINT(1) NOT NULL DEFAULT 0,
            `none` TINYINT(1) NOT NULL DEFAULT 0,
            `sports` TINYINT(1) NOT NULL DEFAULT 0,
            `parties` TINYINT(1) NOT NULL DEFAULT 0,
            `subscribe` TINYINT(1) NULL DEFAULT 0,
            `check` TINYINT(1) NULL DEFAULT 0,
            `id_lang` INT(11) NULL,
            `check_at` DATETIME NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY (`id_susc_newsletter`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        // Table for tracking changes to subscription preferences
        $sql3 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'alsernet_forms_newsletter_history` (
            `id_history` INT(11) NOT NULL AUTO_INCREMENT,
            `id_susc_newsletter` INT(11) NOT NULL,
            `action_type` ENUM(\'none\', \'parties\', \'sports\', \'subscribe\', \'check\') NOT NULL,
            `old_value` TINYINT(1) NOT NULL,
            `new_value` TINYINT(1) NOT NULL,
            `synced_at` DATETIME NULL,
            `changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_history`),
            FOREIGN KEY (`id_susc_newsletter`) REFERENCES `'._DB_PREFIX_.'alsernet_forms_newsletter`(`id_susc_newsletter`)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        // Table for storing subscription job details
        $sql4 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'alsernet_forms_newsletter_jobs` (
            `id_jobs` INT(11) NOT NULL AUTO_INCREMENT,
            `id_history` INT(11) NOT NULL,
            `action_type` ENUM(\'none\', \'parties\', \'sports\', \'subscribe\', \'check\') NOT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`id_jobs`),
            FOREIGN KEY (`id_history`) REFERENCES `'._DB_PREFIX_.'alsernet_forms_newsletter_history`(`id_history`)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        // Table for storing form lists
        $sql5 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'alsernet_forms_newsletter_lists` (
            `id_list` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,  -- List name (white, black, etc.)
            `description` TEXT NULL,       -- List description
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_list`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        // Table for associating users with lists
        $sql6 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'alsernet_forms_newsletter_user_lists` (
            `id_user_list` INT(11) NOT NULL AUTO_INCREMENT,
            `id_susc_newsletter` INT(11) NOT NULL,
            `id_list` INT(11) NOT NULL,
            `added_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_user_list`),
            FOREIGN KEY (`id_susc_newsletter`) REFERENCES `'._DB_PREFIX_.'alsernet_forms_newsletter`(`id_susc_newsletter`)
                ON DELETE CASCADE
                ON UPDATE CASCADE,
            FOREIGN KEY (`id_list`) REFERENCES `'._DB_PREFIX_.'alsernet_forms_newsletter_lists`(`id_list`)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        // Table for storing API endpoint requests and their status
        $sql7 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'alsernet_forms_requests` (
            `id_alsernetforms_request` INT(11) NOT NULL AUTO_INCREMENT,
            `endpoint_type` VARCHAR(50) NOT NULL DEFAULT \'default\',
            `method` VARCHAR(10) NOT NULL,
            `url` TEXT NOT NULL,
            `payload` LONGTEXT NULL,
            `response` LONGTEXT NULL,
            `status` ENUM(\'pending\', \'processing\', \'success\', \'failed\', \'server_unavailable\') NOT NULL DEFAULT \'pending\',
            `retry_count` INT(11) NOT NULL DEFAULT 0,
            `max_retries` INT(11) NOT NULL DEFAULT 3,
            `last_error` TEXT NULL,
            `created_at` DATETIME NOT NULL,
            `synced_at` DATETIME NULL,
            `next_retry_at` DATETIME NULL,
            PRIMARY KEY (`id_alsernetforms_request`),
            INDEX `idx_status` (`status`),
            INDEX `idx_endpoint_type` (`endpoint_type`),
            INDEX `idx_next_retry` (`next_retry_at`),
            INDEX `idx_created_at` (`created_at`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        // Table for tracking endpoint availability
        $sql8 = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'alsernet_endpoint_health` (
            `id_endpoint_health` INT(11) NOT NULL AUTO_INCREMENT,
            `endpoint_url` VARCHAR(255) NOT NULL,
            `endpoint_type` VARCHAR(50) NOT NULL,
            `is_available` TINYINT(1) NOT NULL DEFAULT 1,
            `last_check_at` DATETIME NOT NULL,
            `last_success_at` DATETIME NULL,
            `last_failure_at` DATETIME NULL,
            `consecutive_failures` INT(11) NOT NULL DEFAULT 0,
            `response_time_ms` INT(11) NULL,
            PRIMARY KEY (`id_endpoint_health`),
            UNIQUE KEY `unique_endpoint` (`endpoint_url`, `endpoint_type`),
            INDEX `idx_availability` (`is_available`),
            INDEX `idx_last_check` (`last_check_at`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

        // Execute all queries
        return Db::getInstance()->execute($sql1)
            && Db::getInstance()->execute($sql2)
            && Db::getInstance()->execute($sql3)
            && Db::getInstance()->execute($sql4)
            && Db::getInstance()->execute($sql5)
            && Db::getInstance()->execute($sql6)
            && Db::getInstance()->execute($sql7)
            && Db::getInstance()->execute($sql8);
    }

    public function uninstall()
    {
        // Ensure uninstall DB logic is called and the module can be removed
        if (! parent::uninstall() || ! $this->uninstallDB()) {
            return false;
        }

        return true;
    }

    private function uninstallDB()
    {
        // Drop tables in reverse order of dependencies to avoid foreign key issues
        $sql8 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'alsernet_endpoint_health`;';
        $sql7 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'alsernet_forms_requests`;';
        $sql6 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'alsernet_forms_newsletter_user_lists`;';
        $sql5 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'alsernet_forms_newsletter_lists`;';
        $sql4 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'alsernet_forms_newsletter_jobs`;';
        $sql3 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'alsernet_forms_newsletter_history`;';
        $sql2 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'alsernet_forms_newsletter`;';
        $sql1 = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'alsernet_forms`;';

        // Execute the queries to drop the tables
        return Db::getInstance()->execute($sql1)
            && Db::getInstance()->execute($sql2)
            && Db::getInstance()->execute($sql3)
            && Db::getInstance()->execute($sql4)
            && Db::getInstance()->execute($sql5)
            && Db::getInstance()->execute($sql6)
            && Db::getInstance()->execute($sql7)
            && Db::getInstance()->execute($sql8);
    }

    public function getWidgetVariablesAuth($hookName, array $configuration)
    {

        $logged = $this->context->customer->isLogged();
        $link = $this->context->link;

        return [
            'logged' => $logged,
            'links' => $logged ? $link->getPageLink('my-account', true) : $link->getPageLink('iniciar-sesion', true),
        ];
    }

    public function getWidgetVariables($hookName, array $configuration) {}

    public function renderWidget($hookName = null, array $configuration = [])
    {

        if (isset($configuration['forms'])) { // Aquí debe coincidir con "forms"
            switch ($configuration['forms']) {
                case 'fitting':
                    $this->smarty->assign([// 'language' => $this->context->language,
                    ]);

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/fitting.tpl');
                case 'demoday':
                    $this->smarty->assign([// 'language' => $this->context->language,
                    ]);

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/demoday.tpl');
                case 'compromise':

                    $this->smarty->assign([// 'language' => $this->context->language,
                    ]);

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/compromise.tpl');
                case 'demodayorder':
                    $this->smarty->assign([// 'language' => $this->context->language,
                    ]);

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/demodayorder.tpl');
                case 'huntinginsurance':
                    $this->smarty->assign([// 'language' => $this->context->language,
                    ]);

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/huntinginsurance.tpl');
                case 'golfdiagnosis':
                    $this->smarty->assign([// 'language' => $this->context->language,
                    ]);

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/golfdiagnosis.tpl');
                case 'gunsmithworkshop':
                    $this->smarty->assign([// 'language' => $this->context->language,
                    ]);

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/gunsmithworkshop.tpl');
                case 'divingpackages':
                    $this->smarty->assign([// 'language' => $this->context->language,
                    ]);

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/divingpackages.tpl');

                case 'exchangesandreturns':

                    /*$sports = $this->getSportsByIdsAndTranslateNew();

                    $this->smarty->assign(array(
                        'sports' => $sports,
                    ));*/

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/exchangesandreturns.tpl');

                case 'paymentandfinancing':

                    $this->smarty->assign([// 'language' => $this->context->language,
                    ]);

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/paymentandfinancing.tpl');
                case 'shipping':

                    $this->smarty->assign([// 'language' => $this->context->language,
                    ]);

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/shipping.tpl');

                case 'newslettersubscribe':

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/subscribers/subscribe.tpl');

                case 'newsletterdischargerssports':

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/discharges/sports.tpl');

                case 'newsletterdischargersnone':

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/discharges/none.tpl');

                case 'newsletterdischargersparties':

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/discharges/parties.tpl');

                case 'wecallyouus':

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/wecallyouus.tpl');

                case 'internalinformationsystem':

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/internalinformationsystem.tpl');

                case 'giftvoucher':

                    /*$sports = $this->getSportsByIdsAndTranslateNew();

                    $this->smarty->assign(array(
                        'sports' => $sports,
                    ));*/

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/campaigns/giftvoucher.tpl');

                case 'customizeyourexperience':

                    /*$sports = $this->getSportsByIdsAndTranslateNew();

                    $this->smarty->assign(array(
                        'sports' => $sports,
                    ));*/

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/campaigns/customizeyourexperience.tpl');

                case 'customeradvocate':

                    /*$sports = $this->getSportsByIdsAndTranslateNew();

                    $this->smarty->assign(array(
                        'sports' => $sports,
                    ));*/

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/customeradvocate.tpl');

                case 'workwithus':

                    /*$sports = $this->getSportsByIdsAndTranslateNew();

                    $this->smarty->assign(array(
                        'sports' => $sports,
                    ));*/

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/workwithus.tpl');

                case 'contact':

                    /*$sports = $this->getSportsByIdsAndTranslateNew();

                    $this->smarty->assign(array(
                        'sports' => $sports,
                    ));*/

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/contact.tpl');

                case 'documents':
                    // ════════════════════════════════════════════════════════════════════════
                    // VALIDACIÓN DE DOCUMENTOS CON RESILIENCIA
                    // ════════════════════════════════════════════════════════════════════════
                    // Este flujo garantiza que aunque el servidor Laravel esté caído,
                    // la petición se guarda en BD y se procesa automáticamente cuando vuelve
                    // ════════════════════════════════════════════════════════════════════════

                    // 1️⃣ EXTRAER UID DEL TOKEN
                    $token = Tools::getValue('token');
                    $uid = strpos($token, '?token=') !== false
                        ? trim(explode('?token=', $token)[1] ?? '')
                        : trim($token);

                    // 2️⃣ OBTENER LA ORDER PARA ACCEDER A document_type
                    // El UID es el document_number del pedido
                    $order = new Order;
                    $orders = $order->getByDocumentNumber($uid); // Obtener orden por document_number

                    if (empty($orders)) {
                        // Si no encuentra por document_number, intentar como reference o ID
                        $order = new Order((int) $uid);
                        if (empty($order->id)) {
                            // Intentar obtener por reference
                            $sql = 'SELECT id_order FROM '._DB_PREFIX_.'orders WHERE reference = "'.pSQL($uid).'" LIMIT 1';
                            $orderId = (int) Db::getInstance()->getValue($sql);
                            if ($orderId) {
                                $order = new Order($orderId);
                            }
                        }
                    } else {
                        $order = reset($orders); // Obtener primer resultado
                    }

                    // 3️⃣ INCLUIR CLASES REQUERIDAS
                    include_once dirname(__FILE__).'/classes/DocumentValidator.php';
                    include_once dirname(__FILE__).'/classes/EndpointAvailabilityChecker.php';

                    // 4️⃣ VERIFICAR DISPONIBILIDAD DEL SERVIDOR ANTES DE VALIDAR
                    $checker = new EndpointAvailabilityChecker;
                    $serverAvailable = $checker->isEndpointAvailable(
                        'https://webadminpruebas.a-alvarez.com/api/health',
                        'documents'
                    );

                    // 5️⃣ MOSTRAR ESTADO DEL SERVIDOR AL USUARIO (para debugging)
                    $serverStatus = $serverAvailable['available']
                        ? '✅ Servidor disponible'
                        : '⏳ Servidor no disponible: '.($serverAvailable['reason'] ?? 'Unknown');

                    // 6️⃣ OBTENER TIPO DE DOCUMENTO DE LA ORDEN
                    // El document_type YA ESTÁ guardado en la Order
                    $documentType = ! empty($order->id) && ! empty($order->document_type)
                        ? $order->document_type
                        : 'dni'; // Fallback a 'dni' si no se encuentra

                    // 7️⃣ VALIDAR DOCUMENTOS CON CIRCUIT BREAKER PATTERN
                    // Si el servidor está caído:
                    //   - NO envía petición al servidor
                    //   - Guarda en BD con status='pending'
                    //   - Cron procesará cuando vuelva
                    // Si el servidor está disponible:
                    //   - Valida inmediatamente
                    //   - Retorna resultado completo
                    $validator = new DocumentValidator;
                    $validation = $validator->validateDocuments(
                        $uid,
                        $documentType,
                        [
                            'customer_id' => $this->context->customer->id ?? null,
                            'order_id' => ! empty($order->id) ? $order->id : null,
                            'order_reference' => ! empty($order->reference) ? $order->reference : $uid,
                            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                        ]
                    );

                    // 8️⃣ GENERAR TRADUCCIONES
                    [$trans_remember, $trans_list] = $this->generateDocumentListOnly($uid, $validation['type']);

                    // 9️⃣ ASIGNAR VARIABLES A TEMPLATE
                    $this->context->smarty->assign([
                        'uid' => $uid,
                        'trans' => $trans_remember,
                        'trans_list' => $trans_list,
                        'label' => $validation['label'],
                        'status' => $validation['status'],  // 'success' | 'pending' | 'error'
                        'type' => $validation['type'],
                        'upload' => $validation['upload'],  // true si puede subir, false si está pendiente
                        'required_documents' => $validation['data']['required_documents'] ?? [],
                        'uploaded_documents' => $validation['data']['uploaded_documents'] ?? [],
                        'missing_documents' => $validation['data']['missing_documents'] ?? [],
                    ]);

                    // 9️⃣ SI SERVIDOR NO DISPONIBLE: MOSTRAR MENSAJE ESPECIAL
                    if ($validation['status'] === 'pending') {
                        $this->context->smarty->assign([
                            'server_unavailable' => true,
                            'server_status' => $serverStatus,
                            'pending_message' => $validation['message'],
                            'request_id' => $validation['request_id'] ?? null,
                            'reason' => $validation['reason'] ?? 'Server unavailable',
                            'retry_info' => [
                                'saved_at' => date('Y-m-d H:i:s'),
                                'will_retry_at' => $validation['next_retry_at'] ?? date('Y-m-d H:i:s', time() + 300),
                            ],
                        ]);

                        // LOG: Registrar que se guardó como pendiente
                        PrestaShopLogger::addLog(
                            "DocumentValidator: Request queued for UID {$uid}. Request ID: {$validation['request_id']}",
                            1,  // Info
                            null,
                            'alsernetforms',
                            $validation['request_id'] ?? null,
                            true
                        );
                    } elseif ($validation['status'] === 'error') {
                        // ❌ ERROR: Mostrar mensaje de error
                        $this->context->smarty->assign([
                            'validation_error' => true,
                            'error_message' => $validation['message'],
                            'error_details' => $validation['error'] ?? null,
                        ]);

                        // LOG: Registrar error
                        PrestaShopLogger::addLog(
                            "DocumentValidator: Error for UID {$uid}: {$validation['error']}",
                            3,  // Error
                            null,
                            'alsernetforms'
                        );
                    } else {
                        // ✅ SUCCESS: Validación completada
                        $this->context->smarty->assign([
                            'validation_success' => true,
                            'server_status' => $serverStatus,
                        ]);
                    }

                    return $this->fetch('module:alsernetforms/views/templates/hook/forms/documents/document.tpl');

                default:
                    break;
            }
        }

        return ''; // Devuelve algo Por defecto si no se cumplen las condiciones
    }

    public function generateDocumentHtml($documentNumber, $documentType)
    {
        if (empty($documentNumber) || empty($documentType)) {
            return '';
        }

        $iso = Context::getContext()->language->iso_code;
        $link = Context::getContext()->link;
        $documents_url = $link->getCMSLink(136).'?token='.urlencode($documentNumber);

        // Switch según tipo de arma
        switch ($documentType) {
            case 'corta':
                $trans_remember = strtr(
                    $this->l(
                        '[b]REMEMBER:[/b] In order to ship your firearm, we need you to send us the following documentation:',
                        'alsernetforms',
                        $iso
                    ),
                    ['[b]' => '<strong>', '[/b]' => '</strong>']
                );

                $trans_list = '<ul style="padding-left: 20px; margin: 8px 0;">'
                    .'<li>'.$this->l('A photocopy of your ID (both sides)', 'alsernetforms', $iso).'</li>'
                    .'<li>'.$this->l('A photocopy of your handgun permit (type B) or Olympic shooting permit (type F)', 'alsernetforms', $iso).'</li>'
                    .'</ul>';
                break;

            case 'rifle':
                $trans_remember = strtr(
                    $this->l(
                        '[b]REMEMBER:[/b] In order to ship your firearm, we need you to send us the following documentation:',
                        'alsernetforms',
                        $iso
                    ),
                    ['[b]' => '<strong>', '[/b]' => '</strong>']
                );

                $trans_list = '<ul style="padding-left: 20px; margin: 8px 0;">'
                    .'<li>'.$this->l('A photocopy of your ID (both sides)', 'alsernetforms', $iso).'</li>'
                    .'<li>'.$this->l('A photocopy of your rifled long-range firearm permit (type D)', 'alsernetforms', $iso).'</li>'
                    .'</ul>';
                break;

            case 'escopeta':
                $trans_remember = strtr(
                    $this->l(
                        '[b]REMEMBER:[/b] In order to ship your weapon, we need you to send us the following documentation:',
                        'alsernetforms',
                        $iso
                    ),
                    ['[b]' => '<strong>', '[/b]' => '</strong>']
                );

                $trans_list = '<ul style="padding-left: 20px; margin: 8px 0;">'
                    .'<li>'.$this->l('A photocopy of your ID (both sides)', 'alsernetforms', $iso).'</li>'
                    .'<li>'.$this->l('A photocopy of a shotgun license (type E)', 'alsernetforms', $iso).'</li>'
                    .'</ul>';
                break;

            case 'dni':
                $trans_remember = strtr(
                    $this->l(
                        '[b]REMEMBER:[/b] In order to process your BB gun order, you must send us a.',
                        'alsernetforms',
                        $iso
                    ),
                    ['[b]' => '<strong>', '[/b]' => '</strong>']
                );

                $trans_list = '<ul style="padding-left: 20px; margin: 8px 0;">'
                    .'<li>'.$this->l('A photocopy of your ID (both sides)', 'alsernetforms', $iso).'</li>'
                    .'</ul>';
                break;

            default:
                $trans_remember = strtr(
                    $this->l(
                        '[b]REMEMBER:[/b] In order to ship your air rifle, you must provide us with a copy of your passport or driving licence (both sides if it\'s a card).',
                        'alsernetforms',
                        $iso
                    ),
                    ['[b]' => '<strong>', '[/b]' => '</strong>']
                );
                $trans_list = '';
                break;
        }

        // Traducciones adicionales
        $trans_instruction = $this->l('Please click on the following link and follow the instructions:', 'alsernetforms', $iso);
        $trans_upload = $this->l('Upload documentation', 'alsernetforms', $iso);
        $trans_whatsapp = $this->l('You can also send us the documentation via WhatsApp to the number: 617362506', 'alsernetforms', $iso);

        // Generar HTML final
        $template = '
            <div style="margin: 20px 0; padding: 15px; background-color: #f5f5f5; border-left: 4px solid #90bb13;">
                <p style="margin: 0 0 5px; font-size: 14px;">'.$trans_remember.'</p>
                '.$trans_list.'
                <p style="margin: 0 0 10px; font-size: 14px;">
                    <strong>'.$trans_instruction.'</strong>
                    <a href="'.$documents_url.'" target="_blank" style="color: #90bb13; text-decoration: underline;">
                        '.$trans_upload.'
                    </a>
                </p>
                <p style="margin: 0; font-size: 13px; color: #666;">
                   <strong>'.$trans_whatsapp.'</strong>
                </p>
            </div>';

        return $template;
    }

    /**
     * Genera solo las variables trans y trans_list sin el HTML completo
     * Retorna array [$trans_remember, $trans_list]
     */
    public function generateDocumentListOnly($documentNumber, $documentType)
    {
        if (empty($documentNumber) || empty($documentType)) {
            return ['', ''];
        }

        $iso = Context::getContext()->language->iso_code;

        switch ($documentType) {
            case 'corta':
                $trans_remember = strtr(
                    $this->l('[b]REMEMBER:[/b] In order to ship your firearm, we need you to send us the following documentation:', 'alsernetforms', $iso),
                    ['[b]' => '<strong>', '[/b]' => '</strong>']
                );
                $trans_list = '<ul style="padding-left: 20px; margin: 8px 0;">'
                    .'<li>'.$this->l('A photocopy of your ID (both sides)', 'alsernetforms', $iso).'</li>'
                    .'<li>'.$this->l('A photocopy of your handgun permit (type B) or Olympic shooting permit (type F)', 'alsernetforms', $iso).'</li>'
                    .'</ul>';
                break;

            case 'rifle':
                $trans_remember = strtr(
                    $this->l('[b]REMEMBER:[/b] In order to ship your firearm, we need you to send us the following documentation:', 'alsernetforms', $iso),
                    ['[b]' => '<strong>', '[/b]' => '</strong>']
                );
                $trans_list = '<ul style="padding-left: 20px; margin: 8px 0;">'
                    .'<li>'.$this->l('A photocopy of your ID (both sides)', 'alsernetforms', $iso).'</li>'
                    .'<li>'.$this->l('A photocopy of your rifled long-range firearm permit (type D)', 'alsernetforms', $iso).'</li>'
                    .'</ul>';
                break;

            case 'escopeta':
                $trans_remember = strtr(
                    $this->l('[b]REMEMBER:[/b] In order to ship your weapon, we need you to send us the following documentation:', 'alsernetforms', $iso),
                    ['[b]' => '<strong>', '[/b]' => '</strong>']
                );
                $trans_list = '<ul style="padding-left: 20px; margin: 8px 0;">'
                    .'<li>'.$this->l('A photocopy of your ID (both sides)', 'alsernetforms', $iso).'</li>'
                    .'<li>'.$this->l('A photocopy of a shotgun license (type E)', 'alsernetforms', $iso).'</li>'
                    .'</ul>';
                break;

            case 'dni':
                $trans_remember = strtr(
                    $this->l('[b]REMEMBER:[/b] In order to process your BB gun order, you must send us a.', 'alsernetforms', $iso),
                    ['[b]' => '<strong>', '[/b]' => '</strong>']
                );
                $trans_list = '<ul style="padding-left: 20px; margin: 8px 0;">'
                    .'<li>'.$this->l('A photocopy of your ID (both sides)', 'alsernetforms', $iso).'</li>'
                    .'</ul>';
                break;

            default:
                $trans_remember = strtr(
                    $this->l('[b]REMEMBER:[/b] In order to ship your air rifle, you must provide us with a copy of your passport or driving licence (both sides if it\'s a card).', 'alsernetforms', $iso),
                    ['[b]' => '<strong>', '[/b]' => '</strong>']
                );
                $trans_list = '';
                break;
        }

        return [$trans_remember, $trans_list];
    }

    public function getSportsByIdsAndTranslateNew()
    {

        $lang = $this->context->language->id;

        $sports_map = [
            1 => 'GOLFE',
            5 => 'HUNTING',
            6 => 'FISHING',
            3 => 'HORSE',
            4 => 'DIVING',
            2 => 'BOATING',
            9 => 'SKIING',
            1395 => 'PADEL',
            10 => 'ADVENTURE',
        ];

        $sports_translation_map = [
            1 => [
                'GOLF' => 'GOLF',
                'HUNTING' => 'CAZA',
                'FISHING' => 'PESCA',
                'HORSE' => 'HÍPICA',
                'DIVING' => 'BUCEO',
                'BOATING' => 'NAUTICA',
                'SKIING' => 'ESQUÍ',
                'PADEL' => 'PÁDEL',
                'ADVENTURE' => 'AVENTURA',
            ],
            2 => [
                'GOLF' => 'GOLF',
                'HUNTING' => 'HUNTING',
                'FISHING' => 'FISHING',
                'HORSE' => 'HORSE RIDING',
                'DIVING' => 'DIVING',
                'BOATING' => 'BOATING',
                'SKIING' => 'SKIING',
                'PADEL' => 'PADEL',
                'ADVENTURE' => 'ADVENTURE',
            ],
            3 => [
                'GOLF' => 'GOLF',
                'HUNTING' => 'CHÂSSE',
                'FISHING' => 'PÊCHE',
                'HORSE' => 'ÉQUITATION',
                'DIVING' => 'PLONGÉE',
                'BOATING' => 'NAUTIQUE',
                'SKIING' => 'SKI',
                'PADEL' => 'PADEL',
                'ADVENTURE' => 'OUTDOOR',
            ],
            4 => [
                'GOLF' => 'GOLFE',
                'HUNTING' => 'CAÇA',
                'FISHING' => 'PESCA',
                'HORSE' => 'EQUITAÇÃO',
                'DIVING' => 'MERGULHO',
                'BOATING' => 'VELA',
                'SKIING' => 'ESQUI',
                'PADEL' => 'PADEL',
                'ADVENTURE' => 'AVENTURA',
            ],
            5 => [
                'GOLF' => 'GOLF',
                'HUNTING' => 'JAGD',
                'FISHING' => 'ANGELN',
                'HORSE' => 'REITEN',
                'DIVING' => 'TAUCHEN',
                'BOATING' => 'NAUTIK',
                'SKIING' => 'SKI',
                'PADEL' => 'PADEL',
                'ADVENTURE' => 'OUTDOOR',
            ],
            6 => [
                'GOLF' => 'GOLF',
                'HUNTING' => 'CACCIA',
                'FISHING' => 'PESCA',
                'HORSE' => 'EQUITAZIONE',
                'DIVING' => 'SUBACQUEA',
                'BOATING' => 'NAUTICA',
                'SKIING' => 'SCI',
                'PADEL' => 'PADEL',
                'ADVENTURE' => 'OUTDOOR',
            ],
        ];

        $ids = [1, 2, 3, 4, 5, 6, 9, 10, 1395];

        $sports_in_language = array_map(function ($id) use ($sports_map, $sports_translation_map, $lang) {
            $sport_name = $sports_map[$id];

            return [
                'id' => $id,
                'name' => $sports_translation_map[$lang][$sport_name] ?? $sport_name,
            ];
        }, $ids);

        return $sports_in_language;
    }

    public function assignUserToLists($id_susc_newsletter)
    {
        // Obtener los datos del usuario
        $user = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'alsernet_forms_newsletter WHERE id_susc_newsletter = '.(int) $id_susc_newsletter);

        // Verificar condiciones y asignar listas
        if ($user['lopd'] == 1) {
            // Asignar a la lista blanca si aceptó el LOPD
            $this->addUserToList($id_susc_newsletter, 1);  // 1: Lista blanca
        } else {
            // Asignar a la lista negra si no aceptó el LOPD
            $this->addUserToList($id_susc_newsletter, 2);  // 2: Lista negra
        }

        // Asignación por deporte (si tiene deportes asociados)
        $sports = explode(',', $user['ids_sports']);
        foreach ($sports as $sport_id) {
            // Verificar si el deporte está en la lista de deportes específicos
            $this->addUserToList($id_susc_newsletter, $sport_id);  // Asigna a la lista correspondiente al deporte
        }
    }

    private function addUserToList($id_susc_newsletter, $id_list)
    {
        // Verificar si el usuario ya está en la lista
        $existing = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'alsernet_form_user_lists WHERE id_susc_newsletter = '.(int) $id_susc_newsletter.' AND id_list = '.(int) $id_list);

        if (! $existing) {
            // Si no está, agregarlo
            Db::getInstance()->insert('alsernet_form_user_lists', [
                'id_susc_newsletter' => (int) $id_susc_newsletter,
                'id_list' => (int) $id_list,
                'added_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function hookDisplayForms($params)
    {
        return $this->renderWidget('displayForms', $params);
    }

    public function hookHeader($params)
    {

        $this->context->controller->addCSS($this->_path.'views/css/front/style.css', 'all');
        $this->context->controller->addCSS($this->_path.'views/css/front/form.css', 'all');
        // $this->context->controller->addCSS($this->_path.'views/css/front/dashboard.css','all');

        // $this->context->controller->addJS($this->_path.'views/js/vendor/api.js');
        $this->context->controller->addJS($this->_path.'views/js/vendor/validate/validate.js');
        $this->context->controller->addJS($this->_path.'views/js/vendor/validate/messages.js');
        $this->context->controller->addJS($this->_path.'views/js/front/scripts.js');
        $this->context->controller->addJS($this->_path.'views/js/front/campaigns.js');
        $this->context->controller->addJS($this->_path.'views/js/front/documents.js');
        $this->context->controller->addJS($this->_path.'views/js/front/subscribers.js');
    }
}
