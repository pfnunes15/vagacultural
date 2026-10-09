# VAGA — Agenda Cultural (reconstrução)

Reconstrução de **vagacultural.pt**, a agenda cultural da **Madeira e Porto Santo**.
O site atual é uma SPA React em Lovable+Supabase; aqui reconstruímos de raiz em **PHP (Laravel) + MySQL**.

## Stack
- **Laravel 12** (PHP 8.3+; local tem 8.5, contentores correm 8.4) — ver `composer.json`.
- **MySQL 8.4** + **Redis** (cache/sessão/filas) via Docker.
- **Site SSR (Blade)** para SEO + **API REST `/api/v1`** (Sanctum) para as futuras apps Android/iOS — um só domínio, uma fonte de verdade.
- Qualidade: **Pint** (estilo), **Larastan/PHPStan level 6**, **Pest 4** (testes).

## Comandos
```sh
# ambiente
docker compose up -d                 # web(nginx) + app(php-fpm) + db(mysql) + redis
# app em http://localhost:8111  (APP_PORT no .env; db exposto em FORWARD_DB_PORT=3307)

docker compose run --rm app php artisan migrate:fresh --seed --force

# qualidade (correr antes de cada commit)
composer check        # = lint:test + stan + test
composer lint         # Pint a corrigir
composer stan         # PHPStan/Larastan
composer test         # Pest

./vendor/bin/pest     # testes diretamente

# pesquisa (Meilisearch via Scout) — indexar eventos
docker compose run --rm app php artisan scout:import "App\\Models\\Event"
```
> Nota: o host pode ter MySQL/artisan serve a ocupar 3306/8000/8080 — por isso os defaults são **8111** (app) e **3307** (db). Correr migrations **dentro do contentor** (`DB_HOST=db`) evita colidir com o MySQL local.

## Convenções de código (SOLID / boa prática PHP)
- `declare(strict_types=1)` em todos os ficheiros (imposto pelo Pint).
- Enums tipados em `app/Enums` para estados/papéis; usados como casts nos modelos.
- Modelos Eloquent finos em `app/Models` com relações tipadas (generics em docblocks `@return`).
- Lógica de negócio não trivial deve ir para **serviços/casos-de-uso** (`app/Services`, a criar quando surgirem), não nos controladores nem nos modelos — mantém SRP.
- Controladores separados por canal: `app/Http/Controllers/Web` e `app/Http/Controllers/Api/V1`; respostas da API via API Resources.
- FormRequests para validação; Policies para autorização (RBAC por `user_roles`).

## Domínio (ver migrations em `database/migrations/2026_10_09_14*`)
- **events** ← pertence a um **promoter** e/ou **organization**; estados em `EventStatus` (draft/pending/published/rejected/archived); soft deletes; bilingue (`*_en`).
- **event_occurrences** — sessões/ocorrências de um evento (data/hora + **venue**). É aqui que o calendário consulta. (`OccurrenceStatus`)
- **categories** (auto-referência p/ subcategorias) ↔ events via pivot **event_category** (`is_primary`).
- **venues** (espaços físicos c/ geo), **organizations** (institucionais), **promoters** (contas que submetem) — três conceitos distintos, como no site atual.
- **promoter_requests** — auto-registo de promotor → aprovação admin → `promoter-setup`.
- **users** (+ perfil) e **user_roles** (`UserRole`: admin/promoter/user, multi-papel).
- **favorites** (polimórfico: eventos/categorias/venues/promoters), **agenda_items** (a minha agenda), **push_subscriptions** (web push), **user_activity_log**.

## Arquitetura de informação (rotas-alvo, do site atual)
Público: `/ /events /event/{slug} /calendar /category/{slug} /organizations /organization/{slug} /promoters /promoter/{slug} /recommendations`
Utilizador: `/auth /my/agenda /my/favorites /my/profile /promoter-registration`
Admin: `/admin/{events,categories,organizations,promoters,venues,users,pending,promoter-requests,analytics}`
API (apps): `/api/v1/...`
