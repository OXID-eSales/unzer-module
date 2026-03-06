# Security Considerations

This document describes known security considerations that have been reviewed and intentionally left unfixed, either because they require architectural changes, have very low practical risk, or are mitigated by other factors.

## Webhook Dispatcher Without Signature Verification

**File:** `src/Controller/DispatcherController.php`
**Risk:** High (architectural)

The Unzer webhook dispatcher does not verify webhook authenticity via cryptographic signatures. Unlike Stripe or PayPal, the Unzer API does not provide webhook signatures. The module currently accepts any POST request to the dispatcher endpoint.

**Mitigation:** The module fetches payment details from the Unzer API using the `retrieveUrl` from the webhook payload. This means the actual payment data comes from Unzer's API (authenticated via the module's private key), not from the webhook body itself. An attacker cannot forge a successful payment — they can only trigger the module to re-check a payment's status at Unzer.

**Recommendation for future improvement:**
- Validate `retrieveUrl` against Unzer API domains (`https://api.unzer.com/`, `https://sbx-api.unzer.com/`)
- Consider IP whitelisting for known Unzer webhook source IPs
- Implement a shared secret or bearer token for webhook authentication

## `|raw` on CMS Content

**File:** `views/twig/frontend/tpl/order/unzer_sepa.html.twig` (OXID 7), Smarty equivalent (OXID 6.5)
**Risk:** Low

CMS content (`oxcontents__oxcontent`) is rendered with `|raw` to allow HTML formatting (e.g., SEPA mandate text). CMS content is managed by shop administrators.

**Mitigation:** CMS content can only be edited by authenticated administrators. The risk is limited to scenarios with a compromised admin account. Removing `|raw` would break the HTML formatting of legal texts. Using an HTML sanitizer (HTMLPurifier) would be a cleaner approach but adds complexity.

## SQL View-Name Interpolation

**Files:** `src/Model/TransactionList.php`, `src/Model/DiscountList.php`
**Risk:** Low

Dynamic view names from `getViewName()` are interpolated into SQL queries. This is a standard OXID framework pattern — `getViewName()` returns a value derived from internal configuration, not from user input.

**Mitigation:** The view name comes from OXID's core view-name registry which is populated at framework initialization. There is no known path for user input to influence this value. The pattern is used throughout the OXID core itself.

## Race Condition in Order Finalization

**Context:** Webhook callbacks and frontend redirects can process the same order concurrently.
**Risk:** Medium

There is no database locking (SELECT FOR UPDATE) during order finalization. If the webhook callback and the customer's browser redirect arrive simultaneously, concurrent processing could lead to inconsistent order state or duplicate capture attempts.

**Mitigation:** The Unzer SDK handles duplicate capture attempts gracefully (idempotent operations). The practical impact is limited to potentially inconsistent order status display, which can be corrected by re-querying the Unzer API. A full fix would require database-level locking across the order finalization flow.

---

*Last updated: 2026-03-06*
