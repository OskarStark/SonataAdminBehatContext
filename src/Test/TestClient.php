<?php

namespace OStark\Test;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;

class TestClient extends Client
{
    protected $nextResponse = null;
    protected $nextScript = null;

    public function setNextResponse(Response $response)
    {
        $this->nextResponse = $response;
    }

    public function setNextScript($script)
    {
        $this->nextScript = $script;
    }

    protected function doRequest($request)
    {
        if (null === $this->nextResponse) {
            return new Response();
        }

        $response = $this->nextResponse;
        $this->nextResponse = null;

        return $response;
    }
}
