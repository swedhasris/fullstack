<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\WebhookController;

// ─── Public / Webhook ─────────────────────────────────────────────────────────
Route::post('/webhooks/whatsapp', [WebhookController::class, 'whatsapp']);

// ─── Authenticated API (Sanctum) ─────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
});

// ─── Session-authenticated API (web guard) ────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Analytics
    Route::get('/analytics/stats', [DashboardController::class, 'stats']);

    // Tickets
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);
    Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign']);
    Route::post('/tickets/{ticket}/comments', [TicketController::class, 'comment']);
    Route::post('/tickets/{ticket}/resolve', [TicketController::class, 'resolve']);
    Route::get('/tickets/{ticket}/activities', [TicketController::class, 'activities']);
    Route::post('/tickets/{ticketId}/activities', [TicketController::class, 'logActivity']);

    // AI
    Route::post('/ai/suggest', [TicketController::class, 'suggest']);
    Route::post('/ai/chat', [TicketController::class, 'chat']);

    // Notifications
    Route::post('/notify', [TicketController::class, 'notify']);

    // Users
    Route::get('/users/agents', [UserController::class, 'agents']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);

    // Timesheets
    Route::post('/timesheets/get-or-create', [TimesheetController::class, 'getOrCreate']);
    Route::post('/time-cards', [TimesheetController::class, 'storeTimeCard']);
});
