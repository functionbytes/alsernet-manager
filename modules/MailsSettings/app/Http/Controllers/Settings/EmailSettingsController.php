<?php

namespace Modules\MailsSettings\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class EmailSettingsController extends Controller
{
    /**
     * Display email backups selection page
     */
    public function index()
    {
        $pageTitle = 'Configuración de Email';
        $breadcrumb = 'Configuración / Email';

        // Get basic backups info for display
        $outgoingSettings = Setting::getEmailSettings();
        $incomingSettings = Setting::getIncomingEmailSettings();

        return view('mails-backups::managers.backups.email.index', compact(
            'pageTitle',
            'breadcrumb',
            'outgoingSettings',
            'incomingSettings'
        ));
    }
}
