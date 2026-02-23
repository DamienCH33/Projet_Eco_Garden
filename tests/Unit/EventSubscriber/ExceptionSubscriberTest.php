<?php

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\ExceptionSubscriber;
use App\Response\ApiResponse;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ExceptionSubscriberTest extends TestCase
{
    public function testExceptionCreatesApiErrorResponse(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('dev');

        $apiResponse = $this->createMock(ApiResponse::class);

        $expectedResponse = new JsonResponse([
            'success' => false,
            'message' => 'Boom',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);

        $apiResponse
            ->expects($this->once())
            ->method('error')
            ->willReturn($expectedResponse);

        $subscriber = new ExceptionSubscriber($kernel, $apiResponse);

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new \Exception('Boom')
        );

        $subscriber->onKernelException($event);

        $this->assertSame($expectedResponse, $event->getResponse());
    }

    public function testExceptionMessageHiddenInProd(): void
    {
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getEnvironment')->willReturn('prod');

        $apiResponse = $this->createMock(ApiResponse::class);

        $response = new JsonResponse([
            'success' => false,
            'message' => 'An unexpected error occurred',
        ], 500);

        $apiResponse->method('error')->willReturn($response);

        $subscriber = new ExceptionSubscriber($kernel, $apiResponse);

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new \Exception('SECRET MESSAGE')
        );

        $subscriber->onKernelException($event);

        $this->assertSame($response, $event->getResponse());
    }
}
