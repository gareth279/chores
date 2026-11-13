# Project guidance

- Preserve the existing PHP/MySQL application and its production data model. Do not introduce a framework or database migration without a verified schema and an explicit compatibility plan.
- Never commit database credentials, private configuration, real user data, dumps, FTP credentials, or backups. Keep secrets outside the document root and out of Git history.
- Do not deploy or alter production unless the user explicitly requests it. For deployment work, preserve server configuration and persistent data, make a backup, and provide a rollback path.
- Use prepared statements for SQL, validate identifiers and form values server-side, escape dynamic HTML with `h()`, and protect state-changing POST routes with CSRF tokens.
- Check authorization in every endpoint, including POST handlers. Keep parent-only operations parent-only.
- Do not change payout/balance calculations until the intended behavior is established and covered by synthetic-data tests.
- Keep the app lightweight and server-rendered. Maintain responsive layouts and avoid unnecessary dependencies.
- Run PHP syntax checks and relevant local tests when PHP tooling is available. Never use production accounts or data as test fixtures.

## Architecture snapshot

See `README.md` for route structure and schema inferred from source. No authoritative schema DDL is in this repository. `REVIEW.md` lists findings and recommended work in priority order.
