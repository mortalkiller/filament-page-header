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

Configure these repository secrets:

```text
DOCS_HOST
DOCS_USER
DOCS_SSH_PRIVATE_KEY
DOCS_SSH_KNOWN_HOSTS
```

Configure this repository variable:

```text
DOCS_REMOTE_PATH
```

For the intended server layout, use:

```text
/var/www/docs.pedromonteiro.dev/filament-page-header
```

After a successful build on `2.x`, the deploy job downloads the exact build artifact and syncs it to the configured directory using rsync over SSH.

The Nginx virtual host should serve `/var/www/docs.pedromonteiro.dev` as its root and route `/filament-page-header/` to the corresponding static directory.
