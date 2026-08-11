# ADR 0003 — Bounded Admin Dashboard Read Delivery

## Status

Accepted — 2026-08-11

## Context

The dashboard shell loaded several widgets independently. A cold browser load
could therefore fan out into many Admin → API and Admin → CMS requests, each
with its own pagination, count, translation, analytics, health and retry
behaviour. On a resource-constrained host this amplified latency and upstream
connection pressure.

The Admin has no database of its own and must preserve the Hub/domain ownership
boundary. It also uses authenticated PHP sessions, so a slow upstream request
must not hold the session file lock for the duration of the request.

## Decision

The dashboard uses a bounded read model:

1. Hub exposes `GET /api/v1/admin/dashboard/summary` for Hub-owned users,
   files and metrics.
2. CMS Domain exposes `GET /api/v1/cms/dashboard/summary` for CMS counts,
   submission statuses and recent activity.
3. Both endpoints return DTO-first, permission-filtered envelopes. A caller
   without a relevant permission does not execute the repository query.
4. `DashboardDataService` composes exactly one Hub and one CMS read per cold
   build. Its permission scope is part of the cache key, so one user's data
   cannot be served to another permission scope.
5. The Admin uses a file cache by default, with a 300-second fresh TTL, a
   3600-second stale TTL, a 15-second failure cooldown and a bounded
   250-millisecond single-flight lock wait.
6. Stale data is used only for transport failures or upstream 5xx responses;
   4xx responses are never hidden by stale data. A cold outage is cached for
   the short failure cooldown to prevent sequential widget retry storms.
7. Dashboard widget routes release the PHP session lock before upstream I/O.
   The session remains open only when the access token is within the refresh
   window. A released-session request never attempts token refresh or session
   regeneration.
8. Browser-side widget fetches use a concurrency of one. Existing secondary
   widgets retain their own contracts while the primary stats, summary,
   activity and files widgets consume the shared read model.

## Consequences

### Positive

- A cold dashboard load has a bounded primary fan-out of two aggregate reads.
- Repeated widgets reuse one permission-scoped snapshot.
- Hub/CMS failures degrade to stale or partial content instead of multiplying
  retries.
- The session lock is not held across normal dashboard upstream I/O.
- The API ownership boundary remains explicit and testable.

### Operational constraints

- The default file lock is valid for a single host or a deployment with a
  shared writable `WRITEPATH`. Multi-host deployments must use a shared lock
  implementation (for example Redis/DB-backed) before scaling horizontally.
- `writable/cache/` and `writable/cache/dashboard-locks/` must be writable by
  PHP-FPM and must not be cleared during every request.
- Production rollout must verify the actual host timeout, process, connection
  and database limits; local tests cannot establish those provider limits.

## Rejected alternatives

- Keeping the seven independent cold fan-outs and only adding browser limits:
  this does not protect direct widget requests or multiple dashboard tabs.
- Calling existing CRUD endpoints with `limit=1`: the requests still pay the
  transport, auth, serialization and controller overhead repeatedly.
- Caching one shared unscoped snapshot: it risks permission leakage.
- Serving stale data for 4xx responses: it masks valid authorization and route
  errors.

## Rollback

Rollback is performed by reverting the Admin dashboard widget/controller
release together with the Hub/CMS summary routes. The old clients remain
available in the repositories until the new read model has passed production
verification, but they are no longer the primary dashboard path.
