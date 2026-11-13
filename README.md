# Chores

A lightweight PHP/MySQL family chores tracker. Family members submit completed chores; a parent reviews them, and approved chore values contribute to a balance that can be recorded as a payout.

## Features

- Email and password login, password change, and session-based authentication
- Chore submission and parent approval or rejection
- Pending and completed history and balance views
- Parent-recorded payouts
- Responsive, dependency-free PHP pages

## Architecture

This is a server-rendered PHP application. `login.php` is the unauthenticated entry point; `index.php`, `history.php`, `approvals.php`, `changepassword.php`, and `payout.php` are page routes. `submitchores.php` and `submitapproval.php` handle form posts. `global.php` loads database configuration and shared escaping/CSRF helpers. `header.php` and `footer.php` provide the shared layout. MySQL tables/views referenced by the code are `people`, `chores`, `chores_done`, `chores_history`, and `people_money`.

The source references columns including `people.peopleid/name/email/password/parent`, `chores.choreid/name/val`, and `chores_done.peopleid/choreid/approved/val`; history and balance pages consume the `chores_history` and `people_money` views. The complete DDL is not present in the source. This list is inferred from queries and must not be treated as a migration schema.

## Requirements

- PHP 8.0 or newer with `mysqli`, `mysqlnd` (for `get_result()`), and `intl` (for `NumberFormatter`)
- MySQL/MariaDB with the existing application schema and views
- Apache or another PHP-capable web server

The cPanel `php.ini` from the deployed directory is intentionally excluded because it contains host-specific settings. It enabled `display_errors`; production should instead log errors privately and keep display disabled.

## Local setup

1. Create a local database and load a schema obtained from a trusted source. No schema dump or production data is included here.
2. Copy `config.example.php` to a private location outside the document root and update the values. Configure `CHORES_CONFIG_FILE` to that file, or set `CHORES_DB_HOST`, `CHORES_DB_USER`, `CHORES_DB_PASSWORD`, and `CHORES_DB_NAME` in the PHP process environment.
3. Serve this directory with PHP and browse to `login.php`. For example, with PHP installed, run `php -S 127.0.0.1:8000` from this directory.

The example config is illustrative only. Never put live credentials in a tracked file or in the public document root. On cPanel, use the account's PHP environment-variable facility if available; otherwise store the returned PHP array in a directory outside `/public_html` and set `CHORES_CONFIG_FILE` using MultiPHP/hosting support. If the host cannot set an environment variable, use a private include path configured by the host, not a publicly reachable `config.php`.

## Testing

Run the source-level security regression checks with `python -m unittest discover -s tests -v`. Run PHP syntax checks on a machine with PHP CLI: `Get-ChildItem *.php | ForEach-Object { php -l $_.FullName }` (PowerShell), or `for f in *.php; do php -l "$f"; done` (POSIX shell). The current development machine did not have PHP CLI installed, and no database schema is supplied, so runtime/database workflows still require a local synthetic database setup. Do not use production personal data for tests.

## Production hosting and deployment

The known production URL is [chores.g5.co.nz](https://chores.g5.co.nz), hosted on the g5.co.nz cPanel environment. The original notes identify `ftp.g5.co.nz` and remote path `/chores/`. A non-authenticated network probe found SSH-2 on port 22, FTP on port 21, and no listener on port 990. Port 21 accepts `AUTH TLS`, but the FTPS TLS handshake failed certificate hostname validation for `ftp.g5.co.nz`; do not use FTPS until the host supplies a matching hostname/certificate. Port 22 speaks SSH-2 and may support SFTP, but the host key and SFTP subsystem have not been verified. No credentials were used and no deployment was performed.

Before deployment, ask the host to confirm SFTP and provide the correct SSH host key fingerprint, or repair the FTPS certificate mismatch. Configure WinSCP to verify the host key/certificate; do not disable verification or fall back to plain FTP. A future PowerShell deployment should use WinSCP scripting with a protected site/session configured in WinSCP. First download a backup of `/chores/` and record its checksum/listing. Upload reviewed PHP/CSS/JS and PWA source to a staging directory, preserve the live configuration, icons, favicon, runtime files and database, then switch using a host-supported rename/release-directory mechanism. Exclude `config.php`, `php.ini`, database content, user uploads, and private backups. Back up the database separately through the host's approved mechanism. Perform login, chore submission, approval, history and payout smoke checks, then retain the prior release until verified; rollback by switching back to it.

The hosting account's exact document root, safe configuration path, backup facility, SFTP/FTPS support, WinSCP site name, and deployable credentials remain to be confirmed. No saved password was read or extracted.

## Known limitations and review status

- No authoritative database DDL, local synthetic fixture, or automated integration test suite is available.
- The current pages use the database views as their source of truth; their definitions and payout arithmetic need verification against known intended behavior before changing any calculations.
- Several pages show family-wide history and balances to every logged-in account; confirm that this is the intended family privacy model.
- The initial source includes legacy CSS syntax and an unconditional JavaScript checkbox mutation; both need targeted correction.
- A read-only production check confirmed that the live login page registers a service worker and references a manifest and icon assets. The manifest and worker source have been recovered into this repository; the production `icons/` PNGs and `favicon.ico` are absent locally and must be preserved during deployment. Only the login page and public PWA assets were compared; application PHP parity remains unknown.
- `REVIEW.md` records the prioritized, source-reviewed findings and identifies fixes on the follow-up branch. Production parity has not been verified.
