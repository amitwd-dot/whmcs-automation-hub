---
name: New Trigger Request
about: Propose a new WHMCS event trigger for Automation Hub
title: '[Trigger Request]: '
labels: 'enhancement, trigger'
assignees: ''
---

### ⚡ Proposed Event Trigger

**Trigger Name:**  
*(e.g., Domain Registration Failed, Client Password Reset, Quote Created)*

**WHMCS Hook Name:**  
*(e.g., `DomainRegisterFailed`, `ClientChangePassword`)*

**Description:**  
*Explain when this event fires and why it would be useful.*

**Expected Payload Data:**  
```json
{
  "event": "domain_register_failed",
  "domain_id": 123,
  "domain_name": "example.com",
  "error": "Registrar connection error"
}
```

**Additional Context:**  
*Add any other context or documentation links about the WHMCS hook here.*
