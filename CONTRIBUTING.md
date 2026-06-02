# Contributing
`darsyn/ip` is a small, solo-maintained library. Bug reports, fixes,
documentation improvements, and ideas are all genuinely welcome. Reviews can
take a little while because I don't check my email every day.

New to open source? Welcome! 🥳

> By participating, you agree to uphold this project's [Code of
> Conduct](CODE_OF_CONDUCT.md).

## Before you start
- **Branch:** open pull requests against
  [`6.x`](https://github.com/darsyn/ip/tree/6.x); the `5.x` branch receives bug
  fixes only.
- **Already documented:** the [README](README.md), the
  [CHANGELOG](CHANGELOG.md), and the [`docs/`](docs/) folder describe the
  current behaviour and API.
- **Security issues:** please do **not** open a public issue or pull request for
  these. Report them privately via GitHub's [_report a
  vulnerability_](https://github.com/darsyn/ip/security/advisories/new) button.
  See [`SECURITY.md`](SECURITY.md) for the full policy.

## Reporting bugs & asking questions
Search the [existing issues](https://github.com/darsyn/ip/issues) first — it may
already be known. When opening a new one, the fastest path to a fix is to
include:

- the PHP version you're running,
- which class is involved (`IPv4`, `IPv6`, or `Multi`),
- the address or input that triggers it, and
- what you expected versus what actually happened.

## Compatibility policy
The most important value of this library is maximum compatibility with PHP
versions. The minimum supported PHP version (MSPV) for the `6.x` branch is PHP
7.1.
The MSPV will **only** be raised if future versions of PHP introduce required
syntax that older versions of PHP cannot support. This is a **hard rule**;
support will not be dropped for the sake of modernization.

> `6.x` bumped the MSPV to PHP 7.1 because PHP 8.4 requires explicit nullable
> types which PHP 7.0 does not support. `5.x` still receives bug fixes for those
> who need MSPV as low as PHP 5.6.

This project relies on what looks like dead, outdated code at first glance
(runtime feature-detection, weird quirks, type coercion, etc) to work across all
supported PHP versions. This is intentional.
Pull requests that raise the minimum supported PHP version will be declined.

## Pull requests
For anything substantial, feel free to open an issue first to talk it through.
You may skip straight to a pull request if you feel comfortable.

- Try to keep each pull request to one change or fix, along with the unit tests
  that cover it (keeps things small and easy to review and give feedback on).
- Follow [Conventional Commits](https://www.conventionalcommits.org/), and try
  to match the emoji style of the existing commit log.
- Note user-facing changes in the [CHANGELOG](CHANGELOG.md).
- You deserve credit! Add your name to the list of authors in the
  [README](README.md), if you're comfortable.

### Running the test suite
Pull requests are expected to pass the CI test suite, which consists of:
- syntax linting,
- ensuring code is formatted according to this project's
  [ruleset](.php-cs-fixer.dist.php) via
  [PHP-CS-Fixer](https://github.com/php-cs-fixer/php-cs-fixer),
- pass all [unit tests](tests/) via [PHPUnit](https://phpunit.de) and _not
  decrease test coverage_, and
- pass static analysis via [PHPStan](https://phpstan.org).

If you have Docker installed, the suite can be run locally via the [local
testing script](tests/local.sh): `bash tests/local.sh`.
If you do not have Docker installed then: push, make a pull request, and let
GitHub do it for you.

## A note on AI-assisted contributions
Use whatever helps you write good code (including AI). The one rule is that you
understand and stand behind what you submit: you should be able to explain what
your change does and why it's correct, just as if you'd typed every line
yourself. Accountability for a contribution always rests with the human
submitting it.
