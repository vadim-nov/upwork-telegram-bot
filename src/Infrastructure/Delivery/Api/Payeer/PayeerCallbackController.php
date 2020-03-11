<?php

declare(strict_types=1);

namespace App\Infrastructure\Delivery\Api\Payeer;

use App\Domain\Payment\PaymentGatewayInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class PayeerCallbackController extends AbstractController
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * @Route("/clb/payeer/status", name="payeer_status")
     */
    public function handleStatus()
    {
        return new JsonResponse('Payment status');
    }

    /**
     * @Route("/clb/payeer/success", name="payeer_success")
     */
    public function handleSuccess(Request $request, PaymentGatewayInterface $paymentGateway)
    {
        $paymentCallback = $paymentGateway->parsePaymentCallback($request->query->all());
        try {
            $this->messageBus->dispatch($paymentCallback);
        } catch (\DomainException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }

        return new Response('payment success');
    }

    /**
     * @Route("/clb/payeer/fail", name="payeer_fail")
     */
    public function handleFail()
    {
        return new JsonResponse('Payment failed');
    }
}
