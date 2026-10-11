# User Management

Simple user-management app. Laravel backend and Nuxt frontend live side by side as two
independent sources, wired together by Docker Compose.

```
.
├── backend/          Laravel 13 (PHP 8.4) — REST API
├── frontend/         Nuxt 4 (Node 26) — SPA
├── docker/
│   ├── nginx/        vhost serving backend/public
│   └── php/          php-fpm image + php.ini
├── compose.yaml      mysql · redis · backend (php-fpm) · nginx · frontend
├── .env.example      compose-level settings (ports, db creds, host UID/GID)
└── Makefile          shortcuts — `make help`
```

## Requirements

Docker + Docker Compose only; PHP and Node are not needed on the host. The current user
must be able to talk to the Docker daemon:

```bash
sudo usermod -aG docker $USER   # then log out / in, or: newgrp docker
```

## First run

```bash
make init     # writes .env + backend/.env, builds, installs deps, migrates
make api-install   # once: adds routes/api.php and Sanctum
```

| Service  | URL                     |
|----------|-------------------------|
| Frontend | http://localhost:3001   |
| Backend  | http://localhost:8000   |
| MySQL    | localhost:3307          |

Ports come from the root `.env`. 3001/3307 are the defaults here because 3000 and 3306
were already in use on this machine; change them there (and keep
`SANCTUM_STATEFUL_DOMAINS` / `FRONTEND_URL` in `backend/.env` in sync).

## Day to day

```bash
make up                                  # start
make down                                # stop
make logs s=backend                      # tail one service
make artisan c="make:model Profile -mf"  # artisan
make composer c="require laravel/sanctum"
make npm c="install @pinia/nuxt"
make test                                # backend test suite
```

## API

All routes are session (cookie) authenticated via Sanctum. A browser client must call
`GET /sanctum/csrf-cookie` once before any mutating request.

| Method | Route             | Purpose                                          |
|--------|-------------------|--------------------------------------------------|
| POST   | `/api/login`      | Sign in; throttled to 5 attempts per email + IP  |
| POST   | `/api/logout`     | Invalidate the session                           |
| GET    | `/api/user`       | The signed-in user                               |
| GET    | `/api/stats`      | Dashboard counters                               |
| GET    | `/api/users`      | List; `search`, `role`, `page`, `per_page`       |
| POST   | `/api/users`      | Create                                           |
| GET    | `/api/users/{id}` | Read                                             |
| PUT    | `/api/users/{id}` | Update; blank `password` keeps the current one   |
| DELETE | `/api/users/{id}` | Delete; 403 when deleting your own account       |

Seeded sign-in: `admin@example.com` / `password`.

## Frontend

- `app/pages/login.vue` — sign-in, honours `?redirect=`
- `app/pages/index.vue` — dashboard cards + newest accounts
- `app/pages/users/index.vue` — table with search, role filter, pagination, create/edit
  modal and delete confirmation
- `app/middleware/auth.global.ts` — route guard; `app/composables/useAuth.ts` — session state

## Notes

- Containers run as the host UID/GID (`UID`/`GID` in `.env`), so files they create stay
  editable from the host.
- `frontend/node_modules` lives in a named volume, not on the host — install packages via
  `make npm` so they land in the container.
- npm 11.19+ gates dependency install scripts. `frontend/package.json` carries an
  `allowScripts` entry for esbuild; if npm reports a new blocked package, approve it with
  `make npm c="install-scripts approve <pkg>"` and re-run `make npm c=install`.
- Nuxt runs with `ssr: false`. The dashboard sits behind a login, so keeping Sanctum's
  cookie session in the browser only avoids forwarding cookies through a server render.
- Auth is Sanctum SPA (cookie) based: `SANCTUM_STATEFUL_DOMAINS` and `SESSION_DOMAIN` in
  `backend/.env` must match the Nuxt origin.

## Deploy (Render)

`render.yaml` is a Render Blueprint for the whole stack: Postgres, Key Value (Redis),
the API (Docker, `docker/prod/backend.Dockerfile`, nginx + php-fpm) and the Nuxt app as
a static site.

- The static site proxies `/api/*` and `/sanctum/*` to the API, so the browser sees one
  origin. Sanctum's cookie needs this: `*.onrender.com` is a public suffix, so two
  onrender.com subdomains count as cross-site.
- Production runs on Postgres; local dev stays on MySQL. Keep queries portable
  (e.g. `whereLike` rather than `like`, which is case-sensitive on Postgres).
- Every boot runs `migrate --force` and `AdminUserSeeder`, which creates the admin from
  `ADMIN_EMAIL` / `ADMIN_PASSWORD` once and never overwrites it afterwards.

Steps:

1. Push the repo to GitHub.
2. Render dashboard → **New → Blueprint** → pick the repo. When prompted, fill in
   `APP_KEY` (`make artisan c="key:generate --show"`), `ADMIN_EMAIL`, `ADMIN_PASSWORD`.
3. If Render assigns a hostname that differs from a service name, update the hostnames
   in `render.yaml` (rewrite destinations, `APP_URL`, `FRONTEND_URL`,
   `SANCTUM_STATEFUL_DOMAINS`) and push again.

To test the production image locally:
`docker build -f docker/prod/backend.Dockerfile -t um-api-prod .`
