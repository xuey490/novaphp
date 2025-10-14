<?php

declare(strict_types=1);

namespace ComposerUnused\Contracts;

interface RepositoryInterface
{
    public function findPackage(string $name): ?PackageInterface;
}
