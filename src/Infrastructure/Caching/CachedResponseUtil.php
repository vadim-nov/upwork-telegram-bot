<?php

namespace App\Infrastructure\Caching;

use Symfony\Component\HttpFoundation\Response;

class CachedResponseUtil
{
    private $response;
    private $ttl;

    public function __construct(Response $response, $ttl = 600)
    {
        $this->response = $response;
        $this->ttl = $ttl;
    }

    public function stream(): Response
    {
        $this->response->setSharedMaxAge($this->ttl);

        $this->response->setVary(['User-Agent']);
        $this->response->headers->addCacheControlDirective('must-revalidate', true);

        return $this->response;
    }
}
