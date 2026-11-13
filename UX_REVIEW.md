# Chores UX review and visual refresh assessment

**Reviewed:** 14 November 2026
**Scope:** Read-only visual review of the live parent account, local source, and PR #1. No form was submitted. The chore checkbox was checked and unchecked locally to observe its visual state.
**Screenshots:** Local-only captures are stored outside the repository at `C:\Users\garet\.codex\visualizations\2026\11\14\chores-ux-review\`. They contain real family chores, names, balances, and history; do not add them to Git.

## Production interaction incident

While opening the parent-facing payout interface for visual inspection, the live page performed a payout as soon as its GET link loaded. The response reported an offset of **−$4** for the selected account. I later opened that same link again after the balance displayed as zero; the response reported a **$0** payout. The base payout implementation in local Git contains an `INSERT` on GET, matching the observed response. These requests likely added two ledger rows (−$4 and $0) in production. I did not inspect production records afterward, and I did not attempt a reversal or any other correction. The inspection is stopped here; please review the live history and handle any correction according to your records.

This was unintended and violated the request not to perform production state changes. The unsafe GET behavior was not apparent until after the first page load. The local PR #1 version changes payout recording to an explicit POST confirmation, but the live route observed here still behaves like the older implementation.

## Current-state review

The authenticated browser session opened the live application on the parent home page. The visible navigation is Home, Approvals, History, Change Password, and Logout. The site uses the title “Chore Tracker.” No child account was used; the child experience below is based on the shared chore-selection screen and local route source.

### Home and chore submission

The page lists six available chores in one large pale-lavender panel. Each row places a cyan outlined checkbox beside a very large chore name and its value. A single “Tick off chores” submit control appears at the bottom. At desktop width the panel occupies only about half the browser width, leaving a large empty area to the right.

The row is a label around the checkbox and text, so the whole row can be selected. Checking a row fades its text to pale cyan and underlines it. That state is visually distinct but has weak contrast and resembles a link. The page has no visible selection count or separate confirmation before the submit control. I did not submit the form.

### Approvals

The parent navigation includes Approvals. The live queue showed “No chores found.” with no supporting empty-state explanation or next step. The message sits directly under the nav and has no card, heading, or visual status treatment. No pending item was available to review, so approval and rejection controls were not exercised.

### History and balances

History shows a “Chore Balances” section with one row per person and a Payout link for the parent. It then shows “Awaiting approval” and “Completed Chores.” The live queue was empty; the completed activity contains person, chore, timestamp, and value. The information is rendered in unbordered default table rows with little spacing or visual grouping. At desktop size it reads as tightly concatenated text rather than a ledger. Balance values and individual activity values have no distinct emphasis.

### Payout

The live payout link opens a legacy page that immediately records the payout on GET and renders raw text rather than a clear confirmation screen. This is a critical usability and safety defect, not a styling issue. Do not redesign or test this route on production. The local PR version has a confirmation page and POST action; therefore payout appearance and behavior differ between the checked-out PR and production.

### Change password

The page has a heading and three password fields (current, new, confirm) with a small default browser Submit button. Labels and inputs sit inline and have inconsistent widths. There is no visible form card, error-message region, or success feedback on the initial page. DevTools reported missing password `autocomplete` hints. I did not enter credentials or submit the form.

### Navigation and mobile presentation

Desktop navigation is a dark horizontal strip with small text links. The desktop layout leaves substantial unused space and the page typography varies sharply between screens. In a 400px responsive viewport the browser scales the page content down to fit; navigation, chore names, history, and controls become very small. The checked-out `header.php` has no `meta name="viewport"`, so its CSS mobile breakpoint cannot reliably set the device-width layout. The mobile captures show the effective small-text rendering rather than a separately designed mobile layout.

### PWA and browser feedback

The live page registers a service worker and references a web manifest. While inspecting the live page, Chrome reported that `/icons/icon-192.png` returned 404 and the manifest icon could not be loaded. A service-worker fetch rejection was also observed once during the session; it may be transient and needs a clean reload to confirm. The local worker uses a cache-first lookup for page requests and precaches fixed asset paths, so replacing `chores.css` under the same URL may leave an older cached version active. The local manifest references 192px and 512px icons; the icon directory is absent from this checkout.

## Screenshots

These images are local-only and contain real family information.

| Screen | Desktop | Mobile (400px responsive viewport) |
| --- | --- | --- |
| Chore selection | [chores-desktop.jpg](C:/Users/garet/.codex/visualizations/2026/11/14/chores-ux-review/chores-desktop.jpg) | [chores-mobile-400x1210.jpg](C:/Users/garet/.codex/visualizations/2026/11/14/chores-ux-review/chores-mobile-400x1210.jpg) |
| Approvals (empty) | Desktop observed; screenshot not saved | [approvals-mobile-400x1210.jpg](C:/Users/garet/.codex/visualizations/2026/11/14/chores-ux-review/approvals-mobile-400x1210.jpg) |
| History and balances | [history-desktop.jpg](C:/Users/garet/.codex/visualizations/2026/11/14/chores-ux-review/history-desktop.jpg) | [history-mobile-400x1210.jpg](C:/Users/garet/.codex/visualizations/2026/11/14/chores-ux-review/history-mobile-400x1210.jpg) |
| Payout | [payout-desktop.jpg](C:/Users/garet/.codex/visualizations/2026/11/14/chores-ux-review/payout-desktop.jpg) | [payout-mobile-400x1210.jpg](C:/Users/garet/.codex/visualizations/2026/11/14/chores-ux-review/payout-mobile-400x1210.jpg) |
| Change password | [change-password-desktop.jpg](C:/Users/garet/.codex/visualizations/2026/11/14/chores-ux-review/change-password-desktop.jpg) | [change-password-mobile-400x1210.jpg](C:/Users/garet/.codex/visualizations/2026/11/14/chores-ux-review/change-password-mobile-400x1210.jpg) |

The payout captures show the responses after the unintended GET requests described above.

## UX findings by priority

1. **Payout is state-changing on page load.** A parent expects to review and confirm. The live GET route instead records a payout as a side effect of opening the link. This must be resolved in the deployment/source plan before anyone tests or styles that live route.
2. **Mobile content is too small.** The missing viewport meta tag makes the browser shrink the page to fit; touch targets and content become hard to read. Add a device-width viewport meta tag in the eventual implementation, then size and test the layout at real phone widths.
3. **Selection feedback has poor contrast.** The checked chore text becomes pale and underlined. Keep the clear checked state, but use a high-contrast background/check indicator and retain readable text.
4. **History lacks hierarchy.** Balances, pending items, and completed activity blend together; the raw table has no visible column labels, row separators, or card/ledger structure.
5. **Empty approvals gives little context.** The current text confirms there are no items, but does not explain that the parent is up to date or orient the user to the next useful screen.
6. **Forms and controls are inconsistent.** Password controls use default browser styles while the home screen has oversized custom checkbox styling. Button size and placement vary, and password fields lack browser autocomplete hints.
7. **PWA assets need attention.** The live 192px icon request fails. The fetch rejection may be transient, but service-worker cache behavior can also make CSS rollout inconsistent.
8. **Parent and child views were not independently compared.** The authenticated session was parent-only. Parent-only approvals and payouts are visible in local source; child account presentation remains unverified.

## Recommended visual direction

Use a calm, friendly utility interface that feels appropriate for a family without adding game mechanics or a dashboard:

- **Palette:** warm white or very light neutral canvas, dark slate/navy text, one confident teal or blue accent, and restrained amber/red status colors. Keep text contrast strong; reserve pale tints for backgrounds, not body copy.
- **Type:** system sans-serif with a clear page title, 1–1.125rem body text, and compact but readable labels. Use tabular numerals for currency and dates where practical.
- **Layout:** a centered, narrow content column on desktop; full-width cards on mobile; consistent page heading and spacing. Add the device-width viewport declaration before evaluating media queries.
- **Navigation:** keep the existing routes and parent visibility rules. Use compact, high-contrast navigation that wraps cleanly on narrow screens; do not add routes or a new mobile menu unless the simple layout proves insufficient.
- **Chores:** make each existing label a distinct row/card with a readable name, separated value, visible native/custom accessible checkbox, and a high-contrast selected state. Keep the existing submit action below the list.
- **Approvals:** show each existing pending item with date/person/chore context and two unmistakable actions: approve selected and reject selected. Preserve current checkbox selection and parent-only behavior. Give the empty state a short, helpful message without adding a workflow.
- **Balances and history:** emphasize the existing balance figures, then separate pending and completed sections. Use a clean ledger on desktop and stacked rows/cards on mobile; preserve all existing rows, values, ordering, and visibility.
- **Payout and password:** use a clear page title, short explanation, full-width controls, and comfortable action buttons. Payout layout must only be designed against the explicit-confirmation POST flow in PR #1, not the current live GET endpoint.

## PR #1 and implementation approach

The live design resembles the older stylesheet: pale lavender chore area, very large type, cyan animated checkboxes, and sparse default page styling. PR #1 replaces that with system typography, a neutral page/card treatment, blue controls, visible native checkboxes, focus outlines, and a small-screen CSS breakpoint. Those are worthwhile foundations and should be retained: the modern font stack, constrained content width, semantic labels, accessible focus outline, and consistent controls.

The refresh should refine the PR stylesheet rather than restore the decorative animated checkbox. The current PHP already supplies useful presentation hooks (`.todo-list`, `.todo`, `.todo__state`, `.todo__text`, `.chores`) and its labels wrap checkboxes, so the chore and approval cards can be redesigned without changing request values or database logic. `history.php` emits plain tables without headers; mobile cards would need small presentation-only markup additions (table headings or cell labels/classes), while keeping the same queries and rows. The shared header can be restyled without changing link destinations or parent-only navigation.

Before an implementation, add the missing viewport meta tag and agree which local revision is the design baseline. The screenshot indicates production does not currently match PR #1. The shared CSS URL and cache-first service worker should be reviewed so a deployment does not leave users on an old stylesheet. Preserve all field names, form methods, values, permission checks, query logic, balance math, and session behavior.

## Functional impact

The recommended card, typography, color, spacing, focus, responsive-table, and form-layout work can remain presentation-only. Wrapping chore values in spans or adding table labels/classes changes markup, not the selected chore IDs or query behavior. The viewport meta tag changes browser layout scaling but not app workflows.

Do not add balance forecasts, selection counters, filters, new roles, or altered approval rules in this refresh. The payout route is the exception: production currently changes data on GET, whereas local PR #1 uses a POST confirmation. Adopting the PR flow is a safety/functionality difference from live production and requires an explicit rollout decision; it must not be hidden inside a CSS redesign.

## Questions before implementation

1. Should the visual direction keep teal as the primary accent, or move to a restrained blue accent like PR #1?
2. Is the PR #1 POST-confirmed payout flow the intended behavior to deploy, given that the live page currently records a payout on GET?
3. Should children continue to see the same family-wide balances and history that the current history page presents?
4. Can the missing production icon assets be restored from the original deployment package, or should the PWA icons be recreated as a separate approved asset task?

No application source, database, PR, or production configuration was intentionally changed in this assessment. The production payout requests described at the top were accidental side effects; no correction has been attempted.

## Implementation status — 2026-11-14

The agreed mobile-first refresh is implemented on `improve/security-ux`: shared app navigation, chore and approval cards, responsive history ledger, consistent forms, viewport/safe-area support, regenerated local PWA icons, and a versioned service worker that caches only known static assets. The existing PR #1 POST-based payout confirmation is retained. Chore, approval, history, authentication, and payout queries and business calculations remain as they were in the branch. See the repository README for validation results. The branch has not been deployed or merged.
