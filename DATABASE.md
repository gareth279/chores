# Database architecture and development notes

## Production discovery

The schema snapshot in [`database/schema.sql`](database/schema.sql) was exported on 2026-11-14 from the authenticated cPanel phpMyAdmin session. The server reported MariaDB 11.4.13. The export was configured with structure selected and data unselected for the four base tables and two views. The downloaded SQL was inspected for row-insertion statements; none were present. It contains no production database name, credentials, or personal records.

Production was inspected through schema metadata and view DDL only. No application table rows were browsed or exported, and no production database or hosting settings were changed. cPanel remote database access / allow-list settings were not verified during this pass; use localhost in phpMyAdmin as the confirmed connection context, not as evidence of a remote hostname.

This is a point-in-time reference snapshot, not a migration. Keep production changes out of local development and review the schema before making migrations.

## Tables

| Object | Columns | Observed constraints / use |
| --- | --- | --- |
| `people` | `peopleid`, `email`, `name`, `password`, `parent` | All columns nullable; no key observed. The app uses `password` for password hashes and `parent` for parent authorization. |
| `chores` | `choreid`, `name`, `val` | All columns nullable; no key observed. `val` is the chore value. |
| `chores_done` | `cdid`, `peopleid`, `choreid`, `whendone`, `approved`, `val` | `cdid` is the only observed primary key and auto-increments. `whendone` defaults to the current timestamp. Other columns nullable. |
| `payments` | `paymentid`, `peopleid`, `amount`, `whendone` | All columns nullable; no key observed. No foreign keys or indexes were present in the export. The current application source does not reference this table. Its intended status is unknown. |

The four tables use InnoDB with `latin1` / `latin1_swedish_ci`. Amounts and values use `DOUBLE`; timestamps use `DATETIME`. No foreign-key constraints, triggers, routines, or events were present in the structure export. Only `chores_done.cdid` had an index (the primary key).

## Views and balance behavior

- `chores_history` joins `chores_done` to `people` by `peopleid` and `chores` by `choreid`. It presents chore, person, value, date, and approval fields. Because both are inner joins, a completion without a matching person or chore is omitted from this view.
- `people_money` groups `chores_history` by person and sums `val` only where `approved = 'Y'`.
- The app records a payout as a completed-chore row with `choreid = 0`, an approved value that is negative the current balance. Confirmed from current source. The schema does not establish whether this special row is intended to be present in `chores`.
- `payments` is not referenced in the current local source. It may be legacy or serve another purpose; confirm intent before removing or integrating it.

Do not change the view arithmetic or payout model until the intended behavior has been confirmed and checked with synthetic fixtures. The snapshot reflects current definitions, not a recommendation to retain every design choice.

## Local development

1. Create an empty local MariaDB database (MariaDB 11.4 is closest to the observed production version).
2. Import `database/schema.sql` into that database using a local DB account with table and view privileges. The two views use `SQL SECURITY DEFINER`; their effective definer will be the account that creates them. Do not use production credentials or a production connection.
3. Configure PHP with a private `CHORES_CONFIG_FILE` or environment variables as described in README. Keep local credentials outside the web root and Git.
4. Use synthetic-only records for manual or automated checks. Never copy production records into development.

The schema export is modified only to remove phpMyAdmin's temporary view-column placeholder tables and replace them with the included view definitions, which are the definitions from the same export. No constraints, columns, defaults, or view logic were intentionally changed. It does not issue `CREATE DATABASE` or `USE`, so select the local database before importing.

## Remote database access

The phpMyAdmin connection was local to the cPanel server over its UNIX socket. That confirms local database access only. Before considering an external development connection, obtain the provider's supported database hostname, port, TLS requirements, and source-IP allow-list rules, and verify the cPanel database user's privileges. Do not add an allow-list entry or change database grants without a separate explicit request. Prefer a local database for development.

## Application rules and confirmed behavior

- Accounts are looked up by email; login checks the stored value using PHP `password_verify()`. Password changes write `password_hash()` output back to `people.password`. Parent access is represented by `parent = 'Y'` in the session; parent-only approval and payout routes check this flag. No uniqueness constraint on email or key on `peopleid` was found in the database metadata.
- The chores page lists definitions with `choreid > 0`. Submitting a selected chore reads its current `val` and creates a `chores_done` row with the signed-in `peopleid`, that `choreid`, and `approved = 'N'`.
- The approval route changes a pending row to `Y` (approved) or `R` (rejected), and only updates rows currently marked `N`. Rejected and pending rows are omitted from the balance sum.
- `people_money.total` is the sum of values for approved `Y` rows visible through `chores_history`. It is not a separate stored balance.
- The current payout route reads that total and, after a parent confirms, inserts a `chores_done` row for the selected person with `choreid = 0`, `approved = 'Y'`, and `val = -total`. This makes a payout an offset in the same ledger and balance view.
- `history.php` queries family-wide balances and history without filtering by the signed-in person. `approvals.php` is parent-gated and lists all pending rows. This behavior is confirmed in source; whether all family accounts should see family-wide history is a product/privacy decision.

## Confirmed schema/source mismatches and open questions

- The application depends on `people.peopleid`, `chores.choreid`, and `chores_done.cdid`, but the production metadata showed a primary key only on `chores_done.cdid`. The lack of keys on the referenced ID fields means their uniqueness is not enforced by the schema. No foreign keys enforce the person/chore relationships.
- `people_money` counts the payout row only if `chores_history` can join it to a `chores` row with `choreid = 0`. The chores page excludes IDs at or below zero, and the export did not establish whether a zero-ID chore definition exists. Without it, payout entries are omitted by the view's inner join and therefore do not reduce the displayed balance. This is a confirmed dependency in the view and code; whether production contains the required row was deliberately not checked because that would require reading application records.
- `payments` has no keys and is unused by the local PHP source. Its intended purpose and whether it is legacy remain unknown.
- The schema permits NULL for identifiers and approval fields even though application flows expect usable IDs and the approval values `N`, `Y`, and `R`. Actual stored values and orphan rows were not inspected.
- No stored procedures, functions, triggers, or events appeared in the structure-only export. No indexes other than the `chores_done` primary key and no foreign-key constraints were exported.

## Clarifications for a later review

1. Should a payout remain an approved negative `chores_done` ledger entry, and is there an intentional `chores.choreid = 0` record to make it visible through the current inner-join view?
2. Is family-wide history and balance visibility intended for every signed-in member?
3. Is `payments` a legacy table, or should it represent a separate payment ledger?
4. Does the host offer an approved remote MySQL endpoint and a separate read-only database account? Those hosting facts were not available in the read-only inspection performed here.

## Existing PR #1

GitHub PR #1, `Harden Chores requests and modernize the UI`, is open and unmerged on `improve/security-ux`. Its current source includes the prepared-statement login and chore submission paths, parent-only approval/payout checks, approval state transitions, and the POST-confirmed negative-ledger payout flow described above. The PR description says full production parity and the complete database schema had not yet been verified; this discovery supplies the missing structure snapshot and view definitions. No PR state or source code was changed.
