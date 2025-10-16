<?php

declare(strict_types=1);

/**
 * This file is part of Navaphp Framework.
 *
 * @link     https://github.com/xuey490/novaphp
 * @license  https://github.com/xuey490/novaphp/blob/main/LICENSE
 *
 * @Filename: %filename%
 * @Date: 2025-10-16
 * @Developer: xuey863toy
 * @Email: xuey863toy@gmail.com
 */

namespace Framework\Security;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CsrfTokenManager
{
    private SessionInterface $session;

    private string $namespace;

    public function __construct(SessionInterface $session, string $namespace = 'csrf_token')
    {
        $this->session   = $session;
        $this->namespace = $namespace;
    }

    public function getToken(string $tokenId = 'default'): string
    {
        $token = $this->session->get($this->getSessionKey($tokenId));
        if (! $token) {
            $token = bin2hex(random_bytes(32));
            $this->session->set($this->getSessionKey($tokenId), $token);
        }
        return $token;
    }

    public function isTokenValid(string $tokenId, string $token): bool
    {
        $expected = $this->session->get($this->getSessionKey($tokenId));
        if (! $expected) {
            return false;
        }
        return hash_equals($expected, $token);
    }

    public function removeToken(string $tokenId = 'default'): void
    {
        $this->session->remove($this->getSessionKey($tokenId));
    }

    private function getSessionKey(string $tokenId): string
    {		 // echo $this->namespace . '.' . $tokenId.'----';
        return $this->namespace . '.' . $tokenId;
    }
}
