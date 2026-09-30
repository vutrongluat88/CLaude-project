# CLAUDE.md

Simple user-management app: Laravel backend + Nuxt frontend, two separate sources in one
repo, orchestrated by Docker Compose. See README.md for the layout and commands.

## Stack (pinned to what was current on 2026-09-30)

- `backend/` — Laravel 13 (framework `^13.17`), PHP 8.4, MySQL 8.4, Redis, Sanctum SPA auth
- `frontend/` — Nuxt 4.5, Node 26

## Working rules

- The host has PHP 7.4 and cannot run this backend. Never run `php`, `composer`, `artisan`
  or `npm` directly on the host for these apps — go through the containers:
  `make artisan c="..."`, `make composer c="..."`, `make npm c="..."`, `make test`.
- `frontend/node_modules` is a named volume, so host-side `npm install` has no effect on
  the running container.
- Backend env lives in `backend/.env`; compose-level settings (ports, DB creds, UID/GID)
  live in the root `.env`. Keep DB credentials in sync between the two.
- Nuxt reads the API base from `NUXT_PUBLIC_API_BASE` via `runtimeConfig.public.apiBase`.
