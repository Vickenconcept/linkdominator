<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CallManagerController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\AiwriterController;
use App\Http\Controllers\ChromeApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Routes that don't require authentication (for chrome extension access)
Route::post('calls/analyze-message', [CallManagerController::class, 'analyzeMessageReply']);
Route::post('calls/conversation/store', [CallManagerController::class, 'storeConversationMessage']);
Route::get('calls/{id}/status', [CallManagerController::class, 'getCallStatus']);

// Chrome extension routes (require lk-id header validation)
Route::middleware(['api'])->group(function() {
    Route::get('campaigns', [CampaignController::class, 'campaign']); // tested
    Route::get('campaigns/status-updates', [CampaignController::class, 'getCampaignStatusUpdates']); // real-time updates
    Route::get('campaigns/{id}/debug-accept-rate', [CampaignController::class, 'debugAcceptRate']); // debug accept rate
    Route::get('campaign/{id}/leads', [CampaignController::class, 'campaignLeads']); // tested
    Route::get('campaign/{id}/sequence', [CampaignController::class, 'campaignSequence']); // tested
    Route::post('campaign/{id}/update', [CampaignController::class, 'campaignUpdate']);
    Route::post('campaign/{id}/update-node', [CampaignController::class, 'campaignSequenceUpdate']);
    Route::post('campaign/{id}/leadgen/store', [CampaignController::class, 'createLeadGenRunning']);
    Route::post('campaign/{campaignId}/leadgen/{leadId}/update', [CampaignController::class, 'updateLeadGenRunning']);
    Route::get('campaign/{campaignId}/leadgen', [CampaignController::class, 'getLeadGenRunning']); // tested
    Route::get('campaign/{campaignId}/leadgen/tracking', [CampaignController::class, 'getLeadGenTracking']); // new endpoint for tracking data
    Route::post('lead/{leadId}/update', [CampaignController::class, 'updateLeadNetworkDegree']);

    Route::post('book-call/store', [CallManagerController::class, 'storeCallStatus']);
    Route::post('calls/generate-message', [CallManagerController::class, 'generateCallMessage']);
    Route::post('calls/process-reply', [CallManagerController::class, 'processCallReply']);
    Route::post('calls/schedule', [CallManagerController::class, 'scheduleCall']);
    Route::get('calls/{id}/message', [CallManagerController::class, 'getCallMessage']);
    Route::get('calls/{id}/scheduling', [CallManagerController::class, 'getSchedulingInfo']);
    Route::get('calls/{id}/response', [CallManagerController::class, 'checkCallResponse']);
    Route::post('calls/{id}/calendar-link', [CallManagerController::class, 'generateCalendarLinkForCall']);
    Route::post('calls/test-reminders', [CallManagerController::class, 'testReminderSystem']);
    Route::post('calls/trigger-ai-messages', [CallManagerController::class, 'triggerAIMessages']);
    Route::get('calls/{id}/conversation', [CallManagerController::class, 'getConversationHistory']);
    Route::get('calls/search-by-connection/{connectionId}', [CallManagerController::class, 'searchByConnection']);
    Route::get('calls/ready-to-send', [CallManagerController::class, 'getMessagesReadyToSend']);
    Route::post('calls/{id}/update-status', [CallManagerController::class, 'updateMessageStatus']);
    Route::post('calls/{id}/pending-message', [CallManagerController::class, 'updatePendingMessage']);
});

Route::controller(LeadController::class)->group(function (){
    Route::get('leads/export', 'export')->name('leads.export');
    Route::get('leads/export/bulk', 'bulk_export')->name('leads.bulk_export');
});

Route::get('aicontents', [AiwriterController::class, 'aicontents']); 

Route::controller(ChromeApiController::class)->group(function (){
    // Regular routes
    Route::get('accessCheck', 'accessCheck');
    Route::get('audience', 'getAudience');
    Route::post('audience', 'storeAudience');
    Route::delete('audience', 'deleteAudience');
    Route::get('audience/list', 'getAudienceList');
    Route::post('audience/list', 'storeAudienceList');
    Route::put('audience/list', 'updateAudienceList');
    Route::delete('audience/list', 'deleteAudienceList');
    Route::get('audience/list/export', 'audienceListExport');
    Route::post('audiences/export/{audience_id}', 'export');
    Route::get('audience/count', 'audienceRecent');
    Route::get('autoresponses', 'getAutoResponses');
    Route::post('autoresponse/store', 'storeAutoResponse');
    Route::get('autoresponse/show/{id}', 'showAutoResponse');
    Route::put('autoresponse/update/{id}', 'updateAutoResponse');
    Route::delete('autoresponse/delete/{id}', 'deleteAutoResponse');
    Route::get('lang', 'langFilter');
    Route::post('conf', 'LinkedInConfig');
    Route::post('snleads/store', 'storeSnLeads');
    Route::get('snleads/lists', 'getSnLeadList');
    Route::post('snleads/list/store', 'storeSnLeadList');
    Route::post('activites', 'storeUserActivity');
    
    // Call management routes
    Route::get('calls/check-existing', 'checkExistingCall');
}); 
