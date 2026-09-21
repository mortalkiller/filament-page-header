import { defineConfig } from 'astro/config';
import starlight from '@astrojs/starlight';

const repositoryUrl = 'https://github.com/mortalkiller/filament-page-header';

export default defineConfig({
  site: 'https://docs.pedromonteiro.dev',
  base: '/filament-page-header',
  trailingSlash: 'always',
  integrations: [
    starlight({
      title: 'Filament Page Header',
      description: 'Responsive, native-first page headers for Filament 4 and 5.',
      favicon: '/filament-page-header/favicon.png',
      logo: {
        src: './src/assets/PM-02.png',
        alt: 'Pedro Monteiro',
      },
      social: [
        {
          icon: 'github',
          label: 'GitHub',
          href: repositoryUrl,
        },
      ],
      editLink: {
        baseUrl: `${repositoryUrl}/edit/2.x/docs-site/src/content/docs/`,
      },
      customCss: ['./src/styles/custom.css'],
      sidebar: [
        {
          label: 'Getting Started',
          items: [
            'getting-started/installation',
            'getting-started/compatibility',
          ],
        },
        {
          label: 'Guides',
          items: [
            'guides/configuration',
            'guides/generator',
            'guides/migration',
          ],
        },
        {
          label: 'API Reference',
          items: [
            'api',
            'api/page-header-plugin',
            'api/header',
            'api/metadata-entry',
            'api/heading-subheading',
            'api/compact-header',
            'api/has-page-header',
            'api/header-options',
            'api/enums',
          ],
        },
        {
          label: 'Development',
          items: [
            'development/local-development',
            'development/testing',
          ],
        },
        {
          label: 'Project',
          items: [
            'project/roadmap',
            'project/security',
          ],
        },
      ],
    }),
  ],
});
