# Changelog

All notable changes to **WHMCS Automation Hub** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-08-09

### Added
- Initial v1.0.0 release of **WHMCS Automation Hub** by [Web Wave Digital](https://webwavedigital.co.in).
- Core pluggable `TriggerInterface` and `ActionInterface` architecture.
- Central `RuleEngine` with automated rule execution, execution timer in milliseconds, error handling, and JSON payload logging.
- `HttpClient` with SSRF (Server-Side Request Forgery) protection blocking local and private IP ranges.
- 10 Built-in Triggers:
  - `order_placed` (`AfterShoppingCartCheckout`)
  - `invoice_paid` (`InvoicePaid`)
  - `invoice_payment_failed` (`InvoicePaymentFailed`)
  - `service_suspended` (`ServiceSuspended`)
  - `service_unsuspended` (`ServiceUnsuspended`)
  - `service_terminated` (`ServiceTerminated`)
  - `ticket_open` (`TicketOpen`)
  - `ticket_user_reply` (`TicketUserReply`)
  - `domain_expiring` (`DailyCronJob` check for 30/14/7 days)
  - `client_add` (`ClientAdd`)
- 4 Built-in Actions:
  - `webhook` (Generic HTTP POST JSON)
  - `slack` (Slack Block Kit message payload)
  - `discord` (Discord Embed payload)
  - `email` (Custom HTML email notification)
- WHMCS Admin UI:
  - Rules Dashboard with instant "Test Rule" execution.
  - Dynamic Action Config Form rendered from `getConfigFields()`.
  - Paginated Activity Log viewer with expandable payload modal.
- Open source developer suite: PHPUnit tests, mock JSON fixtures, `CONTRIBUTING.md`, `README.md`, `SETUP.md`, and GitHub issue templates.
