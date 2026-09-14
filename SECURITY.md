# Security Policy

## Supported versions

| Package line | Maintenance |
| --- | --- |
| `2.x` | Active development, bug fixes and security fixes |
| `1.x` | Security fixes only |

Use the latest patch release in your supported line. Framework compatibility is documented separately in the [README](README.md#version-compatibility).

## Reporting a vulnerability

Please [report a vulnerability privately on GitHub](https://github.com/mortalkiller/filament-page-header/security/advisories/new). Private vulnerability reporting is enabled for this repository. Do not disclose an unpatched vulnerability in a public issue or pull request.

Include:

- Package, PHP, Laravel and Filament versions.
- A minimal reproduction and the affected configuration or component.
- The expected behavior, actual behavior and likely impact.
- Any prerequisites needed to reproduce the issue.

Use fictional data and remove credentials, tokens and personal information. Ordinary bugs and feature requests belong in [GitHub Issues](https://github.com/mortalkiller/filament-page-header/issues).

## Response and disclosure

The maintainer reviews reports on a best-effort basis; there is no guaranteed response or resolution deadline. Follow-up, reproduction and remediation discussions take place in the private report. For confirmed vulnerabilities, the maintainer coordinates a fix and public disclosure, including release notes or a security advisory as appropriate. Reporter credit is subject to the reporter's consent.

## Package boundary

This package renders Filament page headers and their browser behavior. Application permissions, data retrieval and persistence remain the consuming application's responsibility. Native action authorization must remain effective, and ordinary heading and description text must be escaped unless the application explicitly opts into trusted HTML.

If the responsible component is unclear, include your reproduction in the private report so the issue can be directed to the appropriate project.
