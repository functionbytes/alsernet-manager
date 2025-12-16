<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Pages\PagesController;
use App\Http\Controllers\RoleManagementController;

Route::group(['middleware' => ['web']], function () {

    Route::get('/', [LoginController::class, 'showLoginForm'])->name('index');
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/register', [LoginController::class, 'showRegisterForm'])->name('register');
    Route::get('/home', [PagesController::class, 'home'])->name('home');

    Route::get('/clear', function () {
        Artisan::call('dump-autoload');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('config:cache');

        return '<h1>Cache Borrado</h1>';
    });

    Route::group(['prefix' => 'password'], function () {
        Route::get('/confirm', [ForgotPasswordController::class, 'showLinkRequest'])->name('password.confirm');
        Route::get('/reset', [ForgotPasswordController::class, 'showLinkRequest'])->name('password.reset');
        Route::post('/reset', [ResetPasswordController::class, 'reset']);
        Route::post('/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/reset/{slack}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset.token');
    });

    Route::get('/files/{uid}/{name?}', [function ($uid, $name) {
        $path = storage_path('app/users/'.$uid.'/home/files/'.$name);
        $mime_type = \App\Library\File::getFileType($path);
        if (\Illuminate\Support\Facades\File::exists($path)) {
            return response()->file($path, ['Content-Type' => $mime_type]);
        } else {
            abort(404);
        }
    }])->where('name', '.+')->name('user_files');

    // assets path for customer thumbs
    Route::get('/thumbs/{uid}/{name?}', [function ($uid, $name) {
        // Do not use $user->getThumbsPath($name), avoid one SQL query!
        $path = storage_path('app/users/'.$uid.'/home/thumbs/'.$name);
        if (\Illuminate\Support\Facades\File::exists($path)) {
            $mime_type = \App\Library\File::getFileType($path);

            return response()->file($path, ['Content-Type' => $mime_type]);
        } else {
            abort(404);
        }
    }])->where('name', '.+')->name('user_thumbs');

    Route::get('/p/assets/{path}', [function ($token) {
        $decodedPath = \App\Library\StringHelper::base64UrlDecode($token);
        $absPath = storage_path($decodedPath);

        if (\Illuminate\Support\Facades\File::exists($absPath)) {
            $mime_type = \App\Library\File::getFileType($absPath);

            return response()->file($absPath, [
                'Content-Type' => $mime_type,
                'Content-Length' => filesize($absPath),
            ]);
        } else {
            abort(404);
        }
    }])->name('public_assets_deprecated');

    Route::get('assets/{dirname}/{basename}', [function ($dirname, $basename) {
        $dirname = \App\Library\StringHelper::base64UrlDecode($dirname);
        $absPath = storage_path(join_paths($dirname, $basename));

        if (\Illuminate\Support\Facades\File::exists($absPath)) {
            $mimetype = \App\Library\File::getFileType($absPath);

            return response()->file($absPath, [
                'Content-Type' => $mimetype,
                'Content-Length' => filesize($absPath),
            ]);
        } else {
            abort(404);
        }
    }])->name('public_assets');

    // Route::get('setting/{filename}', 'SettingController@file'); // TODO: Fix this route - controller doesn't exist

    // Route::get('/datatable_locale', 'Controller@datatable_locale'); // TODO: Fix this route - controller doesn't exist
    // Route::get('/jquery_validate_locale', 'Controller@jquery_validate_locale'); // TODO: Fix this route - controller doesn't exist

    // Admin Panel Routes - Protected by super-admins role
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        // Role Management
        Route::get('/roles', [RoleManagementController::class, 'index'])->name('roles.index');
        Route::get('/roles/{user}/edit', [RoleManagementController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{user}', [RoleManagementController::class, 'update'])->name('roles.update');

        // Role Mappings & Profile Routes
        Route::get('/roles-mappings', [RoleManagementController::class, 'mappings'])->name('roles.mappings');
        Route::put('/role-mapping/{mapping}', [RoleManagementController::class, 'updateMapping'])->name('roles.update-mapping');
        Route::put('/profile-route/{route}', [RoleManagementController::class, 'updateRoute'])->name('roles.update-route');
    });

    // LiveChat Widget - Public route (no authentication required)
    Route::prefix('lc')->name('lc.')->group(function () {
        Route::get('/widget', [\App\Http\Controllers\Helpdesk\WidgetController::class, 'index'])->name('widget');
        Route::get('/launcher-demo', [\App\Http\Controllers\Helpdesk\WidgetController::class, 'launcherDemo'])->name('launcher-demo');
        Route::get('/api/settings', [\App\Http\Controllers\Helpdesk\WidgetController::class, 'settings'])->name('widget.settings');
        Route::get('/api/helpcenter', [\App\Http\Controllers\Managers\Helpdesk\HelpCenterController::class, 'apiWidget'])->name('widget.helpcenter');
        Route::get('/api/helpcenter/articles/{id}', [\App\Http\Controllers\Managers\Helpdesk\HelpCenterController::class, 'apiArticle'])->name('widget.helpcenter.article');

        // Widget Conversation API - Public (customer-facing)
        Route::post('/api/conversations', [\App\Http\Controllers\Api\Helpdesk\WidgetConversationController::class, 'store'])->name('api.conversations.store');
        Route::get('/api/conversations/{id}', [\App\Http\Controllers\Api\Helpdesk\WidgetConversationController::class, 'show'])->name('api.conversations.show');
        Route::post('/api/conversations/{id}/messages', [\App\Http\Controllers\Api\Helpdesk\WidgetConversationController::class, 'sendMessage'])->name('api.conversations.messages.send');
        Route::get('/api/conversations/{id}/messages', [\App\Http\Controllers\Api\Helpdesk\WidgetConversationController::class, 'getMessages'])->name('api.conversations.messages.index');
        Route::post('/api/conversations/{id}/close', [\App\Http\Controllers\Api\Helpdesk\WidgetConversationController::class, 'close'])->name('api.conversations.close');

        // Catch-all route for React Router (BrowserRouter) - Must be last
        // This allows client-side routing for /lc/widget/*, /lc/widget/conversation, /lc/widget/help, etc.
        Route::get('/widget/{any?}', [\App\Http\Controllers\Helpdesk\WidgetController::class, 'index'])
            ->where('any', '.*')
            ->name('widget.catchall');
    });

    // Alternative route alias for launcher demo (livechat prefix)
    Route::get('/livechat/launcher-demo', [\App\Http\Controllers\Helpdesk\WidgetController::class, 'launcherDemo'])->name('livechat.launcher-demo');

});
