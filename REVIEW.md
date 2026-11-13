# Application review

Review scope: static inspection of the local PHP, JavaScript, CSS, and cPanel PHP settings. No production credentials were accessed, no production files or database were changed, and production parity has not been established. Severity reflects the exposed behavior in source, not a live exploit test.

## Prioritized findings

### Critical — database credential committed in local source

**Confirmed.** `global.php` contained a literal database password. It has been removed from source; database settings now load from environment variables or a PHP config path supplied by `CHORES_CONFIG_FILE`. This workspace had no Git history before this import. The password should be rotated at the host because it was present in the working copy. Do not reuse it in tests or documentation.

### High — state-changing forms lacked CSRF protection (addressed on follow-up branch)

**Confirmed in imported source; addressed in `improve/security-ux`.** Chore submission, approval/rejection, password changes, payout recording, and logout forms now include and verify CSRF tokens.

### High — payout could be triggered with a GET request (addressed on follow-up branch)

**Confirmed in imported source; addressed in `improve/security-ux`.** GET now displays a confirmation, and a parent-only, CSRF-protected POST records the entry. The existing negative-balance ledger formula is preserved pending verification.

### High — server-side authorization gap in approval handler (addressed on follow-up branch)

**Confirmed in imported source; addressed in `improve/security-ux`.** The endpoint still checks parent status, accepts only numeric selected IDs, permits one explicit action, and updates only rows currently pending.

### High — session fixation and long-lived session (partially addressed on follow-up branch)

**Confirmed in imported source; partially addressed in `improve/security-ux`.** Login now regenerates the session ID, and cookies use HttpOnly, SameSite=Lax, and HTTPS Secure when served over HTTPS. The 30-day lifetime remains; review the desired duration for the live site.

### High — PHP errors exposed in production configuration

**Confirmed.** The local cPanel `php.ini` sets `display_errors = On`. The file is excluded from Git because it is host-specific. Production should log errors to a private location and turn display off using cPanel's PHP configuration controls.

### Medium — reflected/stored XSS risk (partially addressed on follow-up branch)

**Confirmed unsafe output sites in imported source.** The follow-up branch escapes names, chore labels, balances, dates, and visible form messages in the pages reviewed. A full endpoint-by-endpoint output audit should continue alongside PHP runtime validation.

### Medium — approval status update ignored current state and outcome (addressed on follow-up branch)

**Confirmed in imported source; addressed in `improve/security-ux`.** The handler ignores controls and token fields, accepts checked numeric IDs, requires a single action, and only updates pending entries. Database statement failures still need consistent handling.

### Medium — broad family data visibility

**Confirmed behavior; intent unknown.** History and balance queries are not scoped to the current account. This may be intentional for a shared family tracker; confirm the privacy model before changing it.

### Medium — duplicate chore submissions and concurrency

**Potential issue.** A member can submit the same chore repeatedly and parallel requests can create duplicate records. No schema-level uniqueness or business rule is available. Define repeat-submission rules and add an appropriate constraint/check only after inspecting the actual DDL and intended schedule.

### Medium — database failures disclose internals

**Confirmed.** Several pages call `die()` with raw connection errors. Replace these with generic user feedback and private logging.

### Low — CSS and JavaScript defects

**Confirmed.** `chores.css` contains LESS-only nested selectors, variables, and color functions but is delivered directly as CSS. `chores.js` automatically checks the first checkbox after a delay. The shared layout does not currently include the JS file, but that mutation should be removed if it is ever loaded. Several form controls have weak labels/focus treatment and the fixed 45px sizing is not mobile friendly.

**Follow-up.** The stylesheet is now valid responsive CSS, and the auto-check behavior has been removed while keeping the `chores.js` path for service-worker compatibility. The public production login was checked read-only: it registers `/service-worker.js` and references `/manifest.json` plus icons. Manifest and worker text have been recovered locally; production icons and favicon are missing from the workspace and must be retained on the server during deployment. This does not prove that the rest of production matches local source.

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
