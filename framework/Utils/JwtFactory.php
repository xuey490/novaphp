<?php

declare(strict_types=1);

namespace Framework\Utils;

use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha384;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsaSha256;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Token\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\Clock\SystemClock;
use Framework\Utils\Cookie;

class JwtFactory
{
    protected Configuration $config;
    protected array $jwtConfig;
    protected DateTimeZone $timezone;

    public function __construct()
    {
        $this->jwtConfig = config('jwt');
        $this->timezone = new DateTimeZone(config('app.time_zone')?? 'Asia/Shanghai'); // ✅ 统一时区
        $this->config = $this->buildConfiguration();
    }

    protected function buildConfiguration(): Configuration
    {
        $algo = $this->jwtConfig['algo'];
        $secret = $this->jwtConfig['secret'];

        $signer = match ($algo) {
            'HS256' => new Sha256(),
            'HS384' => new Sha384(),
            'HS512' => new Sha512(),
            'RS256' => new RsaSha256(),
            default => throw new \InvalidArgumentException("Unsupported algorithm: {$algo}")
        };

        if (in_array($algo, ['HS256', 'HS384', 'HS512'])) {
            $key = InMemory::plainText($secret);
        } else {
            $key = InMemory::file(storage_path('keys/private.key'));
        }

        return Configuration::forSymmetricSigner($signer, $key);
    }

	/*
	* 签发jwt token
	*/
    public function issue(array $claims = [], ?int $ttl = null): string
    {
        // 使用 Asia/Shanghai 时区的当前时间
        $now = new DateTimeImmutable('now', $this->timezone);
        $ttl = $ttl ?? $this->jwtConfig['ttl'];
        $expiresAt = $now->modify("+{$ttl} seconds");
		
		// === 新增：写入 Redis ===
		$userId = $claims['uid'] ?? null;
		// === 新增：如果是单点登录，先踢掉该用户所有旧 Token ===
		if ($userId && ($this->jwtConfig['single_device_login'] ?? false)) {
			$this->cleanExpiredTokens($userId);
			$this->revokeAllForUser($userId);
		}

        $jti = bin2hex(random_bytes(16));

        $builder = $this->config->builder()
			->permittedFor($this->jwtConfig['audience'])    // aud: 受众
            ->identifiedBy($jti)
            ->issuedBy($this->jwtConfig['issuer'])
            ->issuedAt($now)
			->canOnlyBeUsedAfter($now)                    // nbf: 生效时间
            ->expiresAt($expiresAt)
            ->withHeader('typ', 'JWT');

        foreach ($claims as $key => $value) {
            $builder = $builder->withClaim($key, $value);
        }

        $token = $builder->getToken($this->config->signer(), $this->config->signingKey());
		$tokenStr = $token->toString();
		
		//存cookie
		//print_r($tokenStr);
		app('cookie')->make('token', $tokenStr);

		if ($userId) {
			$redis = app('redis.client'); // 或 Redis::connection()
			// 1. 存储 token -> user_id 映射（用于验证时快速查用户）
			$redis->setex("login:token:{$jti}", $ttl, $userId);
			// 2. 将 jti 加入用户活跃列表（用于踢人）
			$redis->sadd("user:active_tokens:{$userId}", $jti);
			// 可选：给这个 set 也设个稍长的 TTL，比如 ttl + 3600
		}	
		
        return $tokenStr;
    }

	/*
	* 解析jwt token
	*/
	public function parse(string $token): Plain
	{
		$token = trim($token);

		if (substr_count($token, '.') !== 2 || !preg_match('/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+$/', $token)) {
			throw new \InvalidArgumentException('Invalid JWT format.');
		}

		$parsed = 	$this->config->parser()->parse($token);
		
		
		// 额外：检查是否在 Redis 中存在（即未被提前注销）

		$jti = $parsed->claims()->get('jti');
		if ($jti && !app('redis.client')->exists("login:token:{$jti}")) {
			throw new \RuntimeException('Token not active or already expired.');
		}
		
		// 通过 jti 查 user_id
		$userId = app('redis.client')->get("login:token:{$jti}");
		if (!$userId) {
			throw new \RuntimeException('Token invalid or expired.');
		}

		// ✅ 正确方式：new SystemClock($timezone)
		$clock = new SystemClock(new DateTimeZone(config('app.time_zone')?? 'Asia/Shanghai'));

		$constraints = [
			new IssuedBy($this->jwtConfig['issuer']),
			new LooseValidAt(
				$clock,
				new \DateInterval('PT' . $this->jwtConfig['blacklist_grace_period'] . 'S')
			),
		];

		$this->config->validator()->assert($parsed, ...$constraints);

		if ($this->isBlacklisted($parsed)) {
			throw new \RuntimeException('Token has been revoked.');
		}

		return $parsed;
	}


	/*
	* 刷新jwt token
	*/
    public function refresh(string $token, ?int $ttl = null): string
    {
        $parsed = $this->parse($token);

        $iat = $parsed->claims()->get('iat');	// DateTimeImmutable
        // 注意：iat/exp 是时间戳（UTC 无关），但刷新逻辑基于当前时间
        $refreshExp = $iat->getTimestamp() + $this->jwtConfig['refresh_ttl'];
        $nowTimestamp = (new DateTimeImmutable('now', $this->timezone))->getTimestamp();

        if ($nowTimestamp > $refreshExp) {
            throw new \RuntimeException('Token cannot be refreshed: refresh TTL expired.');
        }

        $claims = [];
        foreach ($parsed->claims()->all() as $name => $value) {
            if (!in_array($name, ['iss', 'iat', 'exp', 'nbf', 'jti'], true)) {
                $claims[$name] = $value;
            }
        }

        return $this->issue($claims, $ttl);
    }

	/*
	注销用户或所有用户的Token（踢下线）
	*/
	public function revokeAllForUser(int $userId): void
	{
		$redis = app('redis.client');
		$jtis = $redis->smembers("user:active_tokens:{$userId}");

		// 删除所有 token key
		if (!empty($jtis)) {
			$keys = array_map(fn($jti) => "login:token:{$jti}", $jtis);
			$redis->del(...$keys);
		}

		// 清空用户 token 集合
		$redis->del("user:active_tokens:{$userId}");
		
		//清理cookie
		app('cookie')->forget('token');
	}



	// 优化方案（可选）在 issue() 中，单点登录前清理（或定期清理）
	public function cleanExpiredTokens(int $userId): void
	{
		$redis = app('redis.client');
		$jtis = $redis->smembers("user:active_tokens:{$userId}");
		
		$validJtis = [];
		foreach ($jtis as $jti) {
			if ($redis->exists("login:token:{$jti}")) {
				$validJtis[] = $jti;
			}
		}

		// 重建集合（或只删无效的）
		if (count($validJtis) !== count($jtis)) {
			$redis->del("user:active_tokens:{$userId}");
			if (!empty($validJtis)) {
				$redis->sadd("user:active_tokens:{$userId}", ...$validJtis);
			}
		}
	}

	public function revoke(string $token): void
	{
		$parsed = $this->parse($token); // 会验证 + 检查 Redis 存在性
		$jti = $parsed->claims()->get('jti');
		$userId = $parsed->claims()->get('uid');

		if (!$jti) {
			throw new \RuntimeException('Token missing jti claim, cannot revoke.');
		}

		// === 1. 从 Redis 删除（无论黑名单是否开启，都要踢下线）===
		$redis = app('redis.client');
		$redis->del("login:token:{$jti}");
		if ($userId) {
			$redis->srem("user:active_tokens:{$userId}", $jti);
		}

		// === 2. 仅当黑名单开启时，加入缓存黑名单（防重放）===
		if ($this->jwtConfig['blacklist_enabled']) {
			$exp = $parsed->claims()->get('exp');
			$nowTimestamp = (new DateTimeImmutable('now', $this->timezone))->getTimestamp();
			$ttl = max(0, $exp - $nowTimestamp + $this->jwtConfig['blacklist_grace_period']);

			if ($ttl > 0) {
				cache()->put("jwt_blacklist:{$jti}", true, now()->addSeconds($ttl));
			}
		}
	}

    protected function isBlacklisted(Plain $token): bool
    {
        if (!$this->jwtConfig['blacklist_enabled']) {
            return false;
        }

        $jti = $token->claims()->get('jti');
        return $jti && app('cache')->has("jwt_blacklist:{$jti}");
    }

    public function getPayload(string $token): array
    {
        $parsed = $this->parse($token);
        return $parsed->claims()->all();
    }
}