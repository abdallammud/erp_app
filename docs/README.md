# Docs folder guide

This folder has two kinds of documentation, for two different purposes.

## `docs/*.md` — the plan (canonical, read this)

The requirements/planning docs, in Markdown. **This is the source of truth going forward** — lean to read, cheap to re-read for context, easy to diff in git. Start at [`index.md`](index.md).

## `docs/*.html` — browsable snapshot (not maintained)

A styled, navigable version of the same content published earlier, kept for convenience if you want to read it in a browser (`open docs/index.html`). **It will drift out of sync** as the `.md` files are updated — it was a one-time export, not a build target. If it ever meaningfully disagrees with the `.md` files, the `.md` files are correct.

## `docs/build/` — how we're actually building it

This is the engineering execution side, updated continuously as work happens:

- [`00-build-plan.md`](build/00-build-plan.md) — the step-by-step build plan, phase by phase, as a working checklist.
- [`DECISIONS.md`](build/DECISIONS.md) — every technical/design decision and assumption made, with rationale, in ADR-lite format.
- [`QUESTIONS.md`](build/QUESTIONS.md) — open questions that need your input, each with the default we're proceeding under until answered.
- [`CHANGELOG.md`](build/CHANGELOG.md) — dated log of what was actually done, session by session.

**Working agreement:** every build session appends to `CHANGELOG.md`, logs any new assumption in `DECISIONS.md`, and logs any open question in `QUESTIONS.md` rather than silently guessing. The build plan checklist gets checked off as steps complete.
