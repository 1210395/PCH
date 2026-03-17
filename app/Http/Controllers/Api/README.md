# app/Http/Controllers/Api

API controllers for the Palestine Creative Hub. These controllers handle external integrations and are not protected by the designer session guard — they use cryptographic signature verification instead.

---

## Controller Index

| File | Route | Description |
|---|---|---|
| `WebhookController.php` | `POST /api/webhooks/tenders` | Receives tender data from the Jobs.ps job board via signed webhook and upserts it into the local `tenders` table. |

---

## WebhookController

Handles incoming webhook requests from Jobs.ps. The controller:

1. Rejects non-POST requests with a `405 Method Not Allowed` JSON response (supporting `GET`, `PUT`, `PATCH`, `DELETE` via a catch-all route).
2. Reads the raw request body and the `X-Signature` header.
3. Delegates signature verification to `WebhookSignatureService`, which uses OpenSSL RSA-SHA256 to validate the HMAC.
4. Parses the JSON payload and upserts each tender into the `tenders` table using `Tender::updateOrCreate()` keyed on the external reference ID.
5. Returns a `200 OK` JSON confirmation on success, or a `401 Unauthorized` / `422 Unprocessable Entity` on failure.

### Configuration
- Public key path: `JOBS_PS_PUBLIC_KEY_PATH` in `.env`
- Skip verification (dev only): `JOBS_PS_SKIP_VERIFICATION=true` in `.env`

---

## Key Patterns

### Stateless Authentication
The API routes do not use Laravel session middleware. Signature verification is the sole trust mechanism.

### Idempotent Upsert
Tenders use `updateOrCreate()` so replaying the same webhook payload is safe — duplicate entries are not created.

### Logging
All signature failures, payload parse errors, and successful imports are logged with context (payload length, error strings) to aid debugging.
