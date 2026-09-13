# Schema headers implementation plan

**Goal:** Implement the approved reusable header package and prove it independently of Pressiu.
**Architecture:** Filament panel plugin + immutable options; opt-in schema trait; thin layout/heading components; namespaced Blade/CSS; a lazy Alpine controller with one sticky DOM instance.
**Tech stack:** PHP 8.3+, Filament 5.7+, Laravel 12/13, Pest/Testbench, Node and Playwright.
**Spec:** specification.md.

## Execution
1. Configuration: write tests/Unit/HeaderOptionsTest.php first. Run Pest and observe failure without HeaderOptions. Implement Enums/HeaderMode.php and HeaderOptions.php until immutable defaults, validation and responsive ordering pass.
2. Rendering: add Testbench fixtures and tests/Feature/HeaderRenderingTest.php. Assert native fallback, null record, shared schema, text escaping, correct h1 and four render hooks before adding PageHeaderPlugin, PageHeaderServiceProvider, Concerns/HasPageHeader and Components/HeaderLayout/Heading. Run focused tests then all PHP tests.
3. Interaction: write native schema/header action tests and the browser acceptance tests before implementing controller. Test one invocation, confirmation modal, preserved form state and visibility. Use actual Filament pages, not HTML mocks.
4. Scroll: write Node tests for viewport mode/geometry and Playwright tests for normal/sticky/compact. Implement resources/js/page-header.js and namespaced CSS. Keep expanded layout footprint stable; do not duplicate DOM. Test return to top, responsive modes, focus, SPA and listener cleanup.
5. Delivery: finish independent workbench, installation/local symlink documentation, license, style checks and CI matrix. Run PHP/JS/browser checks; inspect screenshots and changed files. Record actual results and remaining risks in docs/verification.md. Open a pull request; no merge, release or Pressiu changes.
