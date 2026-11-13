# Chores

A lightweight PHP/MySQL family chores tracker. Family members submit completed chores; a parent reviews them, and approved chore values contribute to a balance that can be recorded as a payout.

## Features

- Email and password login, password change, and session-based authentication
- Chore submission and parent approval or rejection
- Pending and completed history and balance views
- Parent-recorded payouts
- Mobile-first app navigation with parent-only approvals
- Responsive, dependency-free PHP pages with touch-sized controls and desktop ledger tables
- Installable PWA metadata, root-resolved icons, safe-area support, and versioned static-only caching

## Architecture

This is a server-rendered PHP application. `login.php` is the unauthenticated entry point; `index.php`, `history.php`, `approvals.php`, `changepassword.php`, and `payout.php` are page routes. `submitchores.php` and `submitapproval.php` handle form posts. `global.php` loads database configuration and shared escaping/CSRF helpers. `header.php` and `footer.php` provide the shared layout. The verified point-in-time database structure is documented in [DATABASE.md](DATABASE.md) and provided as a structure-only local snapshot in [database/schema.sql](database/schema.sql). It includes four base tables (`people`, `chores`, `chores_done`, `payments`) and two views (`chores_history`, `people_money`). Treat the snapshot as a development reference, not a migration plan.

## Requirements

- PHP 8.0 or newer with `mysqli`, `mysqlnd` (for `get_result()`), and `intl` (for `NumberFormatter`)
- MySQL/MariaDB with the existing application schema and views
- Apache or another PHP-capable web server

The cPanel `php.ini` from the deployed directory is intentionally excluded because it contains host-specific settings. It enabled `display_errors`; production should instead log errors privately and keep display disabled.

## Local setup

1. Create an empty local MariaDB database (11.4 is closest to the discovered production version), then import `database/schema.sql`. See [DATABASE.md](DATABASE.md) for verified structure, view behavior, and limitations. The schema contains no production rows or credentials.
2. Copy `config.example.php` to a private location outside the document root and update the values. Configure `CHORES_CONFIG_FILE` to that file, or set `CHORES_DB_HOST`, `CHORES_DB_USER`, `CHORES_DB_PASSWORD`, and `CHORES_DB_NAME` in the PHP process environment.
3. Serve this directory with PHP and browse to `login.php`. For example, with PHP installed, run `php -S 127.0.0.1:8000` from this directory.

The example config is illustrative only. Never put live credentials in a tracked file or in the public document root. On cPanel, use the account's PHP environment-variable facility if available; otherwise store the returned PHP array in a directory outside `/public_html` and set `CHORES_CONFIG_FILE` using MultiPHP/hosting support. If the host cannot set an environment variable, use a private include path configured by the host, not a publicly reachable `config.php`.

## Testing

Run the source-level security regression checks with `python -m unittest discover -s tests -v`. Run PHP syntax checks with `Get-ChildItem *.php | ForEach-Object { php -l $_.FullName }` (PowerShell), or `for f in *.php; do php -l "$f"; done` (POSIX shell). The refresh was also exercised against a disposable local MariaDB instance seeded with synthetic parent/child accounts and chore rows. Parent and child navigation/route permissions, chore selection, approvals, balances/history, payout confirmation, and public PWA assets were checked locally. No production records or endpoints were used for validation. Do not use production personal data for tests.

## Production hosting and deployment

The known production URL is [chores.g5.co.nz](https://chores.g5.co.nz), hosted on the g5.co.nz cPanel environment. The original notes identify `ftp.g5.co.nz` and remote path `/chores/`. A non-authenticated network probe found SSH-2 on port 22, FTP on port 21, and no listener on port 990. Port 21 accepts `AUTH TLS`, but the FTPS TLS handshake failed certificate hostname validation for `ftp.g5.co.nz`; do not use FTPS until the host supplies a matching hostname/certificate. Port 22 speaks SSH-2 and may support SFTP, but the host key and SFTP subsystem have not been verified. No credentials were used and no deployment was performed.

Before deployment, ask the host to confirm SFTP and provide the correct SSH host key fingerprint, or repair the FTPS certificate mismatch. Configure WinSCP to verify the host key/certificate; do not disable verification or fall back to plain FTP. A future PowerShell deployment should use WinSCP scripting with a protected site/session configured in WinSCP. First download a backup of `/chores/` and record its checksum/listing. Upload reviewed PHP/CSS/JS and PWA source to a staging directory, preserve the live configuration, icons, favicon, runtime files and database, then switch using a host-supported rename/release-directory mechanism. Exclude `config.php`, `php.ini`, database content, user uploads, and private backups. Back up the database separately through the host's approved mechanism. Perform login, chore submission, approval, history and payout smoke checks, then retain the prior release until verified; rollback by switching back to it.

The hosting account's exact document root, safe configuration path, backup facility, SFTP/FTPS support, WinSCP site name, and deployable credentials remain to be confirmed. No saved password was read or extracted.

## Known limitations and review status

- A structure-only production schema snapshot is available at `database/schema.sql`; it was imported successfully into a disposable local MariaDB 11.4 instance. Synthetic browser records were created only in that temporary test database and are not included in the repository.
- The discovered view definitions and current payout model are documented in `DATABASE.md`. Their intended business behavior still needs confirmation before changing calculations.
- Several pages show family-wide history and balances to every logged-in account; confirm that this is the intended family privacy model.
- The initial source includes legacy CSS syntax and an unconditional JavaScript checkbox mutation; both need targeted correction.
- Local PWA icons and favicon are included under `assets/icons/` and the repository root. The service worker caches only versioned static assets; it does not cache authenticated HTML or route responses.
- `REVIEW.md` records the prioritized, source-reviewed findings and identifies fixes on the follow-up branch. Production parity has not been verified.
