<?php

declare(strict_types=1);

use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\MagazineController;
use App\Http\Controllers\Public\SubscribeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques — frontend magazine
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, '__invoke'])->name('home');
Route::get('/sitemap.xml', \App\Http\Controllers\Public\SitemapController::class)->name('sitemap');

/*
|--------------------------------------------------------------------------
| Pages légales (EP-15 / EP-22)
|--------------------------------------------------------------------------
*/
Route::controller(\App\Http\Controllers\Public\LegalController::class)->group(function (): void {
    Route::get('/mentions-legales', 'mentions')->name('legal.mentions');
    Route::get('/confidentialite', 'privacy')->name('legal.privacy');
    Route::get('/cgu', 'terms')->name('legal.terms');
    Route::get('/cookies', 'cookies')->name('legal.cookies');
});

Route::get('/rubriques/{category:slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/recherche', \App\Http\Controllers\Public\SearchController::class)->name('search');

Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])->name('article.show');
Route::get('/magazine', [MagazineController::class, 'index'])->name('magazine');
Route::middleware('auth')->group(function (): void {
    Route::get('/magazine/{issue:slug}/lecteur', [MagazineController::class, 'reader'])->name('magazine.reader');
    Route::get('/magazine/{issue:slug}/pdf', [MagazineController::class, 'streamPdf'])
        ->middleware('throttle:30,1')
        ->name('magazine.pdf');
});
Route::get('/abonnement', [SubscribeController::class, 'index'])->name('subscribe');

/*
|--------------------------------------------------------------------------
| Newsletter (double opt-in)
|--------------------------------------------------------------------------
*/
Route::post('/newsletter/inscription', [\App\Http\Controllers\Public\NewsletterController::class, 'subscribe'])
    ->middleware(['throttle:10,1'])
    ->name('newsletter.subscribe');
Route::get('/newsletter/confirmer/{token}', [\App\Http\Controllers\Public\NewsletterController::class, 'confirm'])
    ->name('newsletter.confirm');
Route::get('/newsletter/desinscription/{token}', [\App\Http\Controllers\Public\NewsletterController::class, 'unsubscribe'])
    ->name('newsletter.unsubscribe');

/*
|--------------------------------------------------------------------------
| Paiement (Paystack)
|--------------------------------------------------------------------------
*/
// Tunnel d'abonnement : formulaire intermédiaire → paiement Paystack
Route::get('/abonnement/{plan:code}/inscription', [CheckoutController::class, 'form'])
    ->name('checkout.form');
Route::post('/abonnement/{plan:code}/inscription', [CheckoutController::class, 'process'])
    ->middleware(['throttle:10,1'])
    ->name('checkout.process');

Route::get('/paiement/callback', [CheckoutController::class, 'callback'])
    ->name('checkout.callback');

// Simulateur local : stand-in pour le hosted checkout Paystack en dev
if (! app()->environment('production')) {
    Route::get('/paiement/simulateur/{reference}', [\App\Http\Controllers\Public\CheckoutSimulatorController::class, 'show'])
        ->name('checkout.simulator');
    Route::post('/paiement/simulateur/{reference}', [\App\Http\Controllers\Public\CheckoutSimulatorController::class, 'simulate'])
        ->name('checkout.simulator.submit');
}

Route::post('/webhooks/paystack', PaystackWebhookController::class)
    ->middleware(['throttle:60,1', 'paystack.ip'])
    ->name('webhooks.paystack');

/*
|--------------------------------------------------------------------------
| Espace abonné
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function (): void {
    Route::get('/compte', \App\Http\Controllers\Public\AccountController::class)->name('account');
    Route::get('/compte/factures/{invoice:number}/pdf', [\App\Http\Controllers\Public\InvoiceController::class, 'download'])
        ->name('account.invoice.download');
    // Redirection intelligente — aiguille chaque profil vers son espace.
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user?->hasAnyRole(['red', 'chef', 'edit', 'adm', 'sup'])) {
            return redirect()->route('admin.dashboard');
        }
        if ($user?->type === 'subscriber') {
            return redirect()->route('account');
        }
        return redirect()->route('home');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 2FA (US-006)
    Route::get('/2fa/setup', [\App\Http\Controllers\Auth\TwoFactorController::class, 'setup'])->name('2fa.setup');
    Route::post('/2fa/setup', [\App\Http\Controllers\Auth\TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::get('/2fa/challenge', [\App\Http\Controllers\Auth\TwoFactorController::class, 'challenge'])->name('2fa.challenge');
    Route::post('/2fa/challenge', [\App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])
        ->middleware('throttle:5,1')->name('2fa.verify');
    Route::delete('/2fa', [\App\Http\Controllers\Auth\TwoFactorController::class, 'disable'])->name('2fa.disable');
});

/*
|--------------------------------------------------------------------------
| Backoffice (/admin) — routage isolé
|--------------------------------------------------------------------------
*/
require __DIR__.'/admin.php';

require __DIR__.'/auth.php';
