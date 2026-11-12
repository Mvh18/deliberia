## What does this PR do?

<!--
A context paragraph: what changes and why. Mention the motivation, the technical
decision or the external constraint if it is not obvious from the title.
Breaking changes, dependencies on other PRs, and anything the reviewer should
watch out for belong here too.
-->

**Main changes:**

1. **…**: …

Closes #

## How to test

<!--
The minimum steps for a reviewer to verify the behaviour:
1. ...
2. ...
3. Check that ...
Include sample data or test credentials where relevant.
-->

---

<!--
Optional sections — delete the ones that do not apply to this PR.
-->

## Breaking changes

<!--
Only if this PR breaks backwards compatibility.
Describe what breaks and what consumers must update. This must match the
BREAKING CHANGE: footer in the commit.
-->

## Review guide

<!--
OPTIONAL — only for PRs with non-trivial logic or several layers.
Say where to start reading and which design decisions are worth knowing about.
-->

## Screenshots

<!--
If the change affects the UI, include before/after captures.
-->

| Before | After |
| ------ | ----- |
|        |       |

---

## Type of change

- [ ] feat — new user-facing capability
- [ ] fix — corrects incorrect behaviour
- [ ] refactor — no behaviour change
- [ ] perf — performance only
- [ ] docs
- [ ] test
- [ ] build — build system or dependencies
- [ ] ci — pipelines and workflows
- [ ] chore — everything else

## Deploy considerations

<!--
Tick what applies and state exactly what must be run.
-->

- [ ] Includes a database migration
- [ ] Requires new environment variables
- [ ] Requires a service restart
- [ ] Requires post-deploy commands
- [ ] Requires a cache or queue flush

## Checklist

- [ ] CI green
- [ ] Tested locally
- [ ] Self-review of the diff complete
- [ ] Commit subjects pass `validate-msg.sh`
- [ ] No secrets, credentials, personal data or local paths in the diff
