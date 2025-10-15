<?php

declare(strict_types=1);

namespace ComposerUnused\Contracts;

interface LinkInterface
{
    public function getTarget(): string;

    /**
     * Returns the line number of the target from composer.json
     */
    public function getLineNumber(): int;
}
