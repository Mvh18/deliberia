---
name: release-plan
description: Analyses pending changes and produces a multi-PR plan with user stories. Auto-detects the stack (Python/JS/PHP) and follows Conventional Commits 1.0.0. Plans are written to .claude/plans/.
allowed-tools: Read Write Bash(git *) Bash(bash .claude/skills/release-plan/validate-msg.sh *) Bash(ruff *) Bash(python -m pytest *) Bash(npm *) Bash(npx *) Bash(./vendor/bin/pint *) Bash(ls *) Bash(find *) Bash(mkdir *) Bash(grep *) Bash(xargs *)
argument-hint: "[starting-issue-number]"
---

# Release Plan

Turns an uncommitted working tree into one or more reviewable pull requests,
each with an issue, a branch, an ordered commit sequence and a PR body.

The plan is a document, not an action: this skill never commits, never pushes
and never opens a PR. It writes files to `.claude/plans/` for the user to
execute.

## Repository context

Branch: !`git branch --show-current`
Changes: !`git status --short`
Diff: !`git diff --stat HEAD 2>/dev/null`
Untracked: !`git ls-files --others --exclude-standard`

## Environment detection

Python (pyproject.toml / ruff.toml): !`find . -maxdepth 3 \( -name "pyproject.toml" -o -name "ruff.toml" \) ! -path "*/node_modules/*" 2>/dev/null | head -3`
JS/TS (package.json with eslint): !`find . -maxdepth 3 -name "package.json" ! -path "*/node_modules/*" 2>/dev/null | xargs -r grep -l '"eslint"' 2>/dev/null | head -3`
PHP (composer.json): !`find . -maxdepth 2 -name "composer.json" 2>/dev/null | head -2`

Existing plans: !`ls .claude/plans/*.md 2>/dev/null | grep -oE '(feat|fix|docs|style|refactor|perf|test|build|ci|chore|revert)-[0-9]+' | grep -oE '[0-9]+' | sort -n | tail -1`

---

## Instructions

### 0. Preflight

**Auto-detect linters** from the configuration files found above:

| Condition                                    | Command                        |
| -------------------------------------------- | ------------------------------ |
| `pyproject.toml` or `ruff.toml` in `<dir>/`  | `cd <dir> && ruff check .`     |
| `package.json` with eslint in `<dir>/`       | `cd <dir> && npm run lint`     |
| `composer.json` at the repository root       | `./vendor/bin/pint --test`     |
| None detected                                | skip linting and carry on      |

If linting fails: list the offending files and stop. Only continue once it is
clean or the user explicitly accepts the failures.

**Conventions.** If `CLAUDE.md` documents branch or commit conventions, those
win over this document — say so and follow them. If it documents none, tell the
user:

> No documented conventions found. Defaulting to Conventional Commits 1.0.0
> (`feat|fix|docs|style|refactor|perf|test|build|ci|chore|revert`), branches
> named `<type>/<issue>-<slug>`, and English for every committed artefact.

### 1. Issue numbering

**On GitHub, issues and pull requests share one sequence.** The next issue
number is one past the highest issue *or* PR, whichever is greater — a repo
whose last PR was #12 opens its first issue as #13, not #1. Check both before
assuming.

- User supplied an argument → use that `N`.
- No argument → highest number found in `.claude/plans/`, plus one.
- No previous plans → ask the user, mentioning the shared-sequence rule above.
- Each additional group increments `N` by one.

### 2. Group the changes

Criteria for each group:

- **Cohesion**: resolves exactly one user story or fixes exactly one bug.
- **Atomicity**: can be merged and deployed on its own, without waiting for a
  sibling group.
- **Size**: prefer small groups. Related code (the backend and frontend of one
  feature) belongs together; unrelated features that happen to touch the same
  file get separated.
- **Reviewability**: if a reviewer could not hold the diff in their head, split
  it.

### 3. Issue type (interactive)

For **each group**, determine its type and present the draft with
`AskUserQuestion` before writing any file.

| Nature of the group                              | Type    | Template                 | Branch                      |
| ------------------------------------------------ | ------- | ------------------------ | --------------------------- |
| New capability or user-visible improvement        | `feat`  | `issue-templates/feat.md`  | `feat/<N>-<slug>`         |
| Incorrect behaviour that needs correcting         | `bug`   | `issue-templates/bug.md`   | `fix/<N>-<slug>`          |
| Dependencies, CI/CD, infra, technical cleanup     | `chore` | `issue-templates/chore.md` | `chore/<N>-<slug>`        |
| Technical investigation to reduce uncertainty     | `spike` | `issue-templates/spike.md` | `spike/<N>-<slug>`        |

Templates live under `.claude/skills/release-plan/`.

**Branch type follows the work, not the issue label.** A refactor with no
behaviour change is `refactor/<N>-<slug>`; a documentation-only change is
`docs/<N>-<slug>`. The branch prefix comes from the same vocabulary as the
commit types in §5, so a chore is never disguised as a feature.

**Slug**: lowercase kebab-case, English, three to five words, no issue type
repeated inside it (`chore/13-dependabot-monthly`, not
`chore/13-chore-dependabot`).

---

#### Draft for **feat**

```
# Issue #N — <title framed around the value delivered>

## User story

**As a** <role performing the action>,
**I want** <concrete action or capability>,
**so that** <benefit or value obtained>.

### Context

<Current state of the system and why this story is needed. Include the
end-to-end flow it fits into, the layers or modules it touches, and any
dependency on other stories.>

### Business value

<One or two sentences: what becomes unblocked or measurably better once this
ships. Connect it to the product's goal.>

---

## Acceptance criteria

\`\`\`gherkin
Scenario: <name — happy path>
  Given <precondition>
  When <user action>
  Then <observable outcome>

Scenario: <name — edge or alternative case>
  Given <precondition>
  When <action>
  Then <observable outcome>
\`\`\`

---

## Out of scope

- <item, with a reference to the story that will cover it>

Labels: feat · <area>
```

Rules:

- At least two Gherkin scenarios: the happy path plus one edge or alternative
  case.
- No implementation detail in the story. Present tense, observable outcomes
  ("can see", "records"), never "the code should".
- Context and Business value are mandatory — do not drop them even when they
  feel obvious.

---

#### Draft for **bug**

```
# Issue #N — [Bug] <what fails, and in what context>

**Summary:** <one sentence>

## Steps to reproduce

1. <step>
2. <step>

**Expected:** <what should happen>
**Actual:** <what happens instead>

## Impact

| Field       | Value                                                |
| ----------- | ---------------------------------------------------- |
| Frequency   | Always / Frequent / Intermittent / Conditional       |
| Blocks prod | Yes / No                                             |
| Workaround  | Yes (<which>) / No                                   |
| Affected    | <who hits it>                                        |

## Definition of done

- [ ] A regression test reproduces the bug and fails before the fix
- [ ] <verifiable criterion>

Labels: bug · <area>
```

Rules:

- The regression test is not optional: a bug with no failing test has no proof
  it was fixed, nor any guard against its return.
- Expected and Actual must be distinguishable by someone who has never seen the
  system.

---

#### Draft for **chore**

```
# Issue #N — [Chore] <what needs doing>

## What

<Description of the task. No user story: this work is internal.>

## Why now

<Concrete justification: EOL date, CVE, measured regression, blocked upgrade.
Without one, chores are deprioritised forever.>

## Scope

<Modules, services, files or infrastructure in scope.>

## Definition of done

- [ ] <verifiable criterion>
- [ ] CI is green
- [ ] No new lint warnings

## Risk / rollback

<Fill in only for CI/CD, critical dependencies, infrastructure, database
migrations or production configuration.>

- **Main risk:** <what could go wrong>
- **Rollback:** <how to revert>

Labels: chore · <area>
```

Rules:

- A verifiable Definition of done replaces Gherkin here. Forcing scenarios onto
  a dependency bump produces theatre, not clarity.
- "Why now" is the field that decides whether this gets scheduled. Never leave
  it as "keeping things up to date".

---

#### Draft for **spike**

```
# Issue #N — [Spike] <what is being investigated>

## Context

<The uncertainty or risk that motivates the spike, and the decision that is
blocked until it is resolved.>

## Questions to answer

- [ ] <concrete, answerable question>
- [ ] <concrete, answerable question>

## Timebox

<hours or days> — when it expires, report what was learned rather than
extending silently.

## Deliverable

<Where the answer lands: an ADR, a section in a README, a throwaway branch, a
decision recorded in CLAUDE.md.>

Labels: spike · <area>
```

Rules:

- A spike delivers knowledge, not production code. If the output is meant to
  ship, it is a `feat`.
- Every question must be answerable with evidence, not opinion.

---

Offer the user **"Approve"** / **"Adjust"**. If they adjust, show the corrected
draft before continuing.

### 4. Write the plan file

Create `.claude/plans/` if missing (`mkdir -p .claude/plans`), then write one
file per group, named `<branch-type>-<N>-<slug>.md`:

- `.claude/plans/feat-13-turn-bucketer.md`
- `.claude/plans/fix-14-pot-rounding.md`
- `.claude/plans/chore-15-dependabot-monthly.md`

**Plan file structure:**

---

```
# Issue #N — <title>

[The issue section for this type — see the drafts in §3.]

---

# Branch plan — <type>/<N>-<slug>

## Header

Branch: `<type>/<N>-<slug>` | Issue: #N | Base: `main`

## Files in this group

| File           | State                     | Description |
| -------------- | ------------------------- | ----------- |
| `path/to/file` | New / Modified / Deleted  | What it does |

## Commits

### Commit 1 — <subject>

\`\`\`
<type>[(scope)]: <imperative verb> <description>

<Optional body: the WHY. Omit when the subject already explains it.
Wrapped at 72 characters.>

Refs #N
\`\`\`

Files: `path/to/file`

\`\`\`bash
git add path/to/file
git commit
\`\`\`

### Commit N — <last>

\`\`\`
<type>[(scope)]: <imperative verb> <description>

Closes #N
\`\`\`

Files: `path/to/last`

\`\`\`bash
git add path/to/last
git commit
\`\`\`

## Execution order

\`\`\`
1 → 2 → ... → N
\`\`\`

## Pull request

**Title:** `<type>[(scope)]: <imperative verb> <description>`

The PR title follows the same Conventional Commits grammar as the commits,
because a squash merge turns it into the commit message on `main`.

**Body:** see `.claude/skills/release-plan/pr-template.md`.

\`\`\`markdown
## What does this PR do?

<Context paragraph.>

**Main changes:**

1. **<Area>**: <what it does and why>

Closes #N

## How to test

1. ...

## Type of change

- [ ] feat  - [ ] fix   - [ ] refactor  - [ ] perf
- [ ] docs  - [ ] test  - [ ] build     - [ ] ci     - [ ] chore

## Deploy considerations

[only the ones that apply]

## Checklist

- [ ] CI green
- [ ] Tested locally
- [ ] Self-review complete
\`\`\`

## Post-commit verification

[Repeat the lint commands detected in §0, or "(no linters detected)".]
```

---

### 5. Commit rules

**Every committed artefact is written in English** — subject, body, branch name
and PR title — regardless of the language of the plan or the conversation.

Commits follow **Conventional Commits 1.0.0**:

```
<type>[(optional scope)][!]: <description>

[optional body]

[optional footers]
```

- **Type**: one of `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`,
  `build`, `ci`, `revert`, `chore`. `feat` and `fix` are the only two that
  describe user-facing change; everything else is internal.
- **Scope** (optional): the area touched, lowercase, in parentheses —
  `feat(domain):`, `fix(parser):`, `ci(workflow):`. Add one when the repository
  has clear modules and the subject would otherwise be ambiguous. Omit it rather
  than inventing a vague one.
- **Description**: the first token after the colon is an **imperative,
  lowercase English verb** — `add`, `migrate`, `remove`, `fix`. Never a noun
  (`migration`, `support`) and never a gerund (`adding`). No trailing period.
- **Length**: **60 characters preferred, 72 the hard ceiling** for the whole
  subject line. Detail belongs in the body.
- **Body**: English, separated from the subject by a blank line, wrapped at
  **72 characters**. Mandatory for non-trivial changes, optional for typos and
  cosmetic fixes. Explain *why*, not *what* — the diff already shows what.
- **Breaking changes**: append `!` before the colon **and** add a
  `BREAKING CHANGE: <consequence>` footer describing what consumers must update.
  Both, not either.
- **Footers**: `Refs #N` on intermediate commits, `Closes #N` on the last one.
  GitHub also accepts `Fixes #N` and `Resolves #N`.
- **Validate before writing**:

  ```bash
  bash .claude/skills/release-plan/validate-msg.sh "<proposed subject>"
  ```

  Only use subjects that return `✓ OK`. The script enforces the grammar, the
  imperative mood and the 72-character ceiling; it warns above 60.

**Commit grouping inside a PR:**

- Schema or migration changes → their own commit, always first.
- Backend business logic → one or two commits depending on size.
- Frontend / UI → its own commit, last.
- Configuration that motivates the change → alongside it, or separate when
  large.
- Each commit should leave the repository in a working state. A commit that
  knowingly breaks the build is a sign the split is wrong.

### 6. Final summary

After writing every file:

```
| File                                       | Issue | Branch                        | Commits |
| ------------------------------------------ | ----- | ----------------------------- | ------- |
| .claude/plans/feat-13-turn-bucketer.md     | #13   | feat/13-turn-bucketer         | 3       |
| .claude/plans/chore-14-dependabot-monthly.md | #14 | chore/14-dependabot-monthly   | 1       |
```

State how many files were created and the recommended merge order (migrations
before the frontend that consumes them; independent features in any order).

Close by reminding the user that this skill wrote documents only — the issues,
branches, commits and PRs are theirs to create.
