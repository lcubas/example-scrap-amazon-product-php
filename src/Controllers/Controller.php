<?php

declare(strict_types=1);

namespace App\Controllers;

use Throwable;
use App\Response\ResponsePayload;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpBadRequestException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class Controller
{
    // protected LoggerInterface $logger;

    protected Request $request;

    protected Response $response;

    protected array $args;

    /**
     * @throws HttpNotFoundException
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            return $this->handle();
        } catch (Throwable $e) {
            throw new HttpBadRequestException($this->request, $e->getMessage());
        }
    }

    /**
     * @throws Throwable
     * @throws HttpBadRequestException
     */
    abstract protected function handle(): Response;

    /**
     * @param string|null
     *
     * @return mixed
     */
    protected function getFormData($key =  null)
    {
        if (!empty($key)) {
            return $this->request->getParsedBody()[$key] ?? null;
        }

        return $this->request->getParsedBody();
    }

    /**
     * @return mixed
     *
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name)
    {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    /**
     * @param array|object|null $data
     */
    protected function respondWithData($data = null, int $statusCode = 200): Response
    {
        $payload = new ResponsePayload($statusCode, $data);

        return $this->respond($payload);
    }

    protected function respond(ResponsePayload $payload): Response
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);

        return $this->response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($payload->getStatusCode());
    }
}
