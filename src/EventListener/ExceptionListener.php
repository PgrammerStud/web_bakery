<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Log\LoggerInterface;

class ExceptionListener implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request   = $event->getRequest();

        // Log every unhandled exception
        $this->logger->error('Unhandled exception: ' . $exception->getMessage(), [
            'exception' => get_class($exception),
            'path'      => $request->getPathInfo(),
            'method'    => $request->getMethod(),
        ]);

        // API routes get JSON error responses
        if (str_starts_with($request->getPathInfo(), '/api')) {
            $event->setResponse($this->buildApiResponse($exception));
            return;
        }

        // Web routes: let Symfony's default error pages handle it
        // (remove the return if you want custom web error pages too)
    }

    private function buildApiResponse(\Throwable $exception): JsonResponse
    {
        $statusCode = $this->resolveStatusCode($exception);

        // In production, never expose raw exception messages for 500 errors
        $message = $statusCode === 500
            ? 'An unexpected error occurred. Please try again later.'
            : $exception->getMessage();

        return new JsonResponse([
            'success' => false,
            'error'   => $this->resolveErrorType($statusCode),
            'message' => $message,
        ], $statusCode);
    }

    private function resolveStatusCode(\Throwable $exception): int
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        // Map custom exceptions to HTTP status codes
        return match (true) {
            $exception instanceof \App\Exception\InsufficientStockException  => 400,
            $exception instanceof \App\Exception\InvalidQuantityException    => 400,
            $exception instanceof \App\Exception\DuplicateOrderException     => 409,
            default => 500,
        };
    }

    private function resolveErrorType(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'bad_request',
            401 => 'unauthorized',
            403 => 'forbidden',
            404 => 'not_found',
            409 => 'conflict',
            default => 'server_error',
        };
    }
}