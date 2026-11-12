---
name: Bug
about: Behaviour that is incorrect, broken or diverges from what was specified.
title: '[Bug] '
labels: ''
---

**Summary:** <!-- one sentence: what fails, and in what context -->

## Steps to reproduce

<!--
Numbered, from a known starting state. Someone who has never seen the system
must be able to follow them.
-->

1.
2.
3.

**Expected:** <!-- what should happen -->

**Actual:** <!-- what happens instead, verbatim: error text, wrong value, stack trace -->

## Impact

| Field       | Value                                          |
| ----------- | ---------------------------------------------- |
| Frequency   | Always / Frequent / Intermittent / Conditional |
| Blocks prod | Yes / No                                       |
| Workaround  | Yes (<which>) / No                             |
| Affected    | <who hits it>                                  |

## Environment

<!--
Only what is relevant: version or commit, browser, OS, database state, feature
flags. Delete the row if it does not matter.
-->

- Version / commit:
- Environment: local / staging / production

## Definition of done

<!--
The regression test is not optional: a bug with no failing test has no proof it
was fixed, and no guard against its return.
-->

- [ ] A regression test reproduces the bug and fails before the fix
- [ ] That test passes after the fix
- [ ] CI is green

## Root cause

<!--
Optional at filing time, filled in once diagnosed. Worth recording even when the
fix is one line — the next similar bug starts here.
-->

## Notes

<!--
Optional. Logs, screenshots, related issues, when it was last known to work.
-->
