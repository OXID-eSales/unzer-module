<?php

namespace OxidSolutionCatalysts\Unzer\Service;

use OxidSolutionCatalysts\Unzer\Core\Response;

class ResponseHandler
{
    protected Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function response(): Response
    {
        return $this->response;
    }
}
