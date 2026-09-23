# bindrr

Personal spaces for a person and the agents they work with. A space is a private folder of files and notes. The human UI and the agent HTTP API read and write the same objects in S3.

This is a monorepo. `apps/web` is the Laravel app and the preview root. `packages/` is reserved for shared packages later.

Out of scope for this iteration: auth, invites, team roles, billing, file versioning, and the bindrr MCP server.

## Storage

Spaces live on the `s3` filesystem disk (`BINDRR_DISK=s3`). Dogfood bucket:

- Bucket: `artgrafii-bindrr-156777722327`
- Region: `ap-northeast-2`

Each space is a prefix, `spaces/{slug}/`. A `.space.json` marker holds the display name. Uploaded files and agent puts are objects next to that marker. The marker is not listed or downloaded as a file.

Leave `AWS_ACCESS_KEY_ID` and `AWS_SECRET_ACCESS_KEY` empty to use the default AWS credential chain (an instance role or task role). Set them when you are using local keys.

`FILESYSTEM_DISK=s3` makes that disk the framework default. Application code uses `BINDRR_DISK`, which defaults to `s3`.

PHP `upload_max_filesize` and `post_max_size` must be at least 10M. The app rejects larger uploads and puts (`BINDRR_MAX_UPLOAD_KB`, default `10240`).

## Environment

Copy `apps/web/.env.example` to `apps/web/.env` and set:

```dotenv
APP_NAME=bindrr
APP_KEY=base64:...
FILESYSTEM_DISK=s3
BINDRR_DISK=s3
BINDRR_MAX_UPLOAD_KB=10240
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-northeast-2
AWS_BUCKET=artgrafii-bindrr-156777722327
AWS_USE_PATH_STYLE_ENDPOINT=false
```

Optional: `AWS_URL`, `AWS_ENDPOINT`.

Generate a key with `php artisan key:generate` from `apps/web`.

## Run

```bash
cd apps/web
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan migrate
php artisan serve
```

Tests fake the s3 disk and do not call AWS:

```bash
cd apps/web
php artisan test
```

## Agent HTTP API

No auth in this iteration. The same S3 prefix backs the UI.

| Method | Path | Result |
| --- | --- | --- |
| `GET` | `/api/spaces` | List spaces |
| `GET` | `/api/spaces/{slug}/files` | List files |
| `GET` | `/api/spaces/{slug}/files/{name}` | File bytes |
| `PUT` | `/api/spaces/{slug}/files/{name}` | Create or replace a file from the raw body |

`PUT` returns `201` when the file is new and `200` when it replaces an existing file. A missing space is `404`. A reserved or unsafe file name is `422`. A body over the upload limit is `413`.
