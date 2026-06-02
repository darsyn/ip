# Security Policy
**Please do not report security vulnerabilities through public GitHub issues,
pull requests, or discussions.** Use the private channel described below.

## Supported Versions
Security fixes are released for the versions listed below. `darsyn/ip` follows a
deliberate compatibility policy: a major line drops a PHP version **only** when
newer language syntax makes the older version impossible to support.

| Version | Status | PHP | Source |
|---|---|---|---|
| `6.x` | ✅ Actively developed | `7.1` and above (confirmed up to `8.5`) | [`6.x`](https://github.com/darsyn/ip/tree/6.x) |
| `5.x` | ✅ Security & bug fixes only | `5.6` to `8.3` | [`5.x`](https://github.com/darsyn/ip/tree/5.x) |
| `4.x` | ❌ End of life — no security or bug fixes | `5.6` to `8.1` | |

If you are on an end-of-life line, the fix for any reported issue will be to
upgrade to a supported one.

## Reporting a Vulnerability
Please report security vulnerabilities **privately** using GitHub's private
vulnerability reporting: go to the Security tab and click [Report a
vulnerability](https://github.com/darsyn/ip/security/advisories/new). If you are
unable to use GitHub for this, email
[**`hello+reporting@zanbaldwin.com`**](mailto:hello+reporting@zanbaldwin.com) instead.

To help triage and resolve the report quickly, please include as much of the
following as you can:
- the type of issue (e.g. incorrect address classification, parsing flaw,
  comparison/range bug with a security impact);
- the full path of the source file(s) involved;
- the affected tag, branch, or commit (or a direct URL);
- any special configuration required to reproduce the issue;
- step-by-step instructions to reproduce it;
- proof-of-concept or exploit code, if you have it; and
- the impact — including how an attacker might exploit the issue.

## What to Expect
This is a volunteer-maintained open-source project. With that in mind:

- I will acknowledge your report as soon as I read it (email notifications are turned on for reporting via GitHub and filtered to never be sent to spam).
- A confirmed issue is fixed and a new release is published **before** any public advisory goes out.
- With your permission, you will be credited in the advisory.
- There is no bug bounty or monetary reward; I will not agree to fixed-date embargoes, multi-vendor coordination, or NDAs.

## Scope
**In scope:** defects with a security impact in this library's own code
(`src/`) — IP address parsing, binary conversion, comparison, range/CIDR
calculations, and the RFC-based classification helpers.

**Out of scope:** vulnerabilities in your application's _use_ of the library,
issues in third-party or bundled dependencies (please report those to their
respective maintainers), and the documentation site's hosting infrastructure.

A note on using this library for security decisions: the classification helpers
(`isPublicUse()`, `isPrivateUse()`, etc) and CIDR helpers (`inRange()`, etc)
report **RFC-defined categories**. When you rely on them for an access-control
decision (allow/deny-list, SSRF guard, etc), canonicalise the address first and
treat the result as **one layer of defence**, not the only one.

## A Note on AI-Assisted Reports
In the age of AI, any security exploit found by AI should be assumed to already
be known to the public. AI-assisted reports hold the same weight as those
reported by humans, but _accountability always lands with the human controlling
the AI_. Use of AIs does not disqualify reports; unnecessary and abusive
reporting does.

> tl;dr: AI-assisted reports welcome, must be human-verified.
