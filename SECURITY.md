# Security Policy

## Supported Versions

| Version | Supported          |
|---------|--------------------|
| 1.x     | Yes                |

## Reporting a Vulnerability

If you discover a security vulnerability in Lead Insights, please report it responsibly.

**Do NOT open a public GitHub issue for security vulnerabilities.**

Instead, please email: **contact@isapp.be**

### What to include

- A description of the vulnerability
- Steps to reproduce the issue
- The potential impact
- Any suggested fix (optional)

### Response timeline

- **Acknowledgement:** Within 48 hours
- **Initial assessment:** Within 5 business days
- **Fix and release:** As soon as possible, depending on severity

### After a fix is released

- A security advisory will be published on the GitHub repository
- The fix will be included in a patch release with a changelog entry

## Security Design

Lead Insights is designed with privacy and security as defaults:

- **No IP addresses or user agents are stored** — ever
- **No cookies are set without explicit visitor consent** (when `consent_required` is enabled, which is the default)
- **No cross-site tracking or fingerprinting**
- All data is stored within Statamic's existing form submission storage
- No external services are contacted (except Statamic license verification)

## Scope

This policy covers the `isapp/statamic-lead-insights` addon only. For vulnerabilities in Statamic itself, please refer to the [Statamic Security Policy](https://github.com/statamic/cms/security/policy).