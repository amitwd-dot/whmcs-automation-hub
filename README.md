# WHMCS Automation Hub

> **Open-Source Event-Driven Automation Engine for WHMCS**  
> Developed and Maintained by [Web Wave Digital](https://webwavedigital.co.in).

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![WHMCS Version](https://img.shields.io/badge/WHMCS-v8.0%2B-orange.svg)](https://whmcs.com)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%20%7C%208.0%20%7C%208.1%20%7C%208.2-indigo.svg)](https://php.net)

**WHMCS Automation Hub** connects internal WHMCS events (new orders, paid invoices, ticket replies, service suspensions, expiring domains, new clients) to external platforms (Generic Webhooks, Slack, Discord, Email) through a simple rule-based WHMCS Admin interface — **no custom PHP hook-writing required**.

---

## 🚀 Features

- 🔌 **100% Pluggable Architecture**: Built around clean `TriggerInterface` and `ActionInterface` contracts so developers can add new integrations by adding a single file.
- ⚡ **10 Built-in Triggers**:
  - **New Order Placed** (`AfterShoppingCartCheckout`)
  - **Invoice Paid** (`InvoicePaid`)
  - **Invoice Payment Failed** (`InvoicePaymentFailed`)
  - **Service Suspended** (`ServiceSuspended`)
  - **Service Unsuspended** (`ServiceUnsuspended`)
  - **Service Terminated** (`ServiceTerminated`)
  - **Support Ticket Opened** (`TicketOpen`)
  - **Support Ticket Client Reply** (`TicketUserReply`)
  - **Domain Expiring Soon** (`DailyCronJob` check for 30/14/7 day windows)
  - **New Client Registered** (`ClientAdd`)
- 🎯 **4 Built-in Actions**:
  - **Generic Webhook**: Posts a JSON payload to Zapier, Make, n8n, or any custom API.
  - **Slack Notification**: Sends formatted block messages to a Slack incoming webhook.
  - **Discord Webhook**: Sends rich embeds with event details to Discord.
  - **Custom Email Alert**: Sends HTML email notifications to any admin email address.
- 🛡️ **SSRF Security Protection**: Native cURL client with built-in validation blocking SSRF attempts against private ranges (`127.0.0.1`, `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`, `169.254.169.254`).
- 🧪 **"Test Rule" Button**: One-click manual execution of any rule with realistic sample data without waiting for real WHMCS events.
- 📊 **Activity Log Viewer**: Paginated audit log detailing status, execution time in milliseconds, and expandable JSON payload snapshots.

---

## 🛠️ Installation Instructions

1. Download or clone this repository:
   ```bash
   git clone https://github.com/webwavedigital/whmcs-automation-hub.git automationhub
   ```
2. Copy the `automationhub` folder into your WHMCS installation directory under `modules/addons/`:
   ```
   /your-whmcs-path/modules/addons/automationhub/
   ```
3. Log into your WHMCS Admin Area.
4. Navigate to **System Settings** -> **Addon Modules** (or **Configuration** -> **Addon Modules**).
5. Locate **WHMCS Automation Hub**, click **Activate**, and configure Administrator Access permissions.
6. Access the module from **Addons** -> **WHMCS Automation Hub**.

---

## 🖥️ Admin UI Overview

- **Automation Rules List**: Overview of active/disabled rules, target actions, last fired timestamps, and instant **Test Rule** execution buttons.
- **Rule Configuration Form**: Dynamic form that auto-renders configuration fields based on the selected action's `getConfigFields()`.
- **Activity Log**: Detailed log table with status badges (`SUCCESS` / `FAILED`), execution speeds, and an expandable modal to inspect full JSON payload snapshots and error tracebacks.

---

## 🧪 Testing

### Running Unit Tests (PHPUnit)
The business logic, registry loading, payload normalization, and SSRF security checks can be tested independently of a live WHMCS installation:

```bash
cd /your-whmcs-path/modules/addons/automationhub
vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/
```

### Manual Testing on WHMCS Staging
1. Create a test rule (e.g., *Ticket Opened* -> *Generic Webhook* pointing to [webhook.site](https://webhook.site)).
2. Click the **Test Rule** button on the rule card.
3. Verify that the sample payload is received at webhook.site and check the **Activity Logs** tab in WHMCS for a `SUCCESS` log entry.

---

## 🤝 Contributing

We welcome community contributions! Want to add a new event Trigger or Action integration?  
Read our [CONTRIBUTING.md](CONTRIBUTING.md) guide to create a PR in under 30 minutes.

---

## 📄 License & Credits

- **License**: Released under the [MIT License](LICENSE).
- **Original Author**: [Web Wave Digital](https://webwavedigital.co.in)
