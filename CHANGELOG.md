# Changelog

All notable changes to WilyCode HTTP Response are documented here.

## [Unreleased]

## [0.1.0] - 2026-10-16

### Added

- Initial public release of the typed PSR-7 response factory.
- Helpers for JSON, HTML, redirect, and empty responses.
- `HttpStatus` enum with commonly used HTTP status codes, including 301 and 302 redirects.
- PHP 8.4 tests and GitHub Actions workflow.
- Installation, usage, and API documentation.

### Fixed

- Reject `204 No Content` and `304 Not Modified` in `json()` and `html()`; use `empty()` for those statuses.
- Accept only actual redirect statuses (`301`, `302`, `303`, `307`, `308`) in `redirect()`.
- Add regression tests for invalid status/body combinations and all supported redirect statuses.
