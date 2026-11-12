# Contributing to Deliberia

Deliberia is a personal learning tool released as open source. Contributions are welcome,
but the product scope is deliberate: see [ROADMAP.md](ROADMAP.md) for what is (and is not)
in scope before proposing a feature.

## Set up

Requirements: Docker with Compose, Git and [pre-commit](https://pre-commit.com).

```bash
git clone https://github.com/Mvh18/deliberia.git
cd deliberia
pre-commit install --hook-type pre-commit --hook-type commit-msg
docker compose up -d --build --wait    # or: make up
```

More detail in the [README](README.md).

## Workflow

- Branch from `main` using `<type>/<issue>-<slug>`, for example `feat/12-resource-model`.
  Types: `feat`, `fix`, `docs`, `chore`, `refactor`, `test`, `ci`, `build`, `perf`.
- Commit messages follow [Conventional Commits](https://www.conventionalcommits.org/);
  the `commit-msg` hook rejects anything else.
- Open a pull request against `main` (squash merge). The PR template lists what to include.
- Larger changes are specified first with OpenSpec; see [docs/WORKFLOW.md](docs/WORKFLOW.md).

## Before you push

```bash
docker compose exec app composer test       # Pint, Larastan and the test suite  (or: make test)
docker compose exec vite npm run check       # frontend format and lint            (make lint runs all checks)
docker compose exec vite npm run types:check # TypeScript
```

CI runs the same checks plus a secret scan; it must be green before merging.

## Never commit

Secrets, tokens, `.env` files, database dumps or personal data (real email addresses,
local paths). Use `example.com` addresses in tests and fixtures. The hooks and CI scan for
this, but they are the last line of defence, not the first.
