# Filament Page Header documentation site

The public documentation site is built with Astro and Starlight.

## Local development

Astro requires a supported Node.js version.

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

The workflow uploads the generated static site as a GitHub Actions artifact.

Production deployment is handled by GitHub Actions using environment secrets and repository variables configured outside the repository. Server addresses, SSH ports, credentials, host paths, and other infrastructure-specific values must not be committed to the repository.
