#!/usr/bin/env bash
# Validates a commit subject line against Conventional Commits 1.0.0.
#
# Usage: bash .claude/skills/release-plan/validate-msg.sh "feat(domain): add turn bucketer"
#
# Exit codes: 0 = valid (warnings may still be printed), 1 = invalid.
# Spec: https://www.conventionalcommits.org/en/v1.0.0/
set -euo pipefail

msg="${1:-}"

TYPES='feat|fix|docs|style|refactor|perf|test|build|ci|chore|revert'
HARD_LIMIT=72
SOFT_LIMIT=60

fail() {
    echo "✗ FAIL: $1"
    shift
    for line in "$@"; do
        echo "  $line"
    done
    exit 1
}

if [[ -z "$msg" ]]; then
    fail "a commit subject is required as the first argument" \
        "Usage: bash .claude/skills/release-plan/validate-msg.sh \"<subject>\""
fi

# Structure: <type>[(scope)][!]: <description>
if ! echo "$msg" | grep -qE "^($TYPES)(\([a-z0-9][a-z0-9/._-]*\))?!?: .+"; then
    fail "invalid type or malformed subject" \
        "Valid types: feat, fix, docs, style, refactor, perf, test, build, ci, chore, revert" \
        "Format:      <type>[(scope)][!]: <description>" \
        "Examples:    feat: add support for camera zones" \
        "             feat(domain): bucket turn cards by texture" \
        "             refactor!: drop the legacy solver adapter"
fi

# The description must start with a lowercase imperative verb.
description="${msg#*: }"

if echo "$description" | grep -qE '^[A-Z]'; then
    fail "the description must start in lowercase" \
        "Got: \"$description\""
fi

# Imperative verbs that merely happen to end in -ing are not gerunds.
NOT_GERUNDS='bring|cling|fling|ping|ring|sing|spring|sting|string|swing|wring'
first_word="${description%% *}"

if echo "$first_word" | grep -qE '^[a-z]+ing$' && ! echo "$first_word" | grep -qE "^($NOT_GERUNDS)$"; then
    fail "the description must use the imperative, not a gerund" \
        "Got: \"$first_word\"" \
        "Write \"add\" rather than \"adding\", \"migrate\" rather than \"migrating\"."
fi

if echo "$description" | grep -qE '\.$'; then
    fail "the description must not end with a period"
fi

# Length: 72 is the hard ceiling, 60 the project's preferred maximum.
len=${#msg}

if [[ $len -gt $HARD_LIMIT ]]; then
    fail "subject too long ($len characters, maximum $HARD_LIMIT)" \
        "Move the detail into the body, wrapped at 72 characters."
fi

echo "✓ OK"

if [[ $len -gt $SOFT_LIMIT ]]; then
    echo "⚠ WARNING: $len characters — over the preferred $SOFT_LIMIT (ceiling $HARD_LIMIT)."
fi

if echo "$msg" | grep -qE "^($TYPES)(\([a-z0-9][a-z0-9/._-]*\))?!:"; then
    echo "⚠ WARNING: '!' marks a breaking change — the body needs a BREAKING CHANGE: footer."
fi

exit 0
