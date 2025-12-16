<?php

namespace App\Http\Controllers\Managers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailSettingsController extends Controller
{
    /**
     * Display email settings selection page
     */
    public function index()
    {
        $pageTitle = 'Configuración de Email';
        $breadcrumb = 'Configuración / Email';

        // Get basic settings info for display
        $outgoingSettings = Setting::getEmailSettings();
        $incomingSettings = Setting::getIncomingEmailSettings();

        return view('managers.views.settings.email.index', compact(
            'pageTitle',
            'breadcrumb',
            'outgoingSettings',
            'incomingSettings'
        ));
    }
}
