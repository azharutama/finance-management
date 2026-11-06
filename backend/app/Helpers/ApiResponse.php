<?php

namespace App\Helpers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ApiResponse
{
    /**
     * Return a standardized JSON success response (default 200 OK).
     */
    public static function success(
        mixed $data = null,
        string $message = 'Request processed successfully.',
        int $status = SymfonyResponse::HTTP_OK,
        ?array $meta = null
    ): JsonResponse {
        if ($data instanceof JsonResource) {
            $data = $data->resolve(request());
        }

        return response()->json([
            'success' => true,
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
            'errors' => null,
        ], $status);
    }

    /**
     * 201 Created response.
     */
    public static function created(
        mixed $data = null,
        string $message = 'Resource created successfully.',
        ?array $meta = null
    ): JsonResponse {
        return self::success($data, $message, SymfonyResponse::HTTP_CREATED, $meta);
    }

    /**
     * 202 Accepted response for asynchronous processing.
     */
    public static function accepted(
        mixed $data = null,
        string $message = 'Request accepted for processing.',
        ?array $meta = null
    ): JsonResponse {
        return self::success($data, $message, SymfonyResponse::HTTP_ACCEPTED, $meta);
    }

    /**
     * 204 No Content response (must not contain a body).
     */
    public static function noContent(): Response
    {
        return response()->noContent(SymfonyResponse::HTTP_NO_CONTENT);
    }

    /**
     * 200 OK response with standardized pagination metadata.
     */
    public static function paginated(
        LengthAwarePaginator $paginator,
        ?string $resourceClass = null,
        string $message = 'Data retrieved successfully.',
        array $extraMeta = []
    ): JsonResponse {
        $items = $paginator->items();

        if ($resourceClass !== null) {
            /** @var AnonymousResourceCollection $collection */
            $collection = $resourceClass::collection(collect($items));
            $items = $collection->resolve(request());
        }

        $meta = array_merge([
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ], $extraMeta);

        return self::success($items, $message, SymfonyResponse::HTTP_OK, $meta);
    }

    /**
     * Return a standardized JSON error response.
     */
    public static function error(
        string $message = 'An error occurred.',
        int $status = SymfonyResponse::HTTP_BAD_REQUEST,
        mixed $errors = null,
        ?array $meta = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'status' => $status,
            'message' => $message,
            'data' => null,
            'meta' => $meta,
            'errors' => $errors,
        ], $status);
    }

    /**
     * 400 Bad Request.
     */
    public static function badRequest(string $message = 'Bad request.', mixed $errors = null): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_BAD_REQUEST, $errors);
    }

    /**
     * 401 Unauthorized.
     */
    public static function unauthorized(string $message = 'Unauthenticated.'): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * 403 Forbidden.
     */
    public static function forbidden(string $message = 'This action is unauthorized.'): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_FORBIDDEN);
    }

    /**
     * 404 Not Found.
     */
    public static function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_NOT_FOUND);
    }

    /**
     * 405 Method Not Allowed.
     */
    public static function methodNotAllowed(string $message = 'Method not allowed.'): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_METHOD_NOT_ALLOWED);
    }

    /**
     * 409 Conflict.
     */
    public static function conflict(string $message = 'Resource conflict.', mixed $errors = null): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_CONFLICT, $errors);
    }

    /**
     * 413 Payload Too Large.
     */
    public static function payloadTooLarge(string $message = 'Payload too large.'): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_REQUEST_ENTITY_TOO_LARGE);
    }

    /**
     * 415 Unsupported Media Type.
     */
    public static function unsupportedMediaType(string $message = 'Unsupported media type.'): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }

    /**
     * 422 Unprocessable Entity.
     */
    public static function validationError(
        mixed $errors,
        string $message = 'The given data was invalid.'
    ): JsonResponse {
        return self::error($message, SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * 429 Too Many Requests.
     */
    public static function tooManyRequests(string $message = 'Too many requests. Please slow down.'): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_TOO_MANY_REQUESTS);
    }

    /**
     * 500 Internal Server Error.
     */
    public static function serverError(string $message = 'An unexpected internal server error occurred.'): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * 502 Bad Gateway.
     */
    public static function badGateway(string $message = 'Bad gateway.'): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_BAD_GATEWAY);
    }

    /**
     * 503 Service Unavailable.
     */
    public static function serviceUnavailable(string $message = 'Service temporarily unavailable.'): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_SERVICE_UNAVAILABLE);
    }

    /**
     * 504 Gateway Timeout.
     */
    public static function gatewayTimeout(string $message = 'Gateway timeout.'): JsonResponse
    {
        return self::error($message, SymfonyResponse::HTTP_GATEWAY_TIMEOUT);
    }
}

