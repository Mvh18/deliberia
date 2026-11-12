---
name: Chore / Maintenance
about: Internal technical work with no direct user value — dependencies, infra, CI/CD, refactors, cleanup.
title: '[Chore] '
labels: ''
---

## What

<!--
A clear description of the task. No user story: this work is internal.
-->

## Why now

<!--
Concrete justification for prioritising this.
Good: "Library X reaches EOL on 2026-06-01", "Build time grew 40 %",
"CVE-2026-XXXX affects the current base image", "Blocks the upgrade to Y".
Bad: "keeping things up to date".

Without a real reason, chores are deprioritised forever — so this is the field
that decides whether the issue gets scheduled at all.
-->

## Scope

<!--
Which modules, services, files or infrastructure are in scope — and, where it
helps, what is deliberately out.
-->

## Definition of done

<!--
Verifiable criteria. This replaces acceptance scenarios: forcing Gherkin onto a
dependency bump produces theatre, not clarity.
-->

- [ ]
- [ ] CI is green
- [ ] No new lint warnings

## Risk / rollback

<!--
Fill in ONLY for CI/CD, critical dependencies, infrastructure, database
migrations or production configuration.

- **Main risk:** what could go wrong
- **Rollback:** how to revert if it does
-->

## Notes

<!--
Optional. Upstream changelog, CVE advisory, related ADR, baseline metrics.
-->
