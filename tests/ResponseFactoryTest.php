<?php

declare(strict_types=1);

namespace WilyCode\Http\Response\Tests;

use InvalidArgumentException;
use JsonException;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WilyCode\Http\Response\HttpStatus;
use WilyCode\Http\Response\ResponseFactory;
use WilyCode\Http\Response\ResponseFactoryInterface;

final class ResponseFactoryTest extends TestCase
{
    private ResponseFactory $factory;

    protected function setUp(): void
    {
        $psr17Factory = new Psr17Factory();
        $this->factory = new ResponseFactory($psr17Factory, $psr17Factory);
        self::assertInstanceOf(ResponseFactoryInterface::class, $this->factory);
    }

    public function testCreatesJsonResponseWithTypedStatusAndUtf8Body(): void
    {
        $response = $this->factory->json(
            ['id' => 15, 'title' => 'Zażółć / test'],
            HttpStatus::Created,
            ['Location' => '/articles/15'],
        );

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('application/json; charset=utf-8', $response->getHeaderLine('Content-Type'));
        self::assertSame('/articles/15', $response->getHeaderLine('Location'));
        self::assertSame('{"id":15,"title":"Zażółć / test"}', (string) $response->getBody());
    }

    public function testJsonThrowsWhenPayloadCannotBeEncoded(): void
    {
        $this->expectException(JsonException::class);

        $stream = fopen('php://memory', 'rb');
        self::assertIsResource($stream);

        try {
            $this->factory->json(['invalid' => $stream]);
        } finally {
            fclose($stream);
        }
    }

    public function testCreatesHtmlResponseAndAllowsHeaderOverride(): void
    {
        $response = $this->factory->html(
            '<h1>Article</h1>',
            headers: ['Content-Type' => 'application/xhtml+xml'],
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/xhtml+xml', $response->getHeaderLine('Content-Type'));
        self::assertSame('<h1>Article</h1>', (string) $response->getBody());
    }

    public function testCreatesSeeOtherRedirectByDefault(): void
    {
        $response = $this->factory->redirect('/articles');

        self::assertSame(303, $response->getStatusCode());
        self::assertSame('/articles', $response->getHeaderLine('Location'));
        self::assertSame('', (string) $response->getBody());
    }

    public function testCreatesNoContentResponseByDefault(): void
    {
        $response = $this->factory->empty();

        self::assertSame(204, $response->getStatusCode());
        self::assertSame('', (string) $response->getBody());
    }

    public function testJsonSupportsNestedListsAndEscapes(): void
    {
        $response = $this->factory->json([
            'items' => [['id' => 1], ['id' => 2]],
            'path' => '/articles',
        ]);

        self::assertSame('{"items":[{"id":1},{"id":2}],"path":"/articles"}', (string) $response->getBody());
    }

    public function testAcceptsMultipleHeaderValues(): void
    {
        $response = $this->factory->html('Hello', headers: ['Vary' => ['Accept', 'Accept-Language']]);

        self::assertSame(['Accept', 'Accept-Language'], $response->getHeader('Vary'));
    }

    public function testContentTypeOverrideIsCaseInsensitive(): void
    {
        $response = $this->factory->json(['ok' => true], headers: ['content-type' => 'application/problem+json']);

        self::assertSame('application/problem+json', $response->getHeaderLine('Content-Type'));
        self::assertSame(['application/problem+json'], $response->getHeader('Content-Type'));
    }

    public function testSupportsPermanentAndFoundRedirects(): void
    {
        $permanent = $this->factory->redirect('/new', HttpStatus::MovedPermanently);
        $found = $this->factory->redirect('/next', HttpStatus::Found);

        self::assertSame(301, $permanent->getStatusCode());
        self::assertSame(302, $found->getStatusCode());
        self::assertSame('/new', $permanent->getHeaderLine('Location'));
        self::assertSame('/next', $found->getHeaderLine('Location'));
    }

    public function testNotModifiedHasNoBody(): void
    {
        $response = $this->factory->empty(HttpStatus::NotModified);

        self::assertSame(304, $response->getStatusCode());
        self::assertSame('', (string) $response->getBody());
    }

    #[DataProvider('bodylessStatuses')]
    public function testJsonRejectsBodylessStatuses(HttpStatus $status): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('HTTP ' . $status->value);

        $this->factory->json(['ok' => true], $status);
    }

    #[DataProvider('bodylessStatuses')]
    public function testHtmlRejectsBodylessStatuses(HttpStatus $status): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('HTTP ' . $status->value);

        $this->factory->html('<p>Unexpected body</p>', $status);
    }

    /** @return iterable<string, array{HttpStatus}> */
    public static function bodylessStatuses(): iterable
    {
        yield '204 No Content' => [HttpStatus::NoContent];
        yield '304 Not Modified' => [HttpStatus::NotModified];
    }

    #[DataProvider('redirectStatuses')]
    public function testRedirectSupportsAllRedirectStatuses(HttpStatus $status): void
    {
        $response = $this->factory->redirect('/target', $status);

        self::assertSame($status->value, $response->getStatusCode());
        self::assertSame('/target', $response->getHeaderLine('Location'));
        self::assertSame('', (string) $response->getBody());
    }

    /** @return iterable<string, array{HttpStatus}> */
    public static function redirectStatuses(): iterable
    {
        yield '301 Moved Permanently' => [HttpStatus::MovedPermanently];
        yield '302 Found' => [HttpStatus::Found];
        yield '303 See Other' => [HttpStatus::SeeOther];
        yield '307 Temporary Redirect' => [HttpStatus::TemporaryRedirect];
        yield '308 Permanent Redirect' => [HttpStatus::PermanentRedirect];
    }

    #[DataProvider('nonRedirectStatuses')]
    public function testRedirectRejectsNonRedirectStatuses(HttpStatus $status): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('HTTP ' . $status->value);

        $this->factory->redirect('/target', $status);
    }

    /** @return iterable<string, array{HttpStatus}> */
    public static function nonRedirectStatuses(): iterable
    {
        yield '200 OK' => [HttpStatus::Ok];
        yield '204 No Content' => [HttpStatus::NoContent];
        yield '304 Not Modified' => [HttpStatus::NotModified];
        yield '404 Not Found' => [HttpStatus::NotFound];
    }

    public function testHtmlAllowsEmptyBodyForNormalStatus(): void
    {
        $response = $this->factory->html('', HttpStatus::Ok);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('', (string) $response->getBody());
    }

    public function testHttpStatusExposesNameAndNumber(): void
    {
        self::assertSame('Created', HttpStatus::Created->name);
        self::assertSame(201, HttpStatus::Created->value);
    }
}
