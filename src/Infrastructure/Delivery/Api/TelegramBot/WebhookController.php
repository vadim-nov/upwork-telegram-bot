<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 07/04/2019
 * Time: 19:41
 */

namespace App\Infrastructure\Delivery\Api\TelegramBot;


use App\Application\TelegramBot\Factory\TelegramMessageFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController
{
    private $messageBus;
    private $telegramMessageFactory;

    public function __construct(MessageBusInterface $messageBus, TelegramMessageFactory $telegramMessageFactory)
    {
        $this->messageBus = $messageBus;
        $this->telegramMessageFactory = $telegramMessageFactory;
    }

    /**
     * @Route("/clb/telegram/{secret}")
     *
     * @param string $secret
     * @param Request $request
     * @return Response
     */
    public function index(string $secret, Request $request)
    {
        if ($secret === getenv('TELEGRAM_BOT_SECRET')) {
            $message = $this->telegramMessageFactory->createFromRequestBody($request->getContent());

            $this->messageBus->dispatch($message);

            return new Response();
        }

        throw new AccessDeniedHttpException();
    }
}
