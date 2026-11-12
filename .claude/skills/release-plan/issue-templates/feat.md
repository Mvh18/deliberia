---
name: Feature / User story
about: A new capability or a user-visible improvement, framed as the value it delivers.
title: '[Feat] '
labels: ''
---

## User story

<!--
**As a** <role performing the action>,
**I want** <concrete action or capability>,
**so that** <benefit or value obtained>.

The role is whoever acts, not "the system". If you cannot name a beneficiary,
this is probably a chore, not a feature.
-->

### Context

<!--
Current state of the system and why this story is needed now. Include the
end-to-end flow it fits into, the layers or modules it touches, and any
dependency on other stories.

Mandatory — do not drop it because it feels obvious to you today.
-->

### Business value

<!--
One or two sentences: what becomes unblocked or measurably better once this
ships. Connect it to the product's goal.

Mandatory.
-->

---

## Acceptance criteria

<!--
At least two scenarios: the happy path plus one edge or alternative case.
Observable outcomes only — no implementation detail, present tense.
-->

```gherkin
Scenario: <name — happy path>
  Given <precondition>
  When <user action>
  Then <observable outcome>

Scenario: <name — edge or alternative case>
  Given <precondition>
  When <action>
  Then <observable outcome>
```

---

## Out of scope

<!--
What this story deliberately does not cover, each with a reference to the story
that will. An empty list is suspicious in anything but the smallest feature.
-->

-

## Notes

<!--
Optional. Design references, prototype links, related ADRs, upstream docs.
-->
