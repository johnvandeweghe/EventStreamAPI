<?php
namespace EventStreamApi\Security;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TokenVerifier
{
    public function __construct(
        private string $jwksUri,
        private string $issuer,
        private string $audience,
        private CacheInterface $jwkCache
    ) {}

    /**
     * @param string $token
     * @return array<string, string>
     */
    public function verify(string $token): array
    {
        $keys = $this->getJWKs();
        $verifiedToken = (array) JWT::decode($token, $keys, ["RS256"]);


        $iss = $verifiedToken["iss"] ?? null;

        if ($iss !== $this->issuer) {
            throw new \UnexpectedValueException(
                "iss was not the required value; expected '{$this->issuer}', found '$iss'"
            );
        }

        $aud = $verifiedToken["aud"] ?? null;

        if (is_array($aud) && !in_array($this->audience, $aud, true)) {
            throw new \UnexpectedValueException( sprintf(
                "aud did not have the required value; expected '{$this->audience}' was not one of '%s'",
                implode(', ', $aud)
            ) );
        }

        if (is_string($aud) && $aud !== $this->audience) {
            throw new \UnexpectedValueException(
                "Audience (aud) claim mismatch in the ID token; expected '{$this->audience}', found '$aud'"
             );
        }

        return $verifiedToken;
    }

    /**
     * @return array<string, string>
     */
    protected function getJWKs()
    {
        return $this->jwkCache->get("jwks-" . md5($this->jwksUri), function (ItemInterface $item) {
            $rawJWKs = file_get_contents($this->jwksUri);
            if (!$rawJWKs) {
                throw new \RuntimeException("Unable to retrieve JWK set");
            }
            $jwks = json_decode($rawJWKs, true, 512, JSON_THROW_ON_ERROR);
            return JWK::parseKeySet($jwks);
        });
    }
}