<?php

declare(strict_types=1);

namespace App\Application\TelegramBot\MessageHandler\Payment;

use App\Application\TelegramBot\Message\Payment\RequestPlanUpgrade;
use App\Domain\TelegramBot\TelegramApiInterface;
use App\Domain\Core\Entity\Plan;
use Doctrine\ORM\EntityManagerInterface;
use Money\Formatter\IntlMoneyFormatter;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RequestPlanUpgradeHandler implements MessageHandlerInterface
{
    private $telegramApi;
    private $entityManager;
    private $moneyFormatter;

    public function __construct(
        TelegramApiInterface $telegramApi,
        EntityManagerInterface $entityManager,
        IntlMoneyFormatter $moneyFormatter
    ) {
        $this->telegramApi = $telegramApi;
        $this->entityManager = $entityManager;
        $this->moneyFormatter = $moneyFormatter;
    }

    public function __invoke(RequestPlanUpgrade $message): void
    {
        $user = $message->getUser();
        $plans = $this->entityManager->getRepository(Plan::class)->findAllFiltered(!$user->isDev());

        $buttons = [
            array_map(function (Plan $plan) {
                return ['callback_data' => '/upgrade ' . $plan->getName(), 'text' => $plan->getName()];
            }, $plans)
        ];
        $buttons[] = [['callback_data' => '/upgrade cancel', 'text' => 'Cancel']];

        $keyboard = [
            'inline_keyboard' => $buttons,
            'one_time_keyboard' => true,
        ];

        $text = sprintf("Current plan: <b>%s</b>\n", $user->getCurrentPlan() ? $user->getCurrentPlan()->getName() : 'Free');
        $text .= 'Select your plan:';
        /** @var string $text */
        $text = array_reduce($plans, function (string $text, Plan $plan) {
            $row = sprintf(
                "\n<b>%s</b> - %s / month, %s %s, frequency %s",
                $plan->getName(),
                $this->moneyFormatter->format($plan->getPrice()),
                $plan->getSearchCount(),
                $plan->getSearchCount() === 1 ? 'search' : 'searches',
                $plan->getUpdateFrequency()
            );
            return $text . $row;
        }, $text);

        $user = $message->getUser();

        $this->telegramApi->sendMessage($user->getTelegramRef(), $text, $keyboard);
    }
}
