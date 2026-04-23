<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Contracts\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Payment;
use App\Services\Commerce\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Achat à l'unité d'un article premium.
 *
 *   - Visiteur non connecté : on mémorise l'intention en session
 *     (gm_intent.article_slug) et on redirige vers /login avec un message
 *     expliquant que le compte est requis pour le paiement. Après login/
 *     création, AuthenticatedSessionController redirige vers cette même
 *     route qui détecte l'intention et relance le flux.
 *
 *   - Utilisateur connecté : création de l'Order de type=article via
 *     CheckoutService::createOrderForArticle, puis redirection vers le
 *     gateway (Wave par défaut) — flux identique à un abonnement.
 *
 *   - Au retour du paiement, /paiement/callback (existing) détecte type=article
 *     via finalizeOrder qui dispatche sur finalizeArticlePurchase et crée
 *     l'AccessRight.
 */
class ArticlePurchaseController extends Controller
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly CheckoutService $checkout,
    ) {}

    /**
     * GET /compte/continuer-achat — consomme l'intent posé en session par
     * `buy()` lorsqu'un visiteur non connecté a cliqué « Acheter ». Appelé
     * automatiquement après login/register si `gm_intent.type=buy_article`.
     */
    public function continueIntent(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 401);

        $intent = $request->session()->pull('gm_intent');
        if (! is_array($intent) || ($intent['type'] ?? null) !== 'buy_article' || empty($intent['slug'])) {
            return redirect()->route('account')->with('status', 'Aucun achat en attente.');
        }

        $article = Article::where('slug', $intent['slug'])->first();
        if ($article === null || ! $article->isPurchasable()) {
            return redirect()->route('home')->withErrors(['payment' => 'L\'article demandé n\'est plus disponible à l\'achat.']);
        }

        return $this->buy($request, $article);
    }

    public function buy(Request $request, Article $article): RedirectResponse
    {
        abort_unless($article->isPurchasable(), 404);

        $user = $request->user();

        if ($user === null) {
            // Mémorise l'intention pour rebrancher après login/register.
            $request->session()->put('gm_intent', [
                'type' => 'buy_article',
                'slug' => $article->slug,
            ]);

            return redirect()
                ->route('login', ['intent' => 'buy_article'])
                ->with('status', 'Créez votre compte (ou connectez-vous) pour acheter cet article. Vous serez automatiquement redirigé vers le paiement.');
        }

        // Utilisateur déjà titulaire de l'article (abonnement actif ou AccessRight) : accès direct.
        if ($article->isAccessibleBy($user)) {
            return redirect()
                ->route('article.show', $article)
                ->with('status', 'Vous avez déjà accès à cet article.');
        }

        $order = $this->checkout->createOrderForArticle($user, $article, $this->gateway->providerCode());

        try {
            $init = $this->gateway->initialize(
                order: $order->fresh(),
                callbackUrl: route('checkout.callback'),
            );
        } catch (\Throwable $e) {
            Log::error('Article purchase initialize failed', [
                'order' => $order->reference,
                'article' => $article->slug,
                'error' => $e->getMessage(),
            ]);
            $this->checkout->markFailed($order, [], $e->getMessage(), $this->gateway->providerCode());

            return redirect()
                ->route('article.show', $article)
                ->withErrors(['payment' => 'Impossible de lancer le paiement pour le moment. Merci de réessayer dans quelques minutes.']);
        }

        // Persiste le session id gateway (cos-… pour Wave) sur le Payment.
        if ($init->accessCode !== null && $init->accessCode !== '') {
            Payment::where('order_id', $order->id)
                ->where('provider_reference', $order->reference)
                ->where('provider', $this->gateway->providerCode())
                ->latest('id')
                ->first()
                ?->update(['provider_transaction_id' => $init->accessCode]);
        }

        return redirect()->away($init->authorizationUrl);
    }
}
