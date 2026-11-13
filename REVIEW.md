# Application review

Review scope: static inspection of the local PHP, JavaScript, CSS, and cPanel PHP settings. No production credentials were accessed, no production files or database were changed, and production parity has not been established. Severity reflects the exposed behavior in source, not a live exploit test.

## Prioritized findings

### Critical — database credential committed in local source

**Confirmed.** `global.php` contained a literal database password. It has been removed from source; database settings now load from environment variables or a PHP config path supplied by `CHORES_CONFIG_FILE`. This workspace had no Git history before this import. The password should be rotated at the host because it was present in the working copy. Do not reuse it in tests or documentation.

### High — state-changing forms lack CSRF protection

**Confirmed.** Chore submission, approval/rejection, password changes, and payout recording had no anti-CSRF token. Add tokens to all state-changing forms and verify them in their handlers.

### High — payout can be triggered with a GET request

**Confirmed.** `payout.php` performs an INSERT from a query-string `peopleid`, so a parent can trigger an irreversible ledger entry by opening a URL. Change payout to a CSRF-protected POST and validate the target account. Preserve existing balance arithmetic until it is verified.

### High — server-side authorization gap in approval handler

**Confirmed.** `submitapproval.php` checked the parent's session flag, but trusted arbitrary posted IDs and updated rows without restricting them to pending chores. Scope updates to `approved = 'N'`, accept only numeric selected IDs, and never treat submit button names as row IDs.

### High — session fixation and long-lived session

**Confirmed.** Login did not regenerate the session ID, while login sets a 30-day cookie lifetime. Regenerate the ID on successful authentication and review session lifetime/cookie settings for the live HTTPS deployment.

### High — PHP errors exposed in production configuration

**Confirmed.** The local cPanel `php.ini` sets `display_errors = On`. The file is excluded from Git because it is host-specific. Production should log errors to a private location and turn display off using cPanel's PHP configuration controls.

### Medium — reflected/stored XSS risk

**Confirmed unsafe output sites.** Names and chore labels from database rows were interpolated into HTML without HTML escaping in several pages. Escape all dynamic text and attribute values consistently.

### Medium — approval status update ignores current state and outcome

**Confirmed.** The submit handler could mark any `chores_done.cdid` as approved/rejected and iterated over all POST keys, including action controls. It also did not report database failures. Restrict IDs/state and handle no-selection/errors clearly.

### Medium — broad family data visibility

**Confirmed behavior; intent unknown.** History and balance queries are not scoped to the current account. This may be intentional for a shared family tracker; confirm the privacy model before changing it.

### Medium — duplicate chore submissions and concurrency

**Potential issue.** A member can submit the same chore repeatedly and parallel requests can create duplicate records. No schema-level uniqueness or business rule is available. Define repeat-submission rules and add an appropriate constraint/check only after inspecting the actual DDL and intended schedule.

### Medium — database failures disclose internals

**Confirmed.** Several pages call `die()` with raw connection errors. Replace these with generic user feedback and private logging.

### Low — CSS and JavaScript defects

**Confirmed.** `chores.css` contains LESS-only nested selectors, variables, and color functions but is delivered directly as CSS. `chores.js` automatically checks the first checkbox after a delay. The shared layout does not currently include the JS file, but that mutation should be removed if it is ever loaded. Several form controls have weak labels/focus treatment and the fixed 45px sizing is not mobile friendly.

### Low — payout math needs characterization

**Needs verification.** Payout records an entry equal to negative `people_money.total`. This may be the intended ledger convention; do not alter it until a synthetic database test establishes how the view calculates balance and how zero/negative balances should behave.

## Recommended order

1. Rotate the exposed database credential and configure the external secret location.
2. Add CSRF protection, POST-only payouts, constrained approval updates, and session ID regeneration.
3. Escape all database-derived output and remove raw database errors.
4. Obtain schema/view DDL without personal records; create synthetic fixtures and test balances, payout, and permission boundaries.
5. Clarify whether family-wide visibility and repeated chore submissions are intentional.
6. Replace invalid CSS syntax, remove checkbox auto-selection, and review on phone/tablet sizes.
7. Confirm secure transfer support and a staging/backup/rollback plan with the host.
