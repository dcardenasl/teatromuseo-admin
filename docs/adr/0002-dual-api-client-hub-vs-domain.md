# 2. Two API clients, one per upstream owner — never mixed in a module

Date: 2026-07-18 (retroactively documenting a decision already in force — see
`CLAUDE.md` "DomainApiClient: Secondary client for domain-starter backends")

## Status

Accepted

## Context

The admin panel drives two independent backends at once: the Hub
(`ci4-website-builder-api`, owns auth/users/IAM/files/audit/metrics) and a
Domain app (`ci4-website-builder-domain`, owns CMS content). Both expose a
similar REST contract and both issue/consume JWTs differently — the Hub
issues them, the Domain only validates them via introspection. A module that
accidentally called the wrong client would either 404 against the wrong base
URL or, worse, attempt to authenticate against a backend that doesn't own the
resource it's asking about.

## Decision

Two separate library/interface pairs, resolved through separate `Config\Services`
factories:

- `App\Libraries\ApiClient` (`Services::apiClient()`) — for entities the Hub
  owns: Users, Roles, Permissions, Files, Audit, ApiKeys, IAM Applications.
- `App\Libraries\DomainApiClient extends ApiClient implements DomainApiClientInterface`
  (`Services::domainApiClient()`) — for entities a Domain app owns:
  Subscriptions, Projects, Campaigns, and every CMS resource (Pages, Entries,
  Collections, Blocks, Menus, …).

`DomainApiClient` inherits all refresh/header/upload logic from `ApiClient` —
the split is about **which base URL and audience a module talks to**, not
about different HTTP mechanics. Scaffolding (`bin/make-module.sh
--service=domain|hub`) makes the choice explicit at generation time rather
than leaving it to be inferred later. `BaseApiService`/`ResourceApiService`
type-hint the parent `ApiClientInterface` on purpose — the factory function is
where the domain/hub distinction is actually enforced (PHPStan flags a
wrong-factory wiring via the factory's return type, not the service
constructor).

## Consequences

- **Positive:** A module's data ownership is legible from its constructor
  wiring alone. Swapping which backend a resource lives behind (rare, but
  happened historically) is a one-line change in `Config\Services`, not a
  rewrite of the service layer.
- **Negative:** Two client classes to keep behaviorally identical when fixing
  cross-cutting bugs (token refresh, header injection). A fix applied to
  `ApiClient` needs to be checked against `DomainApiClient`'s override
  surface, if any.
- **Guardrail:** "Never mix in the same module" (`CLAUDE.md`) — a module that
  needs both a Hub-owned and a Domain-owned resource should compose two
  services, not reach for both clients inside one service.
