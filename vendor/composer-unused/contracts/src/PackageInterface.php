<?php

declare(strict_types=1);

namespace ComposerUnused\Contracts;

use ComposerUnused\Contracts\Exception\LinkNotFoundException;

interface PackageInterface
{
    /**
     * @phpstan-return array{psr-0?: array<string, string|string[]>, psr-4?: array<string, string|string[]>, classmap?: list<string>, files?: list<string>}
     */
    public function getAutoload(): array;

    public function getName(): string;
    
    public function getUrl(): ?string;

    /**
     * @return array<LinkInterface>
     */
    public function getRequires(): array;

    /**
     * @return array<string>
     */
    public function getSuggests(): array;

    /**
     * @throws LinkNotFoundException
     */
    public function getRequire(string $name): LinkInterface;
}
