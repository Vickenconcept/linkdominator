<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Authenticate\LoginController;
use App\Http\Controllers\Authenticate\ForgotPasswordController;
use App\Http\Controllers\Authenticate\ProfileController;
use App\Http\Controllers\Authenticate\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CallManagerController;
use App\Http\Controllers\CalendlyController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\AiwriterController;
use App\Http\Controllers\SchedulePostController;
use App\Http\Controllers\SocialAccountController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInviteController;
use App\Http\Controllers\UserManagerController;
use App\Http\Controllers\JvzooIpnController;
use App\Http\Controllers\CommentFeedController;

Route::get('/', function () {
    return redirect()->route('auth.login');
});

Route::post('/ipn/jvzoo', [JvzooIpnController::class, 'JVZoo'])->name('ipn.jvzoo');

Route::controller(LoginController::class)->group(function () {
    Route::get('/auth/signin', 'index')->name('auth.login');
    Route::post('/login/authenticate', 'authenticate')->name('auth.authenticate');
});

Route::controller(ForgotPasswordController::class)->group(function () {
    Route::get('/auth/forgot-password', 'index')->name('auth.forgot-password');
    Route::post('/forgot-password', 'store')->name('auth.reset-password');
});

Route::get('/team/register', [TeamInviteController::class, 'register'])->name('team.register');
Route::post('/team/register', [TeamInviteController::class, 'acceptInvite'])->name('team.acceptInvite');

Route::get('/auth/bundle-access', [RegisterController::class, 'bundleSignup'])->name('register.bundle');
Route::post('/auth/bundle-access', [RegisterController::class, 'bundleSignupAuth'])->name('register.bundle.auth');

Route::get('/create-reseller', [RegisterController::class, 'resellerSignup'])->name('register.reseller');
Route::post('/auth/reseller-access', [RegisterController::class, 'resellerSignupAuth'])->name('register.reseller.auth');

Route::get('/privacy-policy', function(){
    return view('privacy-policy');
})->name('privacy-policy');

// Authenticated Routes
Route::middleware(['auth'])->group(function(){
    Route::controller(ProfileController::class)->group(function(){
        Route::get('/profile', 'index')->name('auth.profile');
        Route::put('/profile', 'update')->name('auth.update');
        Route::put('/profile/password', 'updatePassword')->name('auth.updatePassword');
        Route::put('/profile/esp', 'updateEsp')->name('auth.updateEsp');
        Route::post('/profile/generate-token', 'generateToken')->name('auth.generateToken');
        Route::post('/profile/logout', 'logout')->name('auth.logout');
    });

    Route::controller(DashboardController::class)->group(function (){
        Route::get('/dashboard', 'index')->name('dashboard');
        Route::get('/ministats', 'ministats')->name('dashboard.ministats');
        Route::get('/piestats', 'piestats')->name('dashboard.piestats');
        Route::get('/linestats', 'linestats')->name('dashboard.linestats');
        Route::get('/barstats', 'barstats')->name('dashboard.barstats');
    });

    Route::controller(CampaignController::class)->group(function (){
        Route::get('/campaigns', 'index')->name('campaign');
        Route::get('/campaign', 'create')->name('campaign.create');
        Route::post('/campaign/store', 'store')->name('campaign.store');
        Route::post('/campaign/update/{id}', 'update')->name('campaign.update');
        Route::post('/campaign/removelist/{id}', 'removelist')->name('campaign.removelist');
        Route::delete('/campaign/delete/{id}', 'destroy')->name('campaign.delete');
        Route::post('/sequence/store', 'storeSequence')->name('sequence.store');
    });

    Route::controller(CallManagerController::class)->group(function (){
        Route::get('/calls', 'index')->name('calls');
        Route::get('/calls/reminders', 'callReminder')->name('calls.reminders');
        Route::get('/calls/{id}', 'showCallDetails')->name('calls.show');
        Route::put('/calls/status/update', 'update')->name('calls.update');
        Route::put('/calls/pending-message/update', 'updatePendingMessageWeb')->name('calls.update-pending-message');
        Route::get('/call/reminder-messages/{id}', 'show')->name('calls.show.reminder-message');
        Route::put('/call/reminder/update', 'updateCallReminder')->name('calls.update.reminder-message');
    });

    Route::controller(LeadController::class)->group(function (){
        Route::get('/leadlist', 'index')->name('leads.list');
        Route::get('/leadlist/search', 'search_leadlist')->name('leads.list.search');
        Route::get('/leads/{listId}', 'show')->name('leads.show');
        Route::put('/leadlist/update/{listId}', 'update')->name('leads.update');
        Route::get('/leads/export', 'export')->name('leads.export');
        Route::get('/leads/export/bulk', 'bulk_export')->name('leads.bulk_export');
        Route::get('/leads/seach', 'search_leads')->name('leads.search_leads');
        Route::delete('/leadlist/remove/{listId}', 'remove_leadlist')->name('leads.remove_leadlist');
        Route::delete('/leads/remove/{leadId}', 'remove_lead')->name('leads.remove_lead');
        Route::delete('/leads/remove/bulk/{listId}', 'remove_lead_bulk')->name('leads.remove_lead_bulk');
    });

    Route::controller(AiwriterController::class)->group(function (){
        Route::get('/ai-contents', 'index')->name('aiwriter.index');
        Route::get('/ai-content/new', 'create')->name('aiwriter.create');
        Route::post('/ai-content/store', 'store')->name('aiwriter.store');
        Route::get('/ai-content/edit/{id}', 'edit')->name('aiwriter.edit');
        Route::put('/ai-content/update/{id}', 'update')->name('aiwriter.update');
        Route::delete('/ai-content/delete/{id}', 'destroy')->name('aiwriter.delete');
        Route::post('/ai-content/generate', 'generate')->name('aiwriter.generate');
    });

    Route::controller(SchedulePostController::class)->group(function (){
        Route::get('/posts', 'index')->name('post.index');
        Route::get('/post/new', 'create')->name('post.create');
        Route::post('/post/store', 'store')->name('post.store');
        Route::get('/post/edit/{id}', 'edit')->name('post.edit');
        Route::put('/post/update/{id}', 'update')->name('post.update');
        Route::delete('/post/delete/{id}', 'destroy')->name('post.delete');
        Route::post('/post/generate-aicontent', 'generateAiContent')->name('post.aigenerate');
    });

    Route::controller(SocialAccountController::class)->group(function (){
        Route::get('/social-account', 'index')->name('social-account.index');
        Route::delete('/social-account/disconnect/{id}', 'disconnect')->name('social-account.disconnect');
    });

    Route::controller(IntegrationController::class)->group(function (){
        Route::get('/integration/linkedin/login', 'login')->name('integration.login');
        Route::get('/integration/linkedin/callback', 'callback')->name('integration.callback');
    });

    Route::controller(CalendlyController::class)->group(function (){
        Route::get('/oauth/calendly', 'redirect')->name('calendly.connect');
        Route::get('/oauth/calendly/callback', 'callback')->name('calendly.callback');
        Route::post('/calendly/disconnect', 'disconnect')->name('calendly.disconnect');
        Route::get('/calendly/status', 'status')->name('calendly.status');
    });

    Route::controller(TeamController::class)->group(function (){
        Route::get('/team', 'index')->name('team.index');
        Route::delete('/team/delete', 'destory')->name('team.delete');
    });

    Route::controller(TeamInviteController::class)->group(function (){
        Route::post('/team/send-invite', 'sendInvite')->name('team.sendInvite');
        Route::post('/team/resend-invite/{id}', 'resendInvite')->name('team.resendInvite');
        Route::delete('/team/delete-invite/{id}', 'destory')->name('team.deleteInvite');
    });

    Route::get('/tutorials', function(){
        return view('tutorial');
    })->name('tutorials');

    Route::get('/upsell-unlimited', function(){
        return view('bonus.unlimited');
    })->name('upsell-unlimited');

    Route::get('/market-agency-setup', function(){
        return view('bonus.dfyMarketAgencySetup');
    })->name('market-agency-setup');

    Route::get('/dfy-campaign', function(){
        return view('bonus.dfyCampaign');
    })->name('dfy-campaign');

    Route::get('/dfy-software-empire-setup', function(){
        return view('bonus.dfySoftwareEmpireSetup');
    })->name('dfy-software-empire-setup');

    Route::get('/coach-program', function(){
        return view('bonus.coachProgram');
    })->name('coach-program');

    Route::get('/unlimited-traffic', function(){
        return view('bonus.unlimitedTraffic');
    })->name('unlimited-traffic');

    // Admin 
    Route::controller(UserManagerController::class)->group(function (){
        Route::get('/admin/users', 'index')->name('users.index');
        Route::post('/admin/user/store', 'store')->name('user.store');
        Route::put('/admin/user/update/{id}', 'update')->name('user.update');
        Route::delete('/admin/user/delete/{id}', 'destroy')->name('user.delete');
        Route::get('/admin/user/permissions', 'userPermissions')->name('user.permissions');
        Route::put('/admin/user/assign-permissions', 'assignPermissions')->name('user.assign-permissions');

        Route::get('/reseller/users', 'resellerIndex')->name('reseller.index');
    });

    Route::controller(CommentFeedController::class)->group(function (){
        Route::get('/comment', 'index')->name('comment.index');
        Route::get('/comment/campaign/create', 'createCampaign')->name('comment.create-campaign');
        Route::post('/comment/campaign/store', 'storeCampaign')->name('comment.store-campaign');
        Route::put('/comment/campaign/update/{id}', 'updateCampaignStatus')->name('comment.update-campaign-status');
        Route::delete('/comment/campaign/delete/{id}', 'destroyCampaign')->name('comment.delete-campaign');
        Route::post('/comment/skip', 'skipComment')->name('comment.skip');
        Route::post('/comment/generate', 'generateComment')->name('comment.generate');
    });
});

Route::post('/comment/campaign-activities/generate', [CommentFeedController::class, 'generateWebhook']);