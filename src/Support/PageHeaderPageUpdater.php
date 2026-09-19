<?php

declare(strict_types=1);

namespace MortalKiller\FilamentPageHeader\Support;

use Illuminate\Filesystem\Filesystem;
use MortalKiller\FilamentPageHeader\Concerns\HasPageHeader;
use PhpToken;
use ReflectionClass;
use RuntimeException;

final class PageHeaderPageUpdater
{
    public function __construct(
        private readonly Filesystem $filesystem,
    ) {}

    /**
     * @param  class-string  $pageClass
     */
    public function addTrait(string $pageClass): bool
    {
        $reflection = new ReflectionClass($pageClass);
        $path = $reflection->getFileName();

        if (! is_string($path) || (! $this->filesystem->isFile($path))) {
            throw new RuntimeException("Unable to locate the source file for page [{$pageClass}].");
        }

        $realPath = realpath($path);
        $vendorPath = realpath(base_path('vendor'));

        if (
            is_string($realPath)
            && is_string($vendorPath)
            && str_starts_with($realPath, $vendorPath.DIRECTORY_SEPARATOR)
        ) {
            throw new RuntimeException("Refusing to modify vendor page [{$pageClass}].");
        }

        if (! is_writable($path)) {
            throw new RuntimeException("Page source file [{$path}] is not writable.");
        }

        $source = $this->filesystem->get($path);
        $shortName = $reflection->getShortName();
        [$classOffset, $openBraceOffset, $closeBraceOffset] = $this->locateClass($source, $shortName);

        $imports = $this->getNamespaceImports($source, $classOffset);
        $ourImport = $this->findOurImport($imports);
        $traitReference = $ourImport ?? 'HasPageHeader';

        if ($this->classUsesTrait($source, $openBraceOffset, $closeBraceOffset, $traitReference)) {
            return false;
        }

        if (
            ($ourImport === null)
            && $this->classUsesTrait($source, $openBraceOffset, $closeBraceOffset, '\\'.HasPageHeader::class)
        ) {
            return false;
        }

        $hasShortNameConflict = ($ourImport === null) && $this->hasShortNameConflict($imports);

        if ($hasShortNameConflict) {
            $traitReference = '\\'.HasPageHeader::class;
        }

        $source = substr($source, 0, $openBraceOffset + 1)
            ."\n    use {$traitReference};\n"
            .substr($source, $openBraceOffset + 1);

        if (($ourImport === null) && (! $hasShortNameConflict)) {
            $source = $this->insertImport($source, HasPageHeader::class, $shortName);
        }

        $this->filesystem->put($path, $source);

        return true;
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function locateClass(string $source, string $shortName): array
    {
        $tokens = PhpToken::tokenize($source);

        foreach ($tokens as $index => $token) {
            if ($token->id !== T_CLASS) {
                continue;
            }

            $nameIndex = $this->nextSignificantTokenIndex($tokens, $index + 1);

            if (($nameIndex === null) || ($tokens[$nameIndex]->id !== T_STRING) || ($tokens[$nameIndex]->text !== $shortName)) {
                continue;
            }

            $openBraceIndex = $this->findTokenText($tokens, '{', $nameIndex + 1);

            if ($openBraceIndex === null) {
                break;
            }

            $depth = 1;

            for ($i = $openBraceIndex + 1; $i < count($tokens); $i++) {
                if ($tokens[$i]->text === '{') {
                    $depth++;
                } elseif ($tokens[$i]->text === '}') {
                    $depth--;

                    if ($depth === 0) {
                        return [$token->pos, $tokens[$openBraceIndex]->pos, $tokens[$i]->pos];
                    }
                }
            }
        }

        throw new RuntimeException("Unable to locate class [{$shortName}] in its source file.");
    }

    /**
     * @param  list<PhpToken>  $tokens
     */
    private function nextSignificantTokenIndex(array $tokens, int $start): ?int
    {
        for ($i = $start; $i < count($tokens); $i++) {
            if (in_array($tokens[$i]->id, [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            return $i;
        }

        return null;
    }

    /**
     * @param  list<PhpToken>  $tokens
     */
    private function findTokenText(array $tokens, string $text, int $start): ?int
    {
        for ($i = $start; $i < count($tokens); $i++) {
            if ($tokens[$i]->text === $text) {
                return $i;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function getNamespaceImports(string $source, int $classOffset): array
    {
        $tokens = PhpToken::tokenize(substr($source, 0, $classOffset));
        $imports = [];
        $depth = 0;

        foreach ($tokens as $index => $token) {
            if ($token->text === '{') {
                $depth++;

                continue;
            }

            if ($token->text === '}') {
                $depth--;

                continue;
            }

            if ($token->id !== T_USE) {
                continue;
            }

            $next = $this->nextSignificantTokenIndex($tokens, $index + 1);

            if (($next !== null) && ($tokens[$next]->text === '(')) {
                continue;
            }

            $end = $this->findTokenText($tokens, ';', $index + 1);

            if ($end === null) {
                continue;
            }

            $imports[] = substr(
                $source,
                $token->pos,
                ($tokens[$end]->pos + strlen($tokens[$end]->text)) - $token->pos,
            );
        }

        return $imports;
    }

    /**
     * @param  list<string>  $imports
     */
    private function findOurImport(array $imports): ?string
    {
        foreach ($imports as $import) {
            if (! str_contains(str_replace(' ', '', $import), HasPageHeader::class)) {
                continue;
            }

            if (preg_match('/\\bas\\s+([A-Za-z_][A-Za-z0-9_]*)/i', $import, $matches) === 1) {
                return $matches[1];
            }

            return 'HasPageHeader';
        }

        return null;
    }

    /**
     * @param  list<string>  $imports
     */
    private function hasShortNameConflict(array $imports): bool
    {
        foreach ($imports as $import) {
            if (str_contains(str_replace(' ', '', $import), HasPageHeader::class)) {
                continue;
            }

            if (preg_match('/(?:\\\\|\\{)HasPageHeader\\b(?:\\s+as\\s+([A-Za-z_][A-Za-z0-9_]*))?/i', $import, $matches) !== 1) {
                continue;
            }

            if ((! isset($matches[1])) || (strcasecmp($matches[1], 'HasPageHeader') === 0)) {
                return true;
            }
        }

        return false;
    }

    private function classUsesTrait(
        string $source,
        int $openBraceOffset,
        int $closeBraceOffset,
        string $traitReference,
    ): bool {
        $body = substr(
            $source,
            $openBraceOffset + 1,
            $closeBraceOffset - $openBraceOffset - 1,
        );

        $quoted = preg_quote($traitReference, '/');

        return preg_match(
            '/\\buse\\s+'.$quoted.'\\s*(?:[,;{])/m',
            $body,
        ) === 1;
    }

    private function insertImport(string $source, string $import, string $shortName): string
    {
        [$classOffset] = $this->locateClass($source, $shortName);
        $tokens = PhpToken::tokenize(substr($source, 0, $classOffset));
        $lastImportEnd = null;
        $namespaceEnd = null;

        foreach ($tokens as $index => $token) {
            if ($token->id === T_NAMESPACE) {
                for ($i = $index + 1; $i < count($tokens); $i++) {
                    if (in_array($tokens[$i]->text, [';', '{'], true)) {
                        $namespaceEnd = $tokens[$i]->pos + strlen($tokens[$i]->text);

                        break;
                    }
                }

                continue;
            }

            if ($token->id !== T_USE) {
                continue;
            }

            $next = $this->nextSignificantTokenIndex($tokens, $index + 1);

            if (($next !== null) && ($tokens[$next]->text === '(')) {
                continue;
            }

            $end = $this->findTokenText($tokens, ';', $index + 1);

            if ($end !== null) {
                $lastImportEnd = $tokens[$end]->pos + strlen($tokens[$end]->text);
            }
        }

        if ($lastImportEnd !== null) {
            return substr($source, 0, $lastImportEnd)
                ."\nuse {$import};"
                .substr($source, $lastImportEnd);
        }

        if ($namespaceEnd !== null) {
            return substr($source, 0, $namespaceEnd)
                ."\n\nuse {$import};"
                .substr($source, $namespaceEnd);
        }

        throw new RuntimeException('Unable to locate a namespace declaration in the page source file.');
    }
}
