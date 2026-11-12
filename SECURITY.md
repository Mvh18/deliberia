# Security policy

## Reporting a vulnerability

Please **do not open a public issue** for security problems.

Report them privately through GitHub's
[Private Vulnerability Reporting](https://github.com/Mvh18/deliberia/security/advisories/new)
(repository → **Security** → **Report a vulnerability**).

Include what you found, how to reproduce it and the impact you expect. You will get an
acknowledgement as soon as possible; this is a personal project maintained in spare
time, so there is no guaranteed response time.

## Scope

Deliberia is a personal, self-hosted learning tool. Reports about the application code,
its Docker setup and its CI pipeline are welcome.

## If you find a leaked secret

If you notice a credential, token or personal data in the repository or its history,
report it through the same private channel instead of opening an issue, so it can be
revoked and removed before it is amplified.

## Keeping this repository clean

- `.env` and other local files are git-ignored; only `.env.example` (placeholders and
  local-only development values) is versioned.
- Commits are scanned for secrets by [gitleaks](https://github.com/gitleaks/gitleaks) in a
  pre-commit hook and again in CI over the full history.
- See [docs/WORKFLOW.md](docs/WORKFLOW.md) for the checklist run before publishing.
