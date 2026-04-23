# Intégration Wave Business — GÉNIE MARKETING Mag

Wave Business (Côte d'Ivoire / Sénégal / UEMOA) est le gateway de paiement
principal pour les abonnements. Ce document couvre l'architecture de
l'intégration, la configuration, le mode test local, et la mise en production.

---

## 1. Vue d'ensemble

### Pourquoi Wave

| Critère            | Wave                      | Paystack (retiré)       |
|--------------------|---------------------------|-------------------------|
| Mobile money CI    | **Natif (Wave wallet)**   | Via partenaires tiers   |
| Frais transaction  | 1 % marchand              | ~2.9 % + frais fixes    |
| Devise XOF         | Native                    | Disponible              |
| UX                 | Hosted checkout `pay.wave.com` + deep-link app | Redirection hosted     |

Wave reste branchable via le contrat `App\Contracts\PaymentGateway` —
l'ancien `PaystackGateway` est conservé comme fallback activable en changeant
`PAYMENT_GATEWAY=paystack`.

### Flux de paiement

```
Utilisateur                   Laravel                        Wave
-----------                   -------                        ----
POST /abonnement/…/inscription
                          →   CheckoutController::process
                              ├─ createOrderForPlan()        (ordre pending, payment pending)
                              ├─ WaveGateway::initialize()   →  POST /v1/checkout/sessions
                                                             ←  { id: cos-…, wave_launch_url }
                              ├─ Payment.provider_transaction_id ← cos-…
                              └─ 302 → pay.wave.com/c/cos-…
                                                             ↓ (paiement Wave app / USSD)
                                                             └─ success_url / error_url
GET /paiement/callback?reference=GM-…
                          →   CheckoutController::callback
                              └─ WaveGateway::verify()       →  GET /v1/checkout/sessions/cos-…
                                                             ←  { payment_status: succeeded }
                              └─ CheckoutService::finalizeOrder()
                                 ├─ Order → paid
                                 ├─ Subscription active
                                 ├─ Invoice
                                 └─ Email SubscriptionConfirmed

[Async]                   ←   POST /webhooks/wave  [Wave-Signature: t=…,v1=…]
                              WaveWebhookController
                              └─ parseWebhook() (HMAC-SHA256 vérifié)
                              └─ CheckoutService::finalizeOrder() [idempotent]
```

Les deux chemins (callback + webhook) sont **idempotents** :
`CheckoutService::finalizeOrder` lockForUpdate + short-circuit si
`order.status === Paid`. Le premier qui arrive gagne.

---

## 2. Composants

| Fichier                                                             | Rôle                                                 |
|---------------------------------------------------------------------|------------------------------------------------------|
| `app/Services/Payment/WaveGateway.php`                              | Client Wave + parsing webhooks HMAC-SHA256           |
| `app/Services/Payment/FakePaymentGateway.php`                       | Stand-in local — simulateur hosted checkout          |
| `app/Providers/PaymentServiceProvider.php`                          | DI du gateway selon `PAYMENT_GATEWAY` et environnement |
| `app/Http/Controllers/WaveWebhookController.php`                    | Endpoint POST `/webhooks/wave`                       |
| `app/Http/Middleware/VerifyWaveWebhookIp.php`                       | Whitelist IP optionnelle (alias `wave.ip`)           |
| `app/Http/Controllers/Public/CheckoutController.php`                | Tunnel plan → paiement → callback                    |
| `app/Services/Commerce/CheckoutService.php`                         | Orchestration Order/Payment/Subscription/Invoice     |
| `resources/views/public/checkout-simulator.blade.php`               | UI simulateur dev                                    |
| `config/services.php`                                               | Bloc `services.wave`                                 |

---

## 3. Configuration environnement

### Variables `.env`

```env
# Gateway sélectionné : wave (défaut) | paystack | fake
PAYMENT_GATEWAY=wave

# Clé API Wave (format : wave_ci_prod_... en prod, wave_ci_test_... en test)
WAVE_API_KEY=wave_ci_test_xxxxxxxxxxxxxxxxxxxx

# Secret webhook fourni par Wave au moment de l'enregistrement du webhook URL
WAVE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxx

# Base URL — NE PAS modifier sauf sandbox Wave
WAVE_BASE_URL=https://api.wave.com

# Whitelist IP sortantes Wave (optionnel — laisser vide par défaut)
WAVE_WEBHOOK_IPS=
```

### Récupérer les clés

1. Créer un compte marchand sur **https://business.wave.com**
2. Dans le dashboard : **Developer Portal → API Keys → Create Key**
3. Copier la clé (`wave_ci_test_...` ou `wave_ci_prod_...`) dans `WAVE_API_KEY`
4. **Developer Portal → Webhooks → Add Endpoint**
   - URL : `https://votre-domaine.tld/webhooks/wave`
   - Événements à cocher :
     - `checkout.session.completed`
     - `checkout.session.payment_failed`
5. Wave affiche un **webhook secret** (`whsec_…`) à copier dans `WAVE_WEBHOOK_SECRET`
   — **il n'est affiché qu'une seule fois**, le sauvegarder en coffre-fort
   (1Password, Bitwarden, Hostinger Vault, etc.).

### Bascule de provider

```env
PAYMENT_GATEWAY=paystack   # repasse sur Paystack si besoin (fallback)
PAYMENT_GATEWAY=fake       # force le simulateur local, même en prod (test)
```

En production, `fake` ne doit JAMAIS être utilisé. Le bootstrap refuse le
fake automatique si `APP_ENV=production`.

---

## 4. Test local avec fake data

Le projet embarque un simulateur hosted checkout qui remplace Wave tant que
la clé API est un placeholder — aucune transaction réelle, aucun appel HTTP
sortant.

### Activer le simulateur

**Option A** — placeholder (recommandé en dev) :

```env
PAYMENT_GATEWAY=wave
WAVE_API_KEY=wave_test_placeholder
WAVE_WEBHOOK_SECRET=wave_webhook_placeholder
```

**Option B** — force :

```env
PAYMENT_GATEWAY=fake
```

### Tester le flux

```bash
# 1. Lancer l'app
cd web && php artisan serve

# 2. Aller sur http://localhost:8000/abonnement
# 3. Choisir un plan (Digital / Combo / Entreprise)
# 4. Remplir le formulaire d'inscription
# 5. Être redirigé vers http://localhost:8000/paiement/simulateur/GM-…
# 6. Cliquer sur "Payer 24 000 XOF avec Wave" → retour /compte, abonnement actif
#    OU "Simuler un échec" → retour /abonnement avec message d'erreur
```

### Données de test Wave (sandbox)

Wave fournit une sandbox distincte en remplaçant `wave_ci_test_` par la clé
test dans le dashboard. Numéros mobile money de test fournis par Wave
Business (à demander au support Wave — ils ne sont pas publics).

### Tester le webhook en local (ngrok)

```bash
# Terminal 1 : expose le site local
ngrok http 8000
# → https://abcd-1234.ngrok-free.app

# Dashboard Wave → Webhooks → Endpoint URL :
#   https://abcd-1234.ngrok-free.app/webhooks/wave

# Terminal 2 : suivre les logs
php artisan pail
```

Pour injecter un webhook sans passer par Wave :

```bash
BODY='{"type":"checkout.session.completed","data":{"id":"cos-test","client_reference":"GM-2026-000001","amount":"24000","currency":"XOF","payment_status":"succeeded","checkout_status":"complete","payer_mobile":"+2250102030405"}}'
SECRET=$(grep WAVE_WEBHOOK_SECRET web/.env | cut -d= -f2)
TS=$(date +%s)
SIG="t=${TS},v1=$(printf '%s%s' "$TS" "$BODY" | openssl dgst -sha256 -hmac "$SECRET" -r | cut -d' ' -f1)"

curl -X POST http://localhost:8000/webhooks/wave \
  -H "Content-Type: application/json" \
  -H "Wave-Signature: $SIG" \
  -d "$BODY"
# → {"ok":true}
```

---

## 5. Particularités d'implémentation

### Montants XOF

Wave manipule le XOF en **unité entière** (pas de sous-unité : 1 XOF ≠ 100
centimes Wave). Le projet stocke `total_cents` en centimes (compat Paystack et
devises à décimales). Le gateway fait la conversion :

- **initialize** : `amount = intdiv(total_cents, 100)` → string `"24000"`
- **verify / webhook** : `amountCents = (int)$wave_amount * 100` → entier `2_400_000`

La défense montant/devise de `CheckoutService::finalizeOrder` reste donc
inchangée et protège contre le tampering.

### Signature webhook

Wave utilise un format distinct de Paystack :

```
Wave-Signature: t=1714852800,v1=abcdef...
```

Vérification (`WaveGateway::parseWebhook`) :

1. Parser `t=` (timestamp Unix) et `v1=` (HMAC-SHA256)
2. Rejeter si `t` est hors tolérance (passé > 5 min ou futur > 30 s) — anti-replay
3. Calculer `expected = hmac_sha256(t . rawBody, WAVE_WEBHOOK_SECRET)`
4. `hash_equals(expected, v1)` sinon rejeter

### Résolution du session id

Wave expose `GET /v1/checkout/sessions/:id` (pas `/sessions?reference=…`). Le
`CheckoutController::process` persiste le session id Wave (`cos-…`) dans
`payments.provider_transaction_id` immédiatement après `initialize()`, pour
que `verify()` puisse le récupérer lors du callback.

### success_url / error_url

Wave appelle ces URLs sans ajouter de query params. Le gateway ajoute
lui-même `?reference=GM-…&status=success|failed` pour que
`CheckoutController::callback` retrouve la commande.

---

## 6. Sécurité

| Défense                                | Implémentation                                    |
|----------------------------------------|---------------------------------------------------|
| Vérification signature HMAC-SHA256     | `WaveGateway::parseWebhook()`                     |
| Anti-replay timestamp (±5 min / +30 s) | Vérification `t=` dans `parseWebhook()`           |
| Protection amount/currency tampering   | `CheckoutService::finalizeOrder()` match strict   |
| Idempotence callback ↔ webhook         | `lockForUpdate()` + short-circuit `status = Paid` |
| Whitelist IP (optionnelle)             | `VerifyWaveWebhookIp` (`wave.ip`)                 |
| CSRF exclu sur webhook                 | `bootstrap/app.php` (except `webhooks/*`)         |
| Throttle webhook                       | `throttle:60,1` dans `routes/web.php`             |
| CSP `connect-src` restreint            | `SecurityHeaders` autorise `api.wave.com`         |

**Référence clé** : stocker `WAVE_API_KEY` et `WAVE_WEBHOOK_SECRET` en
coffre-fort, jamais en git, jamais en log. Rotation recommandée : annuelle
ou à chaque départ d'un développeur ayant eu accès.

---

## 7. Mise en production (Hostinger Mutualisé)

1. **Dashboard Wave** → passer l'API key de `test` à `prod`, copier la clé.
2. **Dashboard Wave → Webhooks** → créer l'endpoint `https://geniemag.ci/webhooks/wave` en mode prod, copier le secret.
3. **SSH Hostinger** : éditer `geniemag-app/.env` :
   ```env
   PAYMENT_GATEWAY=wave
   WAVE_API_KEY=wave_ci_prod_xxxxxxxxxxxxxxxxxxxx
   WAVE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxx
   ```
4. **Vider les caches** :
   ```bash
   php artisan config:cache
   php artisan route:cache
   ```
5. **Check-list automatique** :
   ```bash
   php artisan gm:pre-launch
   # → "Gateway de paiement configuré  (wave)" doit être ✓
   ```
6. **Smoke test réel** : faire un paiement de 100 XOF depuis un numéro Wave
   réel, vérifier :
   - `orders.status = paid`
   - `subscriptions` créé
   - `invoices` émise
   - Email `SubscriptionConfirmed` reçu
   - Entry webhook loggué dans `storage/logs/laravel-*.log`

---

## 8. Dépannage

| Symptôme                                     | Cause probable                                        | Solution                                     |
|----------------------------------------------|-------------------------------------------------------|----------------------------------------------|
| `Wave initialize failed (HTTP 401)`          | `WAVE_API_KEY` invalide ou de test en prod            | Vérifier la clé dans dashboard Wave          |
| `Wave initialize failed (HTTP 422)`          | Montant 0 ou devise non supportée                     | Vérifier le plan (`price_cents > 0`, `currency='XOF'`) |
| Webhook `signature HMAC invalide`            | `WAVE_WEBHOOK_SECRET` désynchronisé                   | Rotation du secret dans dashboard Wave       |
| Webhook `timestamp hors tolérance`           | Horloge serveur décalée (> 5 min)                     | `sudo ntpdate -u pool.ntp.org` ou service NTP |
| Webhook `order not found`                    | Rejeu d'un webhook d'un environnement précédent       | 202 renvoyé → pas de retry loop, log Sentry  |
| `Paiement non finalisé : Insufficient funds` | Client sans solde Wave                                | UX — proposer de réessayer plus tard         |
| Callback loop infini                         | `success_url` pas en HTTPS ou hors domaine            | Vérifier `APP_URL` et trust proxy config     |

Logs clés à surveiller :

```bash
tail -f storage/logs/laravel-$(date +%Y-%m-%d).log | grep -i wave
```

Si Sentry est configuré (`SENTRY_LARAVEL_DSN`), les `Log::critical` et
`Log::warning` remontent automatiquement.

---

## 9. Tests

```bash
# Tests unitaires (HMAC, anti-replay, parsing)
vendor/bin/pest tests/Unit/WaveGatewayTest.php

# Tests Feature (init HTTP mock, verify BDD)
vendor/bin/pest tests/Feature/WaveGatewayTest.php

# Flux complet (Order → Payment → Subscription → Invoice)
vendor/bin/pest tests/Feature/CheckoutServiceTest.php

# Suite complète
vendor/bin/pest
```

Coverage attendu : signature HMAC-SHA256, anti-replay timestamp, conversion
XOF unit/cents, mapping status Wave → `PaymentStatus`, résolution session id
via BDD, normalisation payload webhook.

---

## 10. Références

- Wave Business API — https://docs.wave.com/business
- Wave Checkout API — https://docs.wave.com/checkout
- Portail développeur Wave — https://business.wave.com/dev-portal
- Support Wave Côte d'Ivoire — toll-free **1315**
- Code source de l'intégration :
  - `web/app/Services/Payment/WaveGateway.php`
  - `web/app/Http/Controllers/WaveWebhookController.php`
- Spec `PaymentGateway` contract — `web/app/Contracts/PaymentGateway.php`
