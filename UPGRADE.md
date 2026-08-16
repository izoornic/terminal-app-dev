# Plan upgrejda: Laravel 10 → 13 + PHP 8.2 → 8.4

> Datum kreiranja: 2026-04-28
> Polazna verzija: Laravel 10.x, PHP 8.2.30, Livewire 2.5
> Ciljana verzija: Laravel 13.x, PHP 8.4.x, Livewire 3.x
>
> **Redoslijed (od 2026-06-23):** L11 i PHP su razdvojeni jer L13 zahtijeva PHP ≥ 8.3.
> Zato PHP 8.4 ide prije frameworka, pa onda inkrementalno L12 → L13:
> FAZA 3 (PHP 8.4) → FAZA 4 (L12) → FAZA 5 (L13) → FAZA 6 (čišćenje).

---

## Trenutno stanje zavisnosti

| Komponenta | Trenutno | Cilj |
| --- | --- | --- |
| PHP | 8.2.30 | 8.4.x |
| Laravel | 10.x | 13.x |
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
- [x] Ručno testirati Spatie Permission role/permission dodele — pokriveno automatskim testom `tests/Feature/RolePermissionTest.php` (7 testova, 56 asercija)
- [X] Ručno testirati menagment module i Google mape


---

## FAZA 2 — Laravel 10 → 11 ✅ KOMPLETIRANA (2026-06-22)

> Urađeno: Laravel 11.54.0 + Jetstream 5.5.3 + Sanctum 4.3.2 + PHPUnit 11.5.55, PHP 8.2.30.
> Aplikacija boot-uje; config/route/view cache prolaze; Unit testovi prolaze.
> Sljedeći korak: pokrenuti pun test suite u Dockeru/Sailu, zatim ručni UI test, pa FAZA 3 (PHP 8.4).

### ⚠️ Ispravka plana — Jetstream verzija

Jetstream 4.x je **Laravel 10 only** (`requires illuminate ^10.17`). Za L11 je potreban **Jetstream ^5.0** (instaliran 5.5.3). Plan je prvobitno navodio ^4.0 — pogrešno.

### ⚠️ `jetstream:install` NIJE pokretan

`php artisan jetstream:install livewire` bi prepisao Livewire-3-prilagođene Jetstream view-ove iz Faze 1. Uradili smo samo composer bump.

### ⚠️ `$casts` property — NIJE migriran (nepotrebno)

`protected $casts` **radi bez deprecation-a u L11** — `casts()` metoda je samo opcija. Masovna konverzija svih modela je nepotrebna i rizična, pa je preskočena. Tačka 2.4 plana se ne primjenjuje.

### ⚠️ Nevažeći heroicon-i (zatečeni bug, otkriven kroz `view:cache`)

`view:cache` kompajlira sve blade-ove odjednom i otkrio je 2 nepostojeće ikone (rušile bi render tih view-ova):

- `heroicon-o-user-card` → `heroicon-o-identification` (6 mjesta, bankomati)
- `heroicon-o-pin-plus` → `heroicon-o-map-pin` (2 mjesta, bankomati/lokacije — kontekst: koordinate)

### Nalazi — Faza 2 (2026-06-22)

**Slim skeleton:**

- `bootstrap/app.php` — withRouting (web/api/console + health `/up`), trustProxies (AWS ELB headeri), trimStrings except (password polja), web grupa append `Jetstream\AuthenticateSession`, api append `throttle:api`, aliasi `auth`/`guest`/`accessrole`.
- `bootstrap/providers.php` — AppServiceProvider, FortifyServiceProvider, JetstreamServiceProvider.
- `routes/console.php` — `Schedule::command('eurorates:info')->dailyAt('08:05')`. Komande se auto-otkrivaju iz `app/Console/Commands`.
- **Obrisano:** `app/Http/Kernel.php`, `app/Console/Kernel.php`, `RouteServiceProvider`, `AuthServiceProvider`, `EventServiceProvider`, `BroadcastServiceProvider`, te middleware `TrustProxies`/`TrimStrings`/`EncryptCookies`/`VerifyCsrfToken`/`PreventRequestsDuringMaintenance` (custom-i bez prave logike).
- **Konsolidovano u `AppServiceProvider::boot()`:** observeri (User, PozicijaTip, PartMovement, PartStock, Blokacija), `api` rate limiter, `Registered → SendEmailVerificationNotification`, jet-* komponente, `money` Blade direktiva.
- **Zadržano:** custom middleware `Authenticate`, `RedirectIfAuthenticated`, `EnsureUserRoleIsAllowedToAccess` (alias `accessrole`).

**Ostale izmjene:**

- `config/app.php` — uklonjen `providers[]` niz (sada `bootstrap/providers.php`); zadržan `editor` ključ i `aliases` (PDF).
- `config/sanctum.php` — middleware blok pokazivao na obrisane custom klase; prebačen na Sanctum 4 default (`authenticate_session`, `encrypt_cookies`, `validate_csrf_token`).
- `config/fortify.php` + `RedirectIfAuthenticated` — `RouteServiceProvider::HOME` → literal `/dashboard`.
- `tests/Feature/{Authentication,Registration,EmailVerification}Test.php` — `RouteServiceProvider::HOME` → `/dashboard`.
- `phpunit.xml` — `<coverage><include>` → `<source>` (PHPUnit 11 schema).

**Verifikacija (host, bez MySQL-a):**

- `php artisan about` → Laravel 11.54.0 ✓
- `route:list` → 86 ruta, `accessrole`/`auth` aliasi se resolve-uju ✓
- `schedule:run` → radi ✓ (`schedule:list` ima kozmetički file-cache-lock bug, ne utiče na cron)
- `config:cache` + `route:cache` + `view:cache` → svi prolaze ✓
- Unit suite (PHPUnit 11) → prolazi ✓
- Feature suite (u kontejneru `terminal-app`, `testing` baza) → **28 prošlo, 8 preskočeno, 1 rizičan, 0 palih** ✓
  - Skipped: feature-gated (registracija, email verifikacija, API tokeni isključeni u konfiguraciji)
  - Risky: Jetstream default `other browser sessions can be logged out` (bez asertacija)

**Zatečeni bugovi popravljeni da bi Feature suite prošao (nisu vezani za L11):**

- `app/Actions/Jetstream/DeleteUser.php` — `dd($user)` zaostao od prvog commita (2024-11-16); rušio bi brisanje korisnika u produkciji i ubijao test proces. Uklonjeno.
- `database/factories/PartTypeFactory.php` — `category_id` generisao nasumičan int (FK na `terminal_tips`) → kršio FK u praznoj test bazi. Prebačeno na `TerminalTip::factory()` (uz `complete()` state).

**Napomena (nije blokirajuće):** testovi koriste `/** @test */` doc-anotacije — PHPUnit 12 ih neće podržavati (treba `#[Test]` atribut). Ostavljeno za kasnije.

**Pokretanje Feature testova:** unutar kontejnera `docker exec terminal-app php artisan test` (DB_HOST=mysql radi unutra), ili na hostu uz `DB_HOST=0.0.0.0` u .env.

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

- [x] Premjestiti custom middleware u `bootstrap/app.php`
- [x] Premjestiti scheduled commands iz `Console/Kernel.php` u `routes/console.php`
- [x] Provjeriti Sanctum middleware (`EnsureFrontendRequestsAreStateful` — i dalje zakomentarisano, nije potrebno)
- [x] Konsolidovati service providere u `AppServiceProvider`

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

- [x] `php artisan optimize:clear`
- [x] `php artisan config:cache` — OK
- [x] `php artisan route:cache` — OK
- [x] `php artisan view:cache` — OK (sve blade-ove kompajlira)
- [x] `php artisan test` — 29 prošlo, 8 skipped, 1 risky, 0 palih (u kontejneru)
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
- [x] Ručno testirati Spatie Permission role/permission dodele — pokriveno automatskim testom `tests/Feature/RolePermissionTest.php` (7 testova, 56 asercija)
- [x] Ručno testirati menagment module i Google mape
- [x] Ručno testirati sve module — **na korisniku** (UI test u browseru)

> ⚠️ Pri pokretanju testova config NE smije biti keširan — keširan config pregazi `phpunit.xml` env override-e (`DB_DATABASE=testing`) i testovi bi išli na pravu bazu. Redoslijed: cache (provjera) → `optimize:clear` → `test`.

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

## FAZA 4 — Laravel 11 → 12 ✅ KOMPLETIRANA (2026-08-15)

> Preduslov: FAZA 3 završena — aplikacija stabilna na PHP 8.4.
> Laravel 12 je namjerno minimalan release (uglavnom bump zavisnosti, malo breaking changes).
> Ovdje je L12 **checkpoint**, ne krajnja destinacija — odmah slijedi L13 (FAZA 5).
>
> **Rezultat:** `laravel/framework` `11.x-dev` → **`v12.66.0`** (stabilan tag; ranije je bio prikovan
> na dev granu). Uklonjen `illuminate/json-schema`. Nijedan drugi paket nije trebalo dirati.
> **`composer audit` sada čist** — tri L11 CVE-a (Signed URL path confusion, CRLF u email rule)
> nestala su s prelaskom na 12.x.

### 4.1 Provjera ekosistema prije bumpa ✅

Svi paketi već deklarišu L12 podršku — **nema blokera**, uključujući i `laravel-maps` koji je bio označen kao rizik:

| Paket | Instalirano | Podržava | Status |
| --- | --- | --- | --- |
| laravel/jetstream | v5.5.3 | ^11\|^12\|^13 | ✅ |
| livewire/livewire | v3.8.4 | ^10\|^11\|^12\|^13 | ✅ |
| laravel/sanctum | v4 | ✅ | ✅ |
| laravel/fortify | v1 | ✅ | ✅ |
| spatie/laravel-permission | 6.25.0 | ^11\|^12\|^13 | ✅ |
| maatwebsite/excel | 3.1.70 | ^11\|^12\|^13 | ✅ |
| barryvdh/laravel-dompdf | v3.1.2 | ^11\|^12\|^13 | ✅ |
| blade-ui-kit/blade-icons | 1.10.1 | ^11\|^12\|^13 | ✅ |
| **larswiegers/laravel-maps** | v0.19 | ^11\|**^12** (nema ^13) | ✅ za L12, **⚠️ bloker za L13** |
| **propaganistas/laravel-phone** | 5.3.6 | ^11\|**^12** (nema ^13) | ✅ za L12, **⚠️ bloker za L13** |

```bash
# Dry-run provjera konflikta
composer require laravel/framework:^12.0 -W --dry-run
```

### 4.2 Upgrejd ✅

```bash
composer require laravel/framework:^12.0 -W
php artisan optimize:clear
```

### 4.3 Ključne provjere iz zvaničnog 12.x upgrade guide-a ✅

Prošao cijeli guide; ništa od breaking changes ne pogađa ovu aplikaciju:

- [x] **Carbon 3** — već je bio instaliran (3.13.2) prije ovog bumpa, `composer.lock` ga nije dirao →
      semantika `diffIn*` (float, predznačeno) nije promijenjena u ovoj fazi. Formatiranje/parsiranje
      provjereno kroz `App\Http\Helpers` (`datumKalendarNow`, `addMonthsToDate`) — radi.
- [x] **`image` validacijsko pravilo (SVG)** — aplikacija **ne koristi** `image` pravilo nigdje u `app/`.
- [x] **Models i UUIDv7** (medium impact) — nema `HasUuids`/`HasUlids` ni u jednom modelu.
- [x] **Multi-schema DB inspecting** — nema poziva `Schema::getTables/getViews/getTypes/getTableListing`.
- [x] **Local disk root → `storage/app/private`** — `config/filesystems.php` **eksplicitno** definiše
      `local` disk s `root => storage_path('app')`, pa promjena defaulta ne utiče.
- [x] **Route precedence (duplikati imena)** — 77 imenovanih ruta, nula duplikata
      (jedini duplirani `name('dashboard')` u `routes/web.php:43` je zakomentarisan).
- [x] **`mergeIfMissing()` s dot-notacijom** — nema upotrebe u kodu.
- [x] **Blueprint/Grammar konstruktori** — nema `new Blueprint`, `setConnection()` ni `withTablePrefix()`.
- [x] **Container: default vrijednosti class-property zavisnosti** — nema pogođenih konstruktora.

### 4.4 Verifikacija ✅

- [x] `php artisan test` — **43 prošlo, 8 skipped, 1 risky** (131 asertacija, 16s). Identično stanju
      prije bumpa; skipped/risky su Jetstream testovi za isključene feature (API tokeni, registracija,
      email verifikacija) + `BrowserSessionsTest` bez asertacija — sve pre-postojeće.
- [x] `php artisan config:cache && php artisan route:cache && php artisan view:cache` — sve tri prolaze
      (`view:cache` kompajlira sve Blade šablone, što pokriva i `<x-maps-google>` komponentu).
- [x] HTTP smoke (iz kontejnera): `/login` → 200 s Livewire assetima, `/dashboard` → 302 na login.
- [x] **PDF (dompdf v3)** — `app('dompdf.wrapper')` generiše validan `%PDF-` izlaz.
- [x] **Excel** — `LicencaNaplataExport` generiše XLSX (~19 KB) nad stvarnim podacima.
- [ ] Ručno testirati u browseru: tiket, licence, bankomat, rezervacije/transfer, auth
- [ ] Ručno provjeriti `larswiegers/laravel-maps` mape (menadžment modul — Google Maps se renderuje u browseru)

> ℹ️ Napomena o lokalnom okruženju: na WSL hostu port 80 drži lokalni Apache, pa `curl localhost/...`
> ne pogađa Sail. Smoke testove pokretati **iz kontejnera**: `docker compose exec laravel.test curl ...`

---

## FAZA 5 — Laravel 12 → 13 ✅ KOMPLETIRANA (2026-08-15)

> Preduslov: FAZA 4 završena — aplikacija stabilna na L12 + PHP 8.4.
> ⚠️ Laravel 13 zahtijeva **PHP ≥ 8.3** — zato PHP 8.4 (FAZA 3) ide prije frameworka.
>
> **Rezultat:** `laravel/framework` **v12.66.0 → v13.25.0**, PHP 8.4.24, 43 testa zelena.
> `composer audit` čist. Symfony stack prešao 7.4 → 8.1 (vidi 5.2).

### 5.1 Provjera ekosistema prije bumpa ✅

Oba blokera označena u FAZI 4 **riješena su upstream** u međuvremenu:

| Paket | Bilo | Sad | Ishod |
| --- | --- | --- | --- |
| larswiegers/laravel-maps | v0.19 (do `^12.0`) | **v0.21** (2026-07-01, `^13.0`) | ✅ bump — ali nosi breaking change, vidi 5.5 |
| propaganistas/laravel-phone | 5.3.6 (do `^12.0`) | 6.0.3 (`^13.0`) | ✅ **paket uklonjen** — vidi 5.2 |
| laravel/tinker | ^2.9 (do `^12.0`) | **^3.0** (v3.0.2) | ✅ bump |
| laravel/boost | ^1.1 (roster ide do `^12.0`) | **^2.5** (v2.5.3, roster v1.0.0) | ✅ bump — bio je stvarni bloker |
| phpunit/phpunit | ^11 | **^12** (12.5.33) | ✅ bump, traži ga L13 guide |
| nunomaduro/collision | ^8 | ^8 (v9 ne postoji) | ✅ ostaje |
| Jetstream, Livewire, sanctum, fortify, spatie/permission, excel, dompdf, blade-icons, ignition, sail | — | — | ✅ već deklarisali `^13.0` |

### 5.2 Upgrejd ✅

```bash
composer remove propaganistas/laravel-phone          # neiskorišćen, vidi ispod
composer require larswiegers/laravel-maps:^0.21 -W
composer require --dev laravel/boost:^2.5 -W
composer require laravel/framework:^13.0 laravel/tinker:^3.0 -W
composer remove symfony/serializer symfony/property-access symfony/property-info symfony/type-info -W
composer require --dev phpunit/phpunit:^12.0 -W
php artisan optimize:clear
```

**`propaganistas/laravel-phone` uklonjen** (odluka korisnika, 2026-08-15). Razlog: paket nije imao
**nijednu aktivnu upotrebu** — sva 4 `phone:` validacijska pravila su zakomentarisana
(`Prijava.php:66`, `Bankomati/Lokacije.php:248,286`, `Bankomati/Komponente/KontaktOsobe.php:37`),
nema `config/phone.php`, nema importa. Uklonjen je i tranzitivni `giggsey/libphonenumber-for-php-lite`.
⚠️ Ako se ta pravila ikad odkomentarišu bez vraćanja paketa, Laravel će baciti
`BadMethodCallException: Method ... validatePhone does not exist`. Vraćanje:
`composer require propaganistas/laravel-phone:^6.0`.

**Symfony pinovi iz FAZE 3 skinuti.** L13 povlači Symfony 8.1 za console/http-foundation/http-kernel/
mailer/mime/process/routing/uid/var-dumper/finder/error-handler. Pinovi na `^7.4` za
`serializer|property-access|property-info|type-info` (dodati u FAZI 3 da se izbjegne miješani stack)
sad su davali **obrnut efekat** — držali su ta 4 paketa na 7.4 dok je ostatak otišao na 8.1.
Uklonjeni su iz `composer.json`; nema direktne upotrebe u `app/` (samo tranzitivno kroz
`web-auth/webauthn-lib`, koji prihvata `^6.4|^7.0|^8.0`). Sad je Symfony konzistentno 8.1.

### 5.3 Breaking changes iz zvaničnog 13.x upgrade guide-a ✅

- [x] **Request Forgery Protection** (high impact) — `VerifyCsrfToken` → `PreventRequestForgery`
      + provjera `Sec-Fetch-Site` zaglavlja. **Ne pogađa nas**: custom `VerifyCsrfToken` klasa je
      nestala još u FAZI 2 (L11), `bootstrap/app.php` je ne pominje, a u `app/Http/Middleware/`
      su ostale samo `Authenticate`, `EnsureUserRoleIsAllowedToAccess`, `RedirectIfAuthenticated`, `TrustHosts`.
- [x] **Cache `serializable_classes`** (medium impact) — novi default `false` u framework configu.
      **Ne pogađa nas**: `config/cache.php` uopšte nema taj ključ, a `CacheManager::getSerializableClasses()`
      radi `?? null`, pa store-ovi preskaču `allowed_classes` ograničenje → staro ponašanje ostaje.
      App ne kešira PHP objekte (nema `Cache::put`/`remember` u `app/`), driver je `file`.
      ℹ️ *Opciono za kasnije:* dodati `'serializable_classes' => false` u `config/cache.php` kao hardening.
- [x] **Pagination Bootstrap view names** — nema referenci na `pagination::default` / `simple-default`.
- [x] **Domain route registration precedence** — nema nijedne `->domain()` rute.
- [x] **Manager `extend` callback binding** — nema `::extend(` u `app/`.
- [x] **`QueueBusy` `$connection` → `$connectionName`**, **`JobAttempted` `$exceptionOccurred` → `$exception`** —
      nema listenera za te evente (`QUEUE_CONNECTION=sync`).
- [x] **`withScheduling` timing** — ne koristi se; scheduling je u `routes/console.php`
      (`Schedule::command('eurorates:info')->dailyAt('08:05')` — provjereno da je preživio).
- [x] **`Js::from` sad koristi `JSON_UNESCAPED_UNICODE`** — jedina upotreba je `@js($recoveryCodes)`
      u `two-factor-recovery.blade.php`; recovery kodovi su ASCII, pa nema razlike.
- [x] **`Str` factories reset između testova** — testovi ne koriste custom UUID/ULID/random factory.
- [x] **Default password reset subject** — nema override-a stringa „Reset Password Notification".

### 5.4 Verifikacija ✅

- [x] `php artisan test` — **43 prošlo, 8 skipped, 1 risky** (131 asertacija). Identično L11 i L12 stanju.
- [x] `config:cache` + `route:cache` + `view:cache` — sve tri prolaze.
- [x] HTTP smoke (iz kontejnera): `/login` → 200, `/dashboard` → 302.
- [x] **PDF (dompdf v3)** — validan izlaz, uklj. ćirilične/latinične dijakritike u sadržaju.
- [x] **Excel** — `LicencaNaplataExport` generiše XLSX (~19 KB) nad stvarnim podacima.
- [x] `<x-maps-google>` se renderuje bez greške na serveru (3.6 KB HTML za 2 markera).
- [ ] **Ručno u browseru: obje managment mape** — vidi 5.5, ovo je jedini otvoreni rizik.
- [ ] Ručno testirati: tiket, licence, bankomat, rezervacije/transfer, auth
- [ ] Deploy na staging i finalni test

### 5.5 ⚠️ OTVOREN RIZIK — Google mape poslije laravel-maps v0.21

`laravel-maps` v0.21 je **breaking**: prešao je s `google.maps.Marker` na
`google.maps.marker.AdvancedMarkerElement` (stari Marker je Google deprecirao 21.2.2024).

**Problem:** Google zahtijeva **registrovan Cloud Map ID** za Advanced Markers. Paket prosljeđuje
`mapId: '{{$mapId}}'`, gdje je `$mapId` samo `Str::random()` — isti string koji služi kao DOM `id`
elementa. Potvrđeno renderom: `mapId: 'wgbhy2J2zbyd6F3b'`. To **nije** validan Cloud Map ID.

**Šta testirati:** otvoriti obje mape u browseru i provjeriti prikazuju li se markeri —
`livewire/managment/distributer-terminali-mapa.blade.php` i `distirbuteri-licence-mapa.blade.php`.
U konzoli tražiti `InvalidMapIdError` ili poruku o Map ID-u.

**Fix ako puknu** (bez forkovanja paketa): napraviti Map ID u Google Cloud Console
(Google Maps Platform → Map Management), staviti ga u `.env`, pa ga proslijediti komponenti
kroz `id` atribut — komponenta ga tada koristi umjesto `Str::random()`:

```blade
<x-maps-google :markers="$pins" :fitToBounds="true" id="{{ config('services.google_maps.map_id') }}"></x-maps-google>
```

**Sporedno:** markeri s `icon` ključem se sad renderuju kao `<img>` fiksno 32×32 px unutar flex diva
(prije je Google skalirao ikonu sam), pa ikone mogu izgledati drugačije. Obje mape koriste `icon`.

---

## FAZA 6 — Post-upgrade čišćenje

> Automatizovani dio odrađen 2026-08-16. Ostalo je ručno testiranje u browseru
> (redovi označeni **RUČNO**) i deploy na staging.

- [x] `php artisan optimize:clear`
- [x] `php artisan config:cache && php artisan route:cache && php artisan view:cache` — sve tri prolaze;
      `route:cache` znači da nema closure ruta, `view:cache` da se svi Blade šabloni kompajliraju na PHP 8.4
- [x] `phpunit.xml` — **već je u modernom formatu** (`<source>` umjesto `<coverage><include>`),
      PHPUnit 12.5.33 ga prihvata bez ijedne poruke o migraciji. Nije trebalo mijenjati.
- [x] `minimum-stability` `dev` → `stable` u `composer.json` — zatvara rupu kroz koju je
      `laravel/framework` bio prikovan na `11.x-dev` (nalaz FAZE 4). Nijedan paket nije bio na dev verziji,
      pa je promjena bez efekta na instalirani stack (`composer update --lock`, lock diff = 2 linije).
- [x] Excel export (`LicencaNaplataExport`) — **automatski smoke test** na stvarnim podacima:
      17.5 KB validan XLSX, 121 red, ispravna zaglavlja
- [x] PDF generisanje (dompdf v3) — **automatski smoke test** na stvarnim podacima:
      `PredracunPdfControler` 984 KB / 239 licenci, `DistPredracunControler` oba tipa (`p` i `r`) ~879 KB.
      Sve `%PDF-` validno. dompdf v2→v3 nije donio regresiju.
- [x] Spatie Permission role/permission dodjele — `tests/Feature/RolePermissionTest.php` zelen na PHP 8.4
- [x] Sken ostataka Livewire 2 sintakse (`emit*`, `dispatchBrowserEvent`, `wire:model.defer/.lazy`,
      `$listeners`, `Livewire.emit`) — **čisto**, jedina pojava je zakomentarisana linija
      u `Managment/DistributerLicencePregled.php:25`
- [x] **RUČNO** — Google mape u browseru (vidi odjeljak 5.5 — jedini otvoreni rizik iz FAZE 5)
- [x] **RUČNO** — tiket workflow
- [x] **RUČNO** — licence i naplata
- [x] **RUČNO** — bankomat module
- [x] **RUČNO** — rezervacije i transfer dijelova
- [x] **RUČNO** — autentifikacija i email verifikacija
- [ ] Deploy na staging i finalni test

### Nalazi FAZE 6

**1. Dinamička svojstva (PHP 8.2+ deprecacija, fatalno u PHP 9) — ISPRAVLJENO**

Skeniranje deprecacija u FAZI 3 bilo je statičko i ovo je promašilo; izašlo je tek pri
*izvršavanju* PDF kontrolera:

```
Creation of dynamic property class@anonymous::$tip is deprecated
  in app/Http/Controllers/Distributer/DistPredracunControler.php on line 98
```

- `DistPredracunControler::vrstaDokumenta()` — anonimna klasa `new class{}` je dobijala
  4 dinamička svojstva. Sada su deklarisana. PDF izlaz je bajt-identičan prije i poslije.
- `DistributerLokacija::$modelId` — postavljano u `deleteShowModal()` bez deklaracije.
  Pored deprecacije, ovo je i **Livewire problem**: nedeklarisana svojstva se ne serijalizuju,
  pa vrijednost ne preživi zahtjev. Dodato `public $modelId;` po konvenciji siblinga.

Regresioni test: `tests/Unit/DinamickaSvojstvaTest.php` (diže `E_DEPRECATED` u izuzetak;
provjereno da sva 3 testa padaju bez ispravki).

Sken cijelog `app/` nije našao druga živa mjesta — preostalih 6 kandidata su zakomentarisane linije.

**2. `BrowserSessionsTest` bez asercija — ISPRAVLJENO**

Jetstream boilerplate koji je PHPUnit prijavljivao kao *risky*. Dodato
`->assertHasNoErrors()->assertSuccessful()`.

**3. `DistributerLokacija` — dugme „Ukloni lokaciju" zove metodu koja ne postoji — RIJEŠENO UKLANJANJEM**

`resources/views/livewire/distributer-lokacija.blade.php:94` je imao `wire:click="delete"`,
a komponenta nema `delete()` metodu — potvrđeno i reflection-om nad cijelom hijerarhijom
(`Component`, `WithPagination`, trait-ovi). Dugme se renderovalo samo kad je `$delete_error`
prazan — dakle baš na „sretnom putu" pucalo je `MethodNotFoundException`.

**Nije bila regresija upgrade-a** (isto bi puklo i na LW2). Korisnik je odlučio da se
funkcionalnost ukloni umjesto da se implementira, pa je obrisan cio mrtvi tok:
dugme u redu tabele, modal za brisanje, `deleteShowModal()`, četiri svojstva koja su
služila samo njemu (`modalDeleteLocVisible`, `modelId`, `l_naziv`, `delete_error`) i
importi `User` / `TerminalLokacija` koji su nakon toga ostali neiskorišteni.

Usput je otišao i nevažeći HTML — dugme je otvarano kao `<x-jet-danger-button>`,
a zatvarano kao `</x-jet-button>`.

Zbog toga je iz `tests/Unit/DinamickaSvojstvaTest.php` uklonjen test
`test_distributer_lokacija_ima_deklarisan_model_id` — svojstvo koje je čuvao više ne postoji.
Dio testa koji pokriva `DistPredracunControler` ostaje.

### Stanje testova na kraju FAZE 6

```
Tests: 55 passed, 8 skipped (162 assertions)
```

8 preskočenih su Jetstream funkcije isključene u konfiguraciji (API tokeni, registracija,
email verifikacija) — očekivano, ne zavisi od upgrade-a. Nula deprecacija uz
`--display-deprecations`, `composer audit` čist.

---

## Pregled rizika i procjena trajanja

| Faza | Rizik | Procjena trajanja |
| --- | --- | --- |
| Faza 0 — Priprema | Nizak | 1h |
| Faza 1 — Livewire 2→3 | **Visok** | 2–5 dana |
| Faza 2 — Laravel 11 | Srednji | 1–2 dana |
| Faza 3 — PHP 8.4 | Nizak | 2–4h |
| Faza 4 — Laravel 12 | Nizak | 0.5–1 dan |
| Faza 5 — Laravel 13 | Nizak–Srednji | 0.5–1 dan |
| Faza 6 — Čišćenje | Nizak | 2–4h |

---

## Korisni linkovi

- [Laravel 11 Upgrade Guide](https://laravel.com/docs/11.x/upgrade)
- [Laravel 12 Upgrade Guide](https://laravel.com/docs/12.x/upgrade)
- [Laravel 13 Upgrade Guide](https://laravel.com/docs/13.x/upgrade)
- [Laravel Support Policy](https://laravel.com/docs/releases#support-policy)
- [Livewire v3 Upgrade Guide](https://livewire.laravel.com/docs/upgrading)
- [Jetstream v4 Changelog](https://github.com/laravel/jetstream/blob/main/CHANGELOG.md)
- [PHP 8.4 Migration Guide](https://www.php.net/manual/en/migration84.php)
