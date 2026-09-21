# Filament Page Header documentation site

The public documentation site is built with Astro and Starlight.

Production URL:

```text
https://docs.pedromonteiro.dev/filament-page-header/
```

## Local development

Astro 7 requires Node.js 22.12.0 or newer.

```bash
cd docs-site
npm install
npm run dev
```

Build the static site with:

```bash
npm run build
```

The generated files are written to `docs-site/dist/`. Do not commit that directory.

## Continuous integration

`.github/workflows/docs.yml` builds the documentation for pull requests and changes pushed to `2.x`.

The workflow uploads the generated `dist/` directory as a GitHub Actions artifact.

## Server deployment

Automatic deployment is disabled until the repository variable below is set:

```text
DOCS_DEPLOY_ENABLED=true
```

The deploy job uses the `docs-production` GitHub environment. Prefer environment-scoped secrets for production credentials, although repository secrets with the same names also work.

Configure these secrets:

```text
DOCS_HOST
DOCS_USER
DOCS_SSH_PRIVATE_KEY
DOCS_SSH_KNOWN_HOSTS
```

Configure these variables:

```text
DOCS_DEPLOY_ENABLED
DOCS_PORT
DOCS_REMOTE_PATH
```

For the current OVH server:

```text
DOCS_HOST=51.210.254.250
DOCS_USER=github-docs
DOCS_PORT=1096
DOCS_REMOTE_PATH=/opt/webserver/docs.pedromonteiro.dev/filament-page-header
```

The `DOCS_SSH_KNOWN_HOSTS` value must be generated for the configured SSH port, for example:

```bash
ssh-keyscan -p 1096 -t ed25519 51.210.254.250
```

After a successful build on `2.x`, the deploy job downloads the exact build artifact and syncs it to the host directory using rsync over SSH.

The Nginx container mounts `/opt/webserver` from the host at `/var/www`, so the host deployment directory is visible inside Nginx as:

```text
/var/www/docs.pedromonteiro.dev/filament-page-header
```
