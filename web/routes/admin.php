<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController;
use App\Livewire\Admin\Articles\ArticleEditor;
use App\Livewire\Admin\Articles\ArticleList;
use App\Livewire\Admin\Audit\AuditList;
use App\Livewire\Admin\Commerce\OrderList;
use App\Livewire\Admin\Commerce\PlanEditor;
use App\Livewire\Admin\Commerce\PlanList;
use App\Livewire\Admin\Commerce\SubscriptionList;
use App\Livewire\Admin\Settings\PaymentMethodsList;
use App\Livewire\Admin\Magazine\IssueEditor;
use App\Livewire\Admin\Magazine\IssueList;
use App\Livewire\Admin\Media\MediaLibrary;
use App\Livewire\Admin\Moderation\CommentList;
use App\Livewire\Admin\Newsletter\CampaignEditor;
use App\Livewire\Admin\Newsletter\CampaignList;
use App\Livewire\Admin\Settings\SettingsEditor;
use App\Livewire\Admin\Taxonomies\CategoryList;
use App\Livewire\Admin\Users\UserEditor;
use App\Livewire\Admin\Users\UserList;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes backoffice — prefix /admin, middleware auth + role backoffice
|--------------------------------------------------------------------------
|
| En production, ces routes pourront être servies depuis un sous-domaine
| `admin.geniemag.ci` via Route::domain() dans RouteServiceProvider.
|
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'backoffice', '2fa'])
    ->group(function (): void {

        Route::get('/', [DashboardController::class, '__invoke'])->name('dashboard');

        // Les routes suivantes seront implémentées sprint par sprint (Phase MVP)
        // Placeholders pour que les liens de navigation ne cassent pas.
        Route::get('/articles', ArticleList::class)->name('articles.index');
        Route::get('/articles/nouveau', ArticleEditor::class)->name('articles.create');
        Route::get('/articles/{article}/editer', ArticleEditor::class)->name('articles.edit');
        Route::get('/medias', MediaLibrary::class)->name('media.index');
        Route::get('/taxonomies', CategoryList::class)->name('taxonomies.index');
        Route::get('/magazines', IssueList::class)->name('issues.index');
        Route::get('/magazines/nouveau', IssueEditor::class)->name('issues.create');
        Route::get('/magazines/{issue}/editer', IssueEditor::class)->name('issues.edit');

        // --- Commercial : com / adm / sup -----------------------------------
        Route::middleware('role:com|adm|sup')->group(function (): void {
            Route::get('/abonnes', SubscriptionList::class)->name('subscribers.index');
            Route::get('/commandes', OrderList::class)->name('orders.index');
            Route::get('/formules', PlanList::class)->name('plans.index');
            Route::get('/formules/nouvelle', PlanEditor::class)->name('plans.create');
            Route::get('/formules/{plan}/editer', PlanEditor::class)->name('plans.edit');
            Route::get('/newsletters', CampaignList::class)->name('newsletters.index');
            Route::get('/newsletters/nouveau', CampaignEditor::class)->name('newsletters.create');
            Route::get('/newsletters/{campaign}/editer', CampaignEditor::class)->name('newsletters.edit');
        });

        // --- Modération : chef / edit / adm / sup ----------------------------
        Route::middleware('role:chef|edit|adm|sup')->group(function (): void {
            Route::get('/commentaires', CommentList::class)->name('comments.index');
        });

        // --- Système : adm / sup uniquement ---------------------------------
        // Bloque l'escalade de privilège via self-edit (C1 de l'audit 2026-04).
        Route::middleware('role:adm|sup')->group(function (): void {
            Route::get('/utilisateurs', UserList::class)->name('users.index');
            Route::get('/utilisateurs/nouveau', UserEditor::class)->name('users.create');
            Route::get('/utilisateurs/{user}/editer', UserEditor::class)->name('users.edit');
            Route::get('/audit', AuditList::class)->name('audit.index');
            Route::get('/parametres', SettingsEditor::class)->name('settings.index');
            Route::get('/moyens-paiement', PaymentMethodsList::class)->name('payment-methods.index');
        });
    });
