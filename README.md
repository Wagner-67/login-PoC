# Login-PoC

**Kurzbeschreibung**  
Login-PoC ist ein Proof-of-Concept (API-only) basierend auf **Symfony**. Es demonstriert grundlegende Authentifizierungs-Workflows und verwendet **JWT** (LexikJWT) plus Refresh-Tokens (Gesdinet). Nicht für Produktion – nur Demo / Interview-Beispiel.

---

## Was das Projekt kann
- **Registrierung** mit E-Mail-Verifizierung (UUID als `user_id`)  
- **JWT-Login** (LexikJWT) — Ausgabe eines JWT beim Login  
- **Refresh Tokens** (Gesdinet JWT Refresh Token Bundle)  
- **Two-Factor-Authentication (2FA)** per E-Mail-Code mit per-User Tracking (`TwoFactorAuth` Entity)  
- **Passwort-Zurücksetzen** via zeit-begrenztem Token, per E-Mail versendet  
- **Rate-Limiting** für öffentliche Endpoints (Limiter Injection in Controllern)  
- **Account-Verified Guard**: Login nur wenn `verified = true`  
- **Logout**: entfernt zugehörige Refresh Tokens

---

## Wichtige Endpoints (Kurzübersicht)
- `POST /public/register_new_user` — Registrierung (sendet Verifizierungs-E-Mail)  
- `GET  /public/verify-account/{token}` — Account-Verifizierung  
- `POST /api/login_check` — Login (JSON, liefert JWT)  
- `POST /public/TwoFactorAuthCode` — 2FA Code bestätigen (liefert JWT)  
- `GET  /auth/TwoFactorAuthE` — 2FA aktivieren (auth)  
- `POST /auth/TwoFactorAuthD` — 2FA deaktivieren (auth, Body: `{ "password": "..." }`)  
- `POST /public/password_change` — Passwort-Reset anstoßen (E-Mail mit Token)  
- `POST /public/password-reset/{token}` — Passwort zurücksetzen  
- `GET  /auth/logout` — Logout (löscht Refresh Token)

---

## Schnellstart (lokal)
```bash
# Repo klonen
git clone https://github.com/Wagner-67/login-PoC.git
cd login-PoC

# Abhängigkeiten installieren
composer install

# .env lokal anlegen & anpassen: DATABASE_URL, MAILER_DSN, APP_SECRET, JWT_* etc.
cp .env .env.local
# Werte in .env.local setzen

# JWT Keys erzeugen (Beispiel)
mkdir -p config/jwt
openssl genpkey -algorithm RSA -out config/jwt/private.pem -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -pubout -out config/jwt/public.pem
chmod 600 config/jwt/private.pem

# DB und Migrationen
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Server starten
symfony server:start
# oder:
php -S 127.0.0.1:8000 -t public
