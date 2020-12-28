<?php
namespace PostChat\Api\Security;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TokenVerifier
{
    public function __construct(private string $jwksUri, private CacheInterface $jwkCache) {}

    public function verify(string $token): array
    {
        $keys = $this->jwkCache->get("jwks-" . md5($this->jwksUri), function(ItemInterface $item) {
            $jwks = json_decode(file_get_contents($this->jwksUri), true, 512, JSON_THROW_ON_ERROR);
            return JWK::parseKeySet($jwks);
        });
        return (array) JWT::decode($token, $keys, ["RSA256"]);
        //TODO: validate iss and aud fields.
    }
}