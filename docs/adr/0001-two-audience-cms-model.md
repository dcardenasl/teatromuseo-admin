# 1. Two parallel CMS editing surfaces, never merged

Date: 2026-07-18 (retroactively documenting a decision already in force — see
`CLAUDE.md` "CMS — Two-Audience Model")

## Status

Accepted

## Context

The CMS module serves two distinct user audiences with very different needs.
Non-technical editors (`cms-editor` role) need a guided, low-friction flow to
publish content without understanding the underlying Page/Entry/BlockInstance
model. Technical administrators (`cms-admin` role) need full structural
control: defining Pages, Menus, block *types*, and redirects — the building
blocks the Wizard itself is assembled from.

Building one screen that serves both audiences would force a trade-off: too
much structural exposure overwhelms editors, or too much hand-holding
frustrates admins who need direct control.

## Decision

Ship two parallel, independently-routed UI flows over the same underlying
domain model:

1. **The Wizard** (`/admin/cms/wizard`) — guided step-by-step flow for
   creating/editing entries and menus. Gate: `cms.entries.read`. Sidebar
   group "Contenido": Entradas, Colecciones, Categorías, Tags, Formularios,
   Envíos.
2. **Canonical CMS modules** — direct CRUD over Pages, Menus, Block types,
   Redirects. Gate: `cms.pages.read` / `cms.menus.read` / `cms.blocks.read`.
   Sidebar group "Estructura".

Both flows write to the same tables (`cms_pages`, `cms_block_instances`,
etc.) through the same services — there is no data duplication, only UI
duplication. Structural routes must never live under an entry-level
permission gate and vice versa (see `CLAUDE.md` "Sidebar permission
structure" for the exact gating table).

## Consequences

- **Positive:** Each audience gets an interface shaped for its actual job.
  Admins can still use the Wizard when convenient (it's additive, not a
  replacement), but editors are never exposed to structural concepts they
  don't need.
- **Negative:** Two UI code paths to maintain for overlapping functionality
  (e.g. both the Wizard and the canonical Entry module can create block
  instances). A bug fix in one flow's block-composition logic does not
  automatically apply to the other.
- **Guardrail:** "Do not remove one to replace the other" — a future session
  that finds the Wizard "redundant" with canonical modules (or vice versa) is
  looking at incomplete context; check this ADR first.
