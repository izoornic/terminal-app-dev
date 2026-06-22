# Plan upgrejda: Laravel 10 → 11 + PHP 8.2 → 8.4

> Datum kreiranja: 2026-04-28
> Polazna verzija: Laravel 10.x, PHP 8.2.30, Livewire 2.5
> Ciljana verzija: Laravel 11.x, PHP 8.4.x, Livewire 3.x

---

## Trenutno stanje zavisnosti

| Komponenta | Trenutno | Cilj |
| --- | --- | --- |
| PHP | 8.2.30 | 8.4.x |
| Laravel | 10.x | 11.x |
| Livewire | 2.5 | 3.x |
| Jetstream | 2.6 | 4.x |
| Sanctum | 3.2 | 4.x |
| PHPUnit | 9.5 | 11.x |
| Collision | 6.1 | 8.x |

---

## FAZA 0 — Priprema

- [x] Kreirati novu granu: `git checkout -b upgrade/laravel11`
- [x] Napraviti backup baze podataka
- [x] Pokrenuti testove i zabilježiti polazno stanje: `php artisan test`
- [x] Zabilježiti module za ručno testiranje: tiket flow, licence, bankomat, rezervacije dijelova
- [x] Pregledati `app/Console/Kernel.php` za scheduled commands
- [x] Pregledati `app/Http/Kernel.php` za custom middleware i grupe
- [x] Pregledati custom service slojeve i providere

### Nalazi — polazno stanje (2026-04-28)

**Testovi:** 1 pao (MySQL nedostupan van Dockera — očekivano), 1 prošao, 36 pending.
Testovi se moraju pokretati unutar Docker okruženja (`./vendor/bin/sail artisan test`).

**Console/Kernel.php — scheduled commands za migraciju u `routes/console.php`:**

- `eurorates:info` — pokreće se svaki dan u 08:05
- Registrovane komande: `EuroratesInfo`, `SyncPozicijaTipRoles`

**Http/Kernel.php — middleware za migraciju u `bootstrap/app.php`:**

| Tip | Klasa | Napomena |
| --- | --- | --- |
| Global | `TrustProxies` | custom klasa |
| Global | `PreventRequestsDuringMaintenance` | custom klasa |
| Global | `TrimStrings` | custom klasa |
| Web group | `EncryptCookies` | custom klasa |
| Web group | `VerifyCsrfToken` | custom klasa |
| Web group | `AuthenticateSession` | Jetstream |
| API group | `EnsureFrontendRequestsAreStateful` | **zakomentarisano** |
| Alias | `auth` → `Authenticate` | custom klasa |
| Alias | `guest` → `RedirectIfAuthenticated` | custom klasa |
| **Alias** | **`accessrole` → `EnsureUserRoleIsAllowedToAccess`** | **custom — kritično** |

**Providers (7) — u L11 se konsoliduju u jedan `AppServiceProvider`:**
`AppServiceProvider`, `AuthServiceProvider`, `BroadcastServiceProvider`,
`EventServiceProvider`, `FortifyServiceProvider`, `JetstreamServiceProvider`, `RouteServiceProvider`

**Custom service slojevi:**

- `app/PartsInventory/` — spare parts logika
- `app/Actions/` → `Bankomati/`, `Fortify/`, `Jetstream/`, `Licence/`, `Lokacije/`, `Rdelovi/`, `Terminali/`, `Tiket/`

---

## FAZA 1 — Livewire 2 → 3 ✅ KOMPLETIRANA (2026-05-12)

> Urađeno na Laravel 10 + Livewire 3.8.0 + Jetstream 4.0.5.
> Sljedeći korak: ručno testiranje UI-a, zatim FAZA 2.

### ⚠️ Livewire 3 config — `class_namespace`

Livewire 3 po defaultu traži komponente u `App\Livewire`. Pošto su sve klase u `App\Http\Livewire`, potrebno je publishati config i promijeniti namespace:

```bash
php artisan vendor:publish --tag=livewire:config
```

```php
// config/livewire.php
'class_namespace' => 'App\\Http\\Livewire',
```

### ⚠️ Alpine.js — dvostruko učitavanje

Livewire 3 bundluje Alpine.js interno. Ručni import u `resources/js/app.js` uzrokuje konflikt (`window.Alpine.cloneNode is not a function`).
**Fix:** Ukloniti iz `app.js`:

```js
// UKLONITI:
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
```

### ⚠️ `@entangle(...).defer` — uklonjen u Livewire 3

Jetstream modal komponenta (`resources/views/vendor/jetstream/components/modal.blade.php`) koristi `.defer` modifier koji više ne postoji.
**Fix:**

```diff
- show: @entangle($attributes->wire('model')).defer,
+ show: @entangle($attributes->wire('model')),
```

### ⚠️ `$dispatch` u blade templateovima — inter-component komunikacija

`wire:click="$dispatch('event', params)"` u blade templateu šalje samo browser CustomEvent bez Livewire konteksta — sibling komponente ga ne primaju pouzdano.
**Fix:** Koristiti PHP metodu koja poziva `$this->dispatch()`:

```php
// U PHP klasi:
public function selectNesto(int $id): void
{
    $this->selectedId = $id;
    $this->dispatch('event', id: $id);
}
```

```html
<!-- U blade templatu: -->
wire:click="selectNesto({{ $item->id }})"
```

### ⚠️ Jetstream 4 — Breaking change: `x-jet-*` komponente

Jetstream 4 je uklonio registraciju `jet-` prefiksa za Blade komponente (u v2 je to radio `JetstreamServiceProvider`).
**Fix:** `AppServiceProvider::boot()` registruje svih 29 komponenti via `BladeCompiler::component()`:

```php
$this->callAfterResolving(BladeCompiler::class, function ($blade) {
    foreach ([...] as $component) {
        $blade->component("vendor.jetstream.components.{$component}", "jet-{$component}");
    }
});
```

Razlog: `Blade::anonymousComponentPath(path, 'jet')` ne radi jer koristi `::` kao delimiter, ne `-`.

### 1.1 Skeniranje koda prije upgrejda

```bash
# Emit pozivi
grep -rn "->emit\(" app/Http/Livewire/ --include="*.php"

# wire:model.lazy i wire:model.defer u templateovima
grep -rn "wire:model\.lazy\|wire:model\.defer" resources/views/ --include="*.blade.php"

# Stari $listeners
grep -rn "protected \$listeners" app/Http/Livewire/ --include="*.php"

# Stari $rules
grep -rn "protected \$rules" app/Http/Livewire/ --include="*.php"

# Stari $queryString
grep -rn "protected \$queryString" app/Http/Livewire/ --include="*.php"
```

### 1.2 Upgrejd paketa

```bash
composer require livewire/livewire:^3.0
```

### 1.3 Mapa promjena Livewire 2 → 3

| Staro (v2) | Novo (v3) |
| --- | --- |
| `wire:model` (real-time) | `wire:model.live` |
| `wire:model.lazy` | `wire:model.blur` |
| `wire:model.defer` | `wire:model` (default je defer u v3) |
| `$this->emit('event')` | `$this->dispatch('event')` |
| `$this->emitTo('comp', 'ev')` | `$this->dispatch('ev')->to('comp')` |
| `$this->emitUp('event')` | `$this->dispatch('ev')` |
| `protected $listeners` | `#[On('event')]` atribut |
| `protected $rules` | `#[Rule]` atribut ili `rules()` metoda |
| `protected $queryString` | `#[Url]` atribut |

### 1.4 Čeklista po modulima

- [x] `app/Http/Livewire/` — korijenski komponenti (465×wire:model.live, 63×wire:model, 33×dispatch, 31×#[On])
- [x] `app/Http/Livewire/Bankomati/` — ATM komponenti
- [x] Tiket modul
- [x] Licence modul
- [x] Spare parts modul
- [x] Distributor modul
- [x] Jetstream 4.0.5 instaliran (Livewire 3 kompatibilan)

### 1.5 Provera

- [x] Ručno testirati users modul
- [x] Ručno testirati terminali workflow premesti, status, novi terminal, blacklista
- [x] Ručno testirati tiket workflow
- [x] Ručno testirati lokacije
- [x] Ručno testirati dodavanja novog distributera
- [x] Ručno testirati licence i kampanje
- [x] Ručno testirati Distributere
- [x] Ručno testirati PDF generisanje (dompdf)
- [x] Ručno testirati bankomat module
- [x] Ručno testirati rezervacije dijelova
- [x] Ručno testirati Excel exportovi
- [x] Ručno testirati Spatie Permission role/permission dodele
- [X] Ručno testirati menagment module i Google mape


---

## FAZA 2 — Laravel 10 → 11

### 2.1 Ažuriranje `composer.json`

Promijeniti verzije u `require` i `require-dev`:

```json
"require": {
    "php": "^8.2",
    "laravel/framework": "^11.0",
    "laravel/jetstream": "^4.0",
    "laravel/sanctum": "^4.0",
    "laravel/tinker": "^2.9"
},
"require-dev": {
    "phpunit/phpunit": "^11.0",
    "nunomaduro/collision": "^8.0"
}
```

```bash
composer update --with-all-dependencies
```

### 2.2 Nova struktura L11 — slim app skeleton

Laravel 11 ukida `Http/Kernel.php` i `Console/Kernel.php`.

| Staro (L10) | Novo (L11) |
| --- | --- |
| `app/Http/Kernel.php` | `bootstrap/app.php` |
| `app/Console/Kernel.php` | `routes/console.php` |
| `$middlewareGroups` u Kernel | `->withMiddleware()` u bootstrap/app.php |
| `$routeMiddleware` aliasi | `->withMiddleware(fn($m) => $m->alias([...]))` |
| 5 App Providera | jedan `AppServiceProvider` |

- [ ] Premjestiti custom middleware u `bootstrap/app.php`
- [ ] Premjestiti scheduled commands iz `Console/Kernel.php` u `routes/console.php`
- [ ] Provjeriti Sanctum middleware (`EnsureFrontendRequestsAreStateful`)
- [ ] Konsolidovati service providere u `AppServiceProvider`

### 2.3 Konfiguracijski fajlovi — merge sa novim defaultima

- [ ] `config/broadcasting.php` — dodan `reverb` driver
- [ ] `config/database.php` — nove opcije za MariaDB
- [ ] `config/mail.php` — provjeri format
- [ ] Pokrenuti: `php artisan lang:publish`

### 2.4 Model `$casts` — deprecated sintaksa

```php
// Staro (L10, radi ali deprecated upozorenje u L11)
protected $casts = ['is_active' => 'boolean'];

// Novo (L11)
protected function casts(): array {
    return ['is_active' => 'boolean'];
}
```

- [ ] Pregledati sve modele u `app/Models/` i ažurirati `$casts` property u metodu

### 2.5 Jetstream 4.x

```bash
composer require laravel/jetstream:^4.0
php artisan jetstream:install livewire
```

- [ ] Pregledati i testirati auth view templateove
- [ ] Provjeriti email verifikaciju i password reset flow

### 2.6 Kompatibilnost paketa sa L11

| Paket | Status | Akcija |
| --- | --- | --- |
| `maatwebsite/excel` ^3.1 | Radi | Nema promjena |
| `spatie/laravel-permission` ^6.23 | Radi | Nema promjena |
| `barryvdh/laravel-dompdf` ^2.0 | Radi | Nema promjena |
| `propaganistas/laravel-phone` ^5.3 | Radi | Nema promjena |
| `blade-ui-kit/blade-heroicons` ^2.6 | Radi | Nema promjena |
| `spatie/crypto` ^2.0 | Provjeriti | Testirati |
| `larswiegers/laravel-maps` ^0.19 | Neizvjesno | Provjeriti GitHub release |

```bash
# Dry-run provjera konflikta
composer require laravel/framework:^11.0 --dry-run 2>&1 | grep -i "conflict\|error"
```

### 2.7 Provjera nakon L11 upgrejda

- [ ] `php artisan optimize:clear`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan test`
- [ ] Ručno testirati sve module

---

## FAZA 3 — PHP 8.4

> Raditi tek kad je L11 potpuno stabilan na PHP 8.2.

### 3.1 Skeniranje deprecated poziva

```bash
grep -rn "FILTER_SANITIZE_STRING\|each(\|create_function\|money_format" app/ --include="*.php"

# Provjeri opšta upozorenja
php artisan about
```

### 3.2 Upgrejd PHP verzije

- [ ] Promijeniti PHP verziju u `docker-compose.yml` (lokalno) ili na cPanel (produkcija)
- [ ] Ažurirati `composer.json`: `"php": "^8.4"`
- [ ] Pokrenuti `composer update`

### 3.3 Ključne promjene u PHP 8.4

- Property hooks (nova sintaksa — ne utiče na stari kod)
- Neke `array_*` funkcije su deprecated
- `bcmath` postaje bundled proširenje
- Provjeri `mb_` funkcije

### 3.4 Provjera

- [ ] `php artisan test`
- [ ] Ručno testirati sve module
- [ ] Provjeriti vendor pakete za PHP 8.4 kompatibilnost

---

## FAZA 4 — Post-upgrade čišćenje

- [ ] `php artisan optimize:clear`
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] Ažurirati `phpunit.xml` za PHPUnit 11 format
- [ ] Ručno testirati: tiket workflow
- [ ] Ručno testirati: licence i naplata
- [ ] Ručno testirati: bankomat module
- [ ] Ručno testirati: rezervacije i transfer dijelova
- [ ] Ručno testirati: Excel exportovi
- [ ] Ručno testirati: PDF generisanje (dompdf)
- [ ] Ručno testirati: Spatie Permission role/permission dodjele
- [ ] Ručno testirati: autentifikacija i email verifikacija
- [ ] Deploy na staging i finalni test

---

## Pregled rizika i procjena trajanja

| Faza | Rizik | Procjena trajanja |
| --- | --- | --- |
| Faza 0 — Priprema | Nizak | 1h |
| Faza 1 — Livewire 2→3 | **Visok** | 2–5 dana |
| Faza 2 — Laravel 11 | Srednji | 1–2 dana |
| Faza 3 — PHP 8.4 | Nizak | 2–4h |
| Faza 4 — Čišćenje | Nizak | 2–4h |

---

## Korisni linkovi

- [Laravel 11 Upgrade Guide](https://laravel.com/docs/11.x/upgrade)
- [Livewire v3 Upgrade Guide](https://livewire.laravel.com/docs/upgrading)
- [Jetstream v4 Changelog](https://github.com/laravel/jetstream/blob/main/CHANGELOG.md)
- [PHP 8.4 Migration Guide](https://www.php.net/manual/en/migration84.php)
