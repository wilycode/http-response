<?php

declare(strict_types=1);

namespace WilyCode\Http\Response;

use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

interface ResponseFactoryInterface
{
    /**
     * @param array<array-key, mixed> $data
     * @param array<string, string|string[]> $headers
     *
     * @throws JsonException
     * @throws InvalidArgumentException When the status does not allow a response body.
     */
    public function json(
        array $data,
        HttpStatus $status = HttpStatus::Ok,
        array $headers = [],
    ): ResponseInterface;

    /**
     * @param array<string, string|string[]> $headers
     * @throws InvalidArgumentException When the status does not allow a response body.
     */
    public function html(
        string $html,
        HttpStatus $status = HttpStatus::Ok,
        array $headers = [],
    ): ResponseInterface;

    /**
     * @param array<string, string|string[]> $headers
     * @throws InvalidArgumentException When the status is not a redirect status.
     */
    public function redirect(
        string $url,
        HttpStatus $status = HttpStatus::SeeOther,
        array $headers = [],
    ): ResponseInterface;

    /** @param array<string, string|string[]> $headers */
    public function empty(
        HttpStatus $status = HttpStatus::NoContent,
        array $headers = [],
    ): ResponseInterface;
}
