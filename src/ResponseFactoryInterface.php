<?php

declare(strict_types=1);

namespace WilyCode\Http\Response;

use JsonException;
use Psr\Http\Message\ResponseInterface;

interface ResponseFactoryInterface
{
    /**
     * @param array<array-key, mixed> $data
     * @param array<string, string|string[]> $headers
     *
     * @throws JsonException
     */
    public function json(
        array $data,
        HttpStatus $status = HttpStatus::Ok,
        array $headers = [],
    ): ResponseInterface;

    /** @param array<string, string|string[]> $headers */
    public function html(
        string $html,
        HttpStatus $status = HttpStatus::Ok,
        array $headers = [],
    ): ResponseInterface;

    /** @param array<string, string|string[]> $headers */
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
