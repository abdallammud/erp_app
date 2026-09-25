# Non-Functional Requirements

The qualities the system must have regardless of which module a user is in — mostly drawn directly from the ToR's technical specifications, extended for the multi-tenant context.

## Availability & reliability

| Requirement | Detail |
|---|---|
| Uptime | Minimum 98% (ToR §9 & §16) |
| Backups | Automated daily backups with periodic restore verification, not just backup-job success/failure logs |
| Disaster recovery | Documented recovery plan and target recovery time, tested at least annually |
| Support response | 24–48 hours during the warranty/maintenance period (ToR §16) |

## Security

- HTTPS/TLS for all traffic; no unencrypted endpoints
- Encryption at rest for the database and document store (AES-256, matching the proposal's stated standard)
- Role-based access control with segregation of duties, enforced structurally (see [`02-architecture.md`](02-architecture.md))
- Two-factor authentication available, and enforceable per role/tenant
- Full audit logging: every create, update, delete, approval, and login event, immutable
- NDA/confidentiality expectations for anyone with platform-operator-level access; tenant data is never accessed except for support, and only with logging

## Data ownership & portability

Both WARDI documents are explicit that the organization owns its data and the source code/configuration built for it belongs to them; the platform generalizes this per-tenant:

- Each tenant NGO owns its own data outright — the platform operator has no rights to use it beyond providing the service.
- A clean, complete data export (not just a partial CSV dump) is available on request and guaranteed on offboarding.
- No tenant's data is ever visible to, or used to benefit, another tenant.

## Performance & field-realistic use

- Lightweight, low-bandwidth-optimized pages — this is a deployment context with variable connectivity in field offices, not a head-office-only assumption.
- Mobile-responsive throughout, since field staff frequently work from phones or tablets, not just desktop browsers.
- Graceful degradation over spotty connections is preferred to requiring a persistent connection; full offline-first support is not committed for Phase 1–4 but the architecture should not foreclose it later.

## Localization

- **Multi-currency** — required from Phase 1 (payroll) onward; an NGO operating across borders (WARDI, with a Nairobi liaison office alongside Somalia operations) needs this immediately.
- **Multi-language** — English as the baseline interface language; Somali, Arabic, and French as configurable per-tenant locales, reflecting the languages relevant to the initial reference customer's operating context and neighboring humanitarian environments.
- **Per-country tax & statutory configuration** — tax brackets, social security/pension rules, and public holiday calendars are tenant- and country-configurable, not hard-coded to one jurisdiction (WARDI alone spans Somalia and Kenya).

## Interoperability

- Data export in Excel, CSV, and PDF wherever data is listed or reported (ToR §9, applied platform-wide)
- API integration capability from Phase 0, so bank systems, SMS gateways, or a future mobile app can integrate without re-architecting
- SMS notification capability as a configurable, optional channel alongside email/in-app notifications

## Scalability & extensibility

- Modular architecture — each of the four modules can be enabled/disabled per tenant, supporting the phased-adoption pattern the ToR itself uses
- Configuration over code changes for anything tenant-specific: leave policies, salary grades, chart of accounts, approval chains, donor report formats
- Designed to support "dozens of NGOs" comfortably on the default shared-database model, with a dedicated-database escape hatch for any tenant with contractual data-residency requirements (see [`02-architecture.md`](02-architecture.md))

## Accessibility & usability

- Human-centered design approach — workflows validated with real HR, finance, and field staff before being finalized, not designed in isolation
- Consistent visual language for status (pending/approved/rejected), confidentiality (restricted/highly restricted), and workflow stage across every module

---
**Prev:** [`09-roadmap.md`](09-roadmap.md) · **Next:** [`11-glossary.md`](11-glossary.md)
