<?php

namespace Packlink\Middleware\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Logeecom\Infrastructure\Configuration\Configuration;
use Logeecom\Infrastructure\ORM\QueryFilter\Operators;
use Logeecom\Infrastructure\ORM\QueryFilter\QueryFilter;
use Logeecom\Infrastructure\ORM\RepositoryRegistry;
use Logeecom\Infrastructure\ServiceRegister;
use Packlink\Shopify\Entity\ShopifyUser;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (empty($_SERVER['HTTP_X_PACKLINK_AUTH'])) {
            throw new HttpException(401, 'Unauthorized!');
        }

        $token = explode('.', $_SERVER['HTTP_X_PACKLINK_AUTH']);

        if (!$this->isTokenStructureValid($token)) {
            throw new HttpException(401, 'Unauthorized!');
        }

        $payload = $this->decodePayload($token[1]);

        if (!$this->isSignatureValid($token[0], $token[1], $token[2])) {
            throw new HttpException(401, 'Unauthorized!');
        }

        if (!$this->isPayloadValid($payload)) {
            throw new HttpException(401, 'Unauthorized!');
        }

        if ($this->isTokenExpired($payload)) {
            throw new HttpException(401, 'Unauthorized!');
        }

        $domain = str_replace('https://', '', $payload['dest']);

        if (!$this->isUserValid($domain)) {
            throw new HttpException(401, 'Unauthorized!');
        }

        $this->getConfigService()->setContext($domain);

        return $next($request);
    }

    /**
     * Validates whether token has a header, payload and signature.
     *
     * @param array $token
     *
     * @return bool
     */
    private function isTokenStructureValid(array $token): bool
    {
        return count($token) === 3;
    }

    /**
     * Decodes JWT payload to array.
     *
     * @param string $payload
     *
     * @return array
     */
    private function decodePayload(string $payload): array
    {
        return json_decode(base64_decode(urldecode($payload)), true);
    }

    /**
     * Checks whether the token signature is valid.
     *
     * @param string $header
     * @param string $payload
     * @param string $signature
     *
     * @return bool
     */
    private function isSignatureValid(string $header, string $payload, string $signature): bool
    {
        $brand = config('brand.active');
        $hash = hash_hmac(
            'sha256',
            $header . '.' . $payload, config('brand.' . $brand . '.shopify.secret'),
            true
        );
        $hash = base64_encode($hash);
        $hash = str_replace(['+', '/', '='], ['-', '_', ''], $hash);

        return hash_equals($hash, $signature);
    }

    /**
     * Checks if payload has all required fields.
     *
     * @param array $payload
     *
     * @return bool
     */
    private function isPayloadValid(array $payload): bool
    {
        return !empty($payload['dest']) && !empty($payload['exp']);
    }

    /**
     * Checks whether the token has been expired.
     *
     * @param array $payload
     *
     * @return bool
     */
    private function isTokenExpired(array $payload): bool
    {
        return time() > $payload['exp'];
    }

    /**
     * Checks whether a user exists for the given domain.
     *
     * @param string $domain
     *
     * @return bool
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function isUserValid(string $domain): bool
    {
        $filter = new QueryFilter();
        /** @noinspection PhpUnhandledExceptionInspection */
        $filter->where('domain', Operators::EQUALS, $domain);

        $user = $this->getUserRepository()->selectOne($filter);

        return $user !== null;
    }

    /**
     * Retrieves configuration service.
     *
     * @return Configuration | object
     */
    private function getConfigService()
    {
        return ServiceRegister::getService(Configuration::CLASS_NAME);
    }

    /**
     * Retrieves Shopify User repository.
     *
     * @return \Logeecom\Infrastructure\ORM\Interfaces\RepositoryInterface
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function getUserRepository()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return RepositoryRegistry::getRepository(ShopifyUser::getClassName());
    }
}