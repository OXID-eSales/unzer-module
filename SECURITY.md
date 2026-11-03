# Security Considerations

This document describes known security considerations that have been reviewed and intentionally left unfixed, either because they require architectural changes, have very low practical risk, or are mitigated by other factors.

## Webhook Dispatcher Without Signature Verification

**File:** `src/Controller/DispatcherController.php`
**Risk:** Low

Unzer does not sign its webhook notifications, so the dispatcher cannot prove that a call really comes from Unzer. For this reason the module never trusts the content of a notification:

- The payment state is never read from the request body. The module takes only the resource id from `retrieveUrl` and fetches the resource from the Unzer API itself, authenticated with the merchant's private key and sent to the API host the SDK is configured for. A notification therefore cannot mark an order as paid or cancelled unless Unzer reports that state.
- The private key is never sent to the host named in `retrieveUrl`.
- Before the Unzer API is contacted, the call is rejected unless `retrieveUrl` uses `https` and an Unzer API host, the referenced transaction is already known to the shop, and the `context` parameter matches a registered webhook.

A forged call can therefore only make the shop read the current state of an existing payment from Unzer and process it, as the genuine notification would. Each such call costs one outbound API request. Shops that want to limit this can rate-limit `cl=unzer_dispatcher` in the web server.

**Planned improvement:** a random token per registered webhook URL, verified by the dispatcher.

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
