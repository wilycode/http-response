<?php

declare(strict_types=1);

namespace WilyCode\Http\Response;

enum HttpStatus: int
{
    case Ok = 200;
    case Created = 201;
    case Accepted = 202;
    case NoContent = 204;

    case MovedPermanently = 301;
    case Found = 302;
    case SeeOther = 303;
    case NotModified = 304;
    case TemporaryRedirect = 307;
    case PermanentRedirect = 308;

    case BadRequest = 400;
    case Unauthorized = 401;
    case Forbidden = 403;
    case NotFound = 404;
    case MethodNotAllowed = 405;
    case Conflict = 409;
    case UnprocessableContent = 422;
    case TooManyRequests = 429;

    case InternalServerError = 500;
    case NotImplemented = 501;
    case BadGateway = 502;
    case ServiceUnavailable = 503;
}
