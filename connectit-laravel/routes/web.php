<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SlaController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ProblemController;
use App\Http\Controllers\ChangeController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\SettingsController;

// ─── Authentication ───────────────────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Profile ──────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
});

// ─── Dashboard ────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/system-analytics', [DashboardController::class, 'index'])->name('system.analytics');
});

// ─── Tickets ──────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');
    Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/comments', [TicketController::class, 'comment'])->name('tickets.comment');
    Route::post('/tickets/{ticket}/resolve', [TicketController::class, 'resolve'])->name('tickets.resolve');
});

// ─── Users ────────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create')->middleware('role:admin');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('role:admin');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('role:admin');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('role:admin');
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status')->middleware('role:admin');
});

// ─── SLA Management ───────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/sla', [SlaController::class, 'index'])->name('sla.index');
    Route::post('/sla', [SlaController::class, 'store'])->name('sla.store');
    Route::put('/sla/{slaPolicy}', [SlaController::class, 'update'])->name('sla.update');
    Route::delete('/sla/{slaPolicy}', [SlaController::class, 'destroy'])->name('sla.destroy');
});

// ─── Reports ──────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:agent'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});

// ─── Knowledge Base ───────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/knowledge', [KnowledgeBaseController::class, 'index'])->name('knowledge.index');
    Route::get('/knowledge/create', [KnowledgeBaseController::class, 'create'])->name('knowledge.create')->middleware('role:agent');
    Route::post('/knowledge', [KnowledgeBaseController::class, 'store'])->name('knowledge.store')->middleware('role:agent');
    Route::get('/knowledge/{knowledgeArticle}', [KnowledgeBaseController::class, 'show'])->name('knowledge.show');
    Route::get('/knowledge/{knowledgeArticle}/edit', [KnowledgeBaseController::class, 'edit'])->name('knowledge.edit')->middleware('role:agent');
    Route::put('/knowledge/{knowledgeArticle}', [KnowledgeBaseController::class, 'update'])->name('knowledge.update')->middleware('role:agent');
    Route::post('/knowledge/{knowledgeArticle}/helpful', [KnowledgeBaseController::class, 'helpful'])->name('knowledge.helpful');
});

// ─── CMDB / Assets ────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create')->middleware('role:agent');
    Route::post('/assets', [AssetController::class, 'store'])->name('assets.store')->middleware('role:agent');
    Route::get('/assets/{asset}', [AssetController::class, 'show'])->name('assets.show');
    Route::get('/assets/{asset}/edit', [AssetController::class, 'edit'])->name('assets.edit')->middleware('role:agent');
    Route::put('/assets/{asset}', [AssetController::class, 'update'])->name('assets.update')->middleware('role:agent');
    Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy')->middleware('role:admin');
});

// ─── Problem Management ───────────────────────────────────────────────────────
Route::middleware(['auth', 'role:agent'])->group(function () {
    Route::get('/problems', [ProblemController::class, 'index'])->name('problems.index');
    Route::get('/problems/create', [ProblemController::class, 'create'])->name('problems.create');
    Route::post('/problems', [ProblemController::class, 'store'])->name('problems.store');
    Route::get('/problems/{problem}', [ProblemController::class, 'show'])->name('problems.show');
    Route::put('/problems/{problem}', [ProblemController::class, 'update'])->name('problems.update');
});

// ─── Change Management ────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:agent'])->group(function () {
    Route::get('/changes', [ChangeController::class, 'index'])->name('changes.index');
    Route::get('/changes/create', [ChangeController::class, 'create'])->name('changes.create');
    Route::post('/changes', [ChangeController::class, 'store'])->name('changes.store');
    Route::get('/changes/{change}', [ChangeController::class, 'show'])->name('changes.show');
    Route::put('/changes/{change}', [ChangeController::class, 'update'])->name('changes.update');
});

// ─── Timesheets ───────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/timesheets', [TimesheetController::class, 'index'])->name('timesheets.index');
    Route::get('/timesheets/{timesheet}', [TimesheetController::class, 'show'])->name('timesheets.show');
    Route::post('/timesheets/{timesheet}/submit', [TimesheetController::class, 'submit'])->name('timesheets.submit');
    Route::get('/timesheets-approvals', [TimesheetController::class, 'approvals'])->name('timesheets.approvals');
    Route::post('/timesheets/{timesheet}/approve', [TimesheetController::class, 'approve'])->name('timesheets.approve');
    Route::post('/timesheets/{timesheet}/reject', [TimesheetController::class, 'reject'])->name('timesheets.reject');
});

// ─── Settings ─────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
