# Referral Network Operations

## Access and rollout

- Existing owner: Admin panel > **এজেন্ট ও রেফারেল**, or `/operations/referrals`.
- Agents use a separate account and login at `/agent/login`. They never receive an admin role.
- Apply normal database backups, `php artisan migrate --force`, and `npm ci && npm run build` through the existing deployment pipeline. No seeders create live agents, rates, patients or transactions.
- Test on staging first with fictional patients. Confirm production SMTP, HTTPS, backup retention and access controls before inviting real agents. Keep `APP_KEY` backed up securely: private fields are encrypted with it.
- An invitation is sent only when the owner presses the invitation button. Its one-use password link expires in 60 minutes. Deactivation, email changes and password resets invalidate old agent sessions; deactivation/email changes also remove outstanding reset tokens.

## Owner setup

1. Create an agent with identity, office, contact details and verified payout destination. Approve/activate after agreeing terms. There is no public agent registration.
2. Under **কমিশন ও চুক্তি**, enter the agent's default percentage, effective date and reason.
3. Add more specific rules for an agent with a hospital, treatment, or both. Enter hospital-side contract percentages and the exact eligible-services basis separately.
4. Send the password invitation. The agent's portal provides a referral link, downloadable QR, own cases, accepted rate, payment totals and statement.

## Commission rules

Agent commission is based only on the hospital commission actually received by AHC, never the patient's bill or an estimated treatment price.

Precedence (highest first):

1. Explicit percentage on this patient case.
2. Agent + hospital + treatment.
3. Agent + hospital.
4. Agent + treatment.
5. Agent default.

Hospital-specific rules win over treatment-only rules when both match. Within the same scope, the most recent effective version wins (ID breaks equal-date ties). Future rules do not apply yet. Disabling a rule falls back to the next matching active rule, including an older version. To end commission for a scope, create a **0%** version instead of disabling every rule. Missing rates stay unset, not silently zero.

Verification snapshots the applicable agent rate and hospital agreement. Hospital/treatment must be selected first. Later default/contract changes apply to newly verified cases, not existing ones. A separate future treatment or hospital visit is a new case. The owner can explicitly override an existing case's agent percentage with a reason; this increments its terms version, clears its previous acceptance, and requires fresh approval of any remaining amount. A reduction below money already paid is rejected.

Example: AHC receives BDT 10,000 hospital commission. An agent's cardiac case at 20% earns BDT 2,000; their fertility case at 30% earns BDT 3,000 on the same received amount. The hospital contract rate is not multiplied into the already-received commission again.

## Patient workflow

1. Patient uses referral link, agent submits with permission, or owner records a referral manually. Each receives a random reference, not a sequential patient identifier.
2. Staff confirm patient/authorized guardian contact permission and attribution. A form checkbox alone is not verification.
3. Matching normalized phone numbers are flagged. The owner distinguishes a different family member, the same patient's new treatment, or a duplicate of an existing case. No automatic merging or payment for duplicates.
4. Select hospital/treatment and verify to freeze the case context and terms. Information-sharing consent is recorded separately before hospital coordination/appointment/completion.
5. Staff can update only assigned cases. Only the owner can assign/reassign, resolve duplicate identity, manage agents/rules, or access hospital receipts and finance actions.
6. Agent accepts the case rate in the portal. Alternatively, the owner records evidence/reference/date of an actual offline acceptance. Editing a rate is not itself consent.

Agents see only their own limited case identity/status and commission/payment information, not diagnosis notes, reports, hospital contracts or bank-credit details. Since both a rate and an earned amount are visible, an agent could mathematically infer the underlying received amount; this is not a promise of absolute revenue secrecy.

## Finance and corrections

- Record only verified hospital credits, including transaction reference, original currency/amount, actual BDT settlement amount and conversion/reconciliation note. No automatic exchange-rate conversion or cross-currency sum is performed.
- Agent entitlement is cumulative: round `(net hospital receipts in paisa * rate in basis points) / 10000` half-up once per case. This avoids per-installment rounding drift. Percentages support 0.01% precision; money supports 0.01 BDT.
- Eligibility requires verified, completed case and accepted current terms. Owner approval is separate. Additional receipts do not automatically approve additional agent money.
- Record actual agent transfers only up to the approved unpaid balance. A verified payout destination is required and its value is snapshotted with each payment. This module records transfers; it does not send money through a bank or wallet.
- Submission/operation keys protect repeat submissions, and repeated references within a case/type are blocked. One bulk bank transaction spanning multiple cases must be allocated accurately across those cases; this release does not import/reconcile bank statements.
- Financial entries and audit rows cannot be edited/deleted through models or UI. Correct an entry through a full reversal with a reason, then record the corrected transaction with a distinct adjustment reference. Reverse entries cannot themselves be reversed; every original can be reversed once.
- A hospital receipt reversal can create an overpaid balance. It is flagged for recovery/settlement and blocks further payout until reconciled. Do not erase a legitimate past payment to hide it.
- There are no invoices, tax calculations, automatic withdrawals, WhatsApp API messaging or medical-document uploads in this release. Hospital contract compliance, accepted agent terms, permissions, supporting evidence and local financial obligations remain operational responsibilities.

## Verification

`php artisan test --filter=ReferralNetworkTest` covers rule precedence, frozen terms, per-case changes, rounding, receipts, payouts, reversals, idempotency, consent, identity checks, role isolation, all new views and password lifecycle. Run the full suite plus `npm run build` before release. A separate fictional SQLite preview can be used for browser QA; never deploy preview credentials or fixtures.

## Release safety

- Laravel 12 and patched Vite dependencies replace versions with known advisories. CI runs Composer and npm security audits before deployment, plus migration/rollback checks on a disposable MySQL database.
- Production HTTP redirects to the configured HTTPS `APP_URL` before sessions start. HSTS is scoped to this host, not mail/subdomains. Referral pages deny framing, disable caching/indexing, and escape user content. Password emails use the configured website URL rather than an untrusted request host.
- `scripts/deployment-preflight.php` refuses migrations unless production/debug-off/HTTPS/secure-cookie settings are correct. It uses the configured MySQL/MariaDB account to make a consistent dump under `storage/app/private/deploy-backups`, outside the public document root. Temporary credential files are removed, backups are owner-readable only, and no database content is printed to CI logs.
- Keep a separate encrypted off-server backup and the existing `APP_KEY`; this on-server recovery copy does not protect against server loss. Review backup retention/disk space regularly. Backups are not deleted automatically.
- Rollback of an established live referral database would delete referral history. For an application regression, redeploy a known-good compatible code revision without running `migrate:rollback`; investigate and fix forward. Restore a database backup only with a reviewed recovery plan that accounts for changes since the backup.
