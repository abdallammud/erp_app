# Changelog

Dated, running log of what was actually done. Newest entry at the top. This is a work log, not a marketing summary — include false starts and reversed decisions, not just the final state.

---

## 2026-09-25 — Documentation restructure + build plan

- Converted all 11 planning docs + index from `docs/*.html` to `docs/*.md` — canonical, lean-to-read format going forward. HTML files kept as a non-maintained browsable snapshot; [`../README.md`](../README.md) explains the split.
- Created `docs/build/` with four living documents: this changelog, [`DECISIONS.md`](DECISIONS.md), [`QUESTIONS.md`](QUESTIONS.md), and [`00-build-plan.md`](00-build-plan.md) — the task-level execution checklist derived from `09-roadmap.md`.
- Logged 11 decisions/assumptions made so far (D-001 through D-011), most carried over implicitly from the prior planning session and now made explicit in writing for the first time — see `DECISIONS.md`.
- Logged 7 open questions (Q1–Q7) that block or affect near-term build steps, each with the default we're proceeding under.
- **Working agreement established:** every session appends to this changelog, logs new assumptions in `DECISIONS.md` as they're made, and logs open questions in `QUESTIONS.md` instead of silently guessing on anything the user would plausibly want to weigh in on.
- **No application code written yet.** Repo still contains only the four source PDFs and the `docs/` planning set — Phase 0 (`00-build-plan.md`) has not started.

## 2026-09-25 — Initial planning phase

- Initialized the git repo (`erp_app`), connected to `github.com/abdallammud/erp_app` via the personal SSH identity, pushed first commit.
- Read all four source documents in full (not skimmed): the WARDI ToR, the WARDI implementation proposal, the generic "MARDO" ERP proposal, and the 70-page Nova Humanitarian HRM UI reference (all 70 pages sampled across three passes).
- Identified the central gap across all four sources: every one assumes a single-organization build; none address multi-tenancy. This became the plan's core design addition.
- Synthesized the four sources into a 12-page HTML planning site (`docs/*.html`, now superseded by the `.md` versions — see the entry above): vision & scope, architecture & multi-tenancy, roles & permissions, four module specs (HRM, Finance, Procurement & Logistics, Programs), data model, roadmap, non-functional requirements, glossary with full source traceability.
- Committed and pushed the four source PDFs and the planning doc set to the repo, with a `.gitignore` added for OS/Drive noise and future Laravel artifacts.
- Flagged (not yet resolved): `Nova Humanitarian HRM.pdf` is 61.8MB — under GitHub's 100MB hard limit so it pushed fine, but flagged as a candidate for Git LFS if more large binary source files get added later.

---
**See also:** [`00-build-plan.md`](00-build-plan.md) · [`DECISIONS.md`](DECISIONS.md) · [`QUESTIONS.md`](QUESTIONS.md)
