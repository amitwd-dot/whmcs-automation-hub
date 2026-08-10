# Maintainer Setup & Release Guide

This document is a short reference guide for **Web Wave Digital** maintainers to create and launch the **WHMCS Automation Hub** repository on GitHub.

---

## 1. 🐙 Creating the GitHub Repository

### Recommended Repository Settings
- **Repository Name**: `whmcs-automation-hub`
- **Owner**: `webwavedigital` (or your preferred GitHub org/user)
- **Description**: `Open-source event-driven automation engine for WHMCS. Connect WHMCS events (orders, invoices, tickets, services, domains) to Webhooks, Slack, Discord, & Email.`
- **Visibility**: Public
- **License**: MIT (LICENSE file is already included in root)

### Recommended Topics / Tags for Discoverability
In GitHub repository settings under **Topics**, add:
`whmcs`, `whmcs-addon`, `whmcs-module`, `automation`, `webhooks`, `slack-integration`, `discord-webhook`, `php`, `open-source`

---

## 2. 🚀 Initial Git Push & Release Checklist

### Step A: Initialize Git & Push to GitHub
Run the following commands inside the `automationhub` module directory:

```bash
cd /path/to/modules/addons/automationhub

# Initialize repository
git init

# Add all module & documentation files
git add .

# Initial commit
git commit -m "feat: initial release v1.0.0 of WHMCS Automation Hub by Web Wave Digital"

# Rename branch to main
git branch -M main

# Add remote origin
git remote add origin https://github.com/webwavedigital/whmcs-automation-hub.git

# Push to GitHub
git push -u origin main
```

### Step B: Create Tag & GitHub Release
1. Create Git tag for version 1.0.0:
   ```bash
   git tag -a v1.0.0 -m "Release v1.0.0 - Initial Production Release"
   git push origin v1.0.0
   ```
2. Navigate to **GitHub** -> **Releases** -> **Draft a new release**.
3. Select Tag: `v1.0.0`.
4. Release Title: `WHMCS Automation Hub v1.0.0`.
5. Release Description: Copy summary from `CHANGELOG.md`.
6. Attach Zip Archive: Zip the contents of `automationhub/` folder into `whmcs-automation-hub-v1.0.0.zip` and attach to release assets.

---

## 3. 🛡️ Code Maintenance & Community PR Workflow
- When community developers submit PRs adding new triggers or actions, ensure they follow the single-file pattern in `/triggers/` or `/actions/`.
- Verify SSRF checks are maintained for any HTTP actions.
- Keep PHPUnit test suite updated.
