# "Continue with Google" setup

The button appears on **Log in** and **Join** only when a Google client id and secret are configured. Without them the platform works normally with email + password.

## 1. Create credentials (free, ~5 minutes)
1. Go to https://console.cloud.google.com/ → create a project (e.g. "YouthConnect").
2. **APIs & Services → OAuth consent screen**: choose *External*, app name "YouthConnect", your email as support/developer contact. Add yourself and your lecturer under *Test users* while the app is in "Testing" (or press *Publish app* so anyone can sign in).
3. **APIs & Services → Credentials → Create credentials → OAuth client ID** → type **Web application**.
4. **Authorised redirect URIs** – add every address you use, exactly:
   - `http://127.0.0.1:8099/auth/google/callback` (local)
   - `https://YOUR-LIVE-DOMAIN/auth/google/callback` (live link)
5. Copy the **Client ID** and **Client secret**.

## 2. Configure
In `config/local.php` (or as environment variables on the host):
```php
'GOOGLE_CLIENT_ID'     => '1234-abc.apps.googleusercontent.com',
'GOOGLE_CLIENT_SECRET' => '...',
'GOOGLE_REDIRECT_URI'  => 'https://YOUR-LIVE-DOMAIN/auth/google/callback', // recommended on the live site
```
Never commit the secret (`config/local.php` is git-ignored).

## 3. Existing database
New installs already include `users.google_id`. On a database created earlier run once:
```sql
ALTER TABLE users ADD google_id VARCHAR(40) NULL UNIQUE AFTER password_hash;
```

## How it behaves
- **New person** → account created from their Google name + verified email, role always `participant`, then sent to *My profile* to add country and age group.
- **Existing email** (registered the normal way) → Google is linked to that account the first time (Google has verified the email).
- **Returning** → straight in. Suspended accounts are refused.
- Security: OAuth `state` (anti-CSRF) + PKCE, server-to-server token exchange over TLS, only `email_verified` accounts accepted, throttled, audit-logged.
