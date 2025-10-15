<?php

declare(strict_types=1);

namespace ComposerUnused\Contracts\Exception;

use RuntimeException;

final class LinkNotFoundException extends RuntimeException implements ContractsExceptionInterface
{
    public static function fromMissingLink(string $name): self
    {
        return new self(sprintf('Link with name "%s" is not required', $name));
    }
}
