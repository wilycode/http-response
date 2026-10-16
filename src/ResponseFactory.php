<?php

declare(strict_types=1);

namespace WilyCode\Http\Response;

use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface as PsrResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class ResponseFactory implements ResponseFactoryInterface
{
    public function __construct(
        private PsrResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
    ) {
    }

    public function json(
        array $data,
        HttpStatus $status = HttpStatus::Ok,
        array $headers = [],
    ): ResponseInterface {
        $this->assertBodyAllowed($status);

        $body = json_encode(
            $data,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );

        return $this->create(
            $body,
            $status,
            array_replace([
                'Content-Type' => 'application/json; charset=utf-8',
            ], $headers),
        );
    }

    public function html(
        string $html,
        HttpStatus $status = HttpStatus::Ok,
        array $headers = [],
    ): ResponseInterface {
        $this->assertBodyAllowed($status);

        return $this->create(
            $html,
            $status,
            array_replace([
                'Content-Type' => 'text/html; charset=utf-8',
            ], $headers),
        );
    }

    public function redirect(
        string $url,
        HttpStatus $status = HttpStatus::SeeOther,
        array $headers = [],
    ): ResponseInterface {
        if (!in_array($status, [
            HttpStatus::MovedPermanently,
            HttpStatus::Found,
            HttpStatus::SeeOther,
            HttpStatus::TemporaryRedirect,
            HttpStatus::PermanentRedirect,
        ], true)) {
            throw new InvalidArgumentException(sprintf(
                'HTTP %d is not a redirect status.',
                $status->value,
            ));
        }

        return $this->create(
            '',
            $status,
            array_replace([
                'Location' => $url,
            ], $headers),
        );
    }

    public function empty(
        HttpStatus $status = HttpStatus::NoContent,
        array $headers = [],
    ): ResponseInterface {
        return $this->create('', $status, $headers);
    }

    private function assertBodyAllowed(HttpStatus $status): void
    {
        if ($status === HttpStatus::NoContent || $status === HttpStatus::NotModified) {
            throw new InvalidArgumentException(sprintf(
                'HTTP %d must not have a response body. Use empty() instead.',
                $status->value,
            ));
        }
    }

    /** @param array<string, string|string[]> $headers */
    private function create(
        string $body,
        HttpStatus $status,
        array $headers,
    ): ResponseInterface {
        $response = $this->responseFactory
            ->createResponse($status->value)
            ->withBody($this->streamFactory->createStream($body));

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }
}
