<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 10/04/2019
 * Time: 17:22
 */

namespace App\Application\TelegramBot\Factory;


use App\Application\TelegramBot\Message\AddSearch\AddSearchStopWords;
use App\Domain\Core\Entity\User;
use App\Application\TelegramBot\Message\AddSearch\AddSearchName;
use App\Application\TelegramBot\Message\AddSearch\AddSearchStart;
use App\Application\TelegramBot\Message\AddSearch\AddSearchLink;
use App\Application\TelegramBot\Message\HelpMessage;
use App\Application\TelegramBot\Message\ListSearchesMessage;
use App\Application\TelegramBot\Message\Payment\PlaceOrder;
use App\Application\TelegramBot\Message\Payment\RequestPlanUpgrade;
use App\Application\TelegramBot\Message\RemoveSearch\RemoveSearchByName;
use App\Application\TelegramBot\Message\RemoveSearch\RemoveSearchRequested;
use App\Application\TelegramBot\Message\StartMessage;
use App\Application\TelegramBot\Message\TelegramMessage;
use App\Application\TelegramBot\Message\UnrecognizedMessage;
use App\Domain\TelegramBot\UI\TelegramButton;
use App\Domain\TelegramBot\ValueObject\TelegramUserStructure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Workflow\Registry;

class TelegramMessageFactory
{
    private $entityManager;
    private $workflows;

    public function __construct(EntityManagerInterface $entityManager, Registry $workflows)
    {
        $this->entityManager = $entityManager;
        $this->workflows = $workflows;
    }

    public function createFromRequestBody($body): TelegramMessage
    {
        $jsonResponse = json_decode($body, true);
        if (isset($jsonResponse['callback_query'])) {
            $userStructure = new TelegramUserStructure($jsonResponse['callback_query']['from']['id']);
            $userStructure->completeProfile($jsonResponse['callback_query']['from']);
            $messageId = $jsonResponse['callback_query']['message']['message_id'];
            $messageText = $jsonResponse['callback_query']['data'];
        } elseif (isset($jsonResponse['edited_message'])) {
            $userStructure = new TelegramUserStructure($jsonResponse['edited_message']['from']['id']);
            $userStructure->completeProfile($jsonResponse['edited_message']['from']);
            $messageId = $jsonResponse['edited_message']['message_id'];
            $messageText = $jsonResponse['edited_message']['text'];
        } else {
            $userStructure = new TelegramUserStructure($jsonResponse['message']['from']['id']);
            $userStructure->completeProfile($jsonResponse['message']['from']);
            $messageId = $jsonResponse['message']['message_id'];
            $messageText = $jsonResponse['message']['text'] ?? '';
        }

        $user = $this->getUserFromTelegram($userStructure);

        if (null === $messageText) {
            return new UnrecognizedMessage($messageId, '', $user);
        }

        $workflow = $this->workflows->get($user);

        if ($this->startsWithOne($messageText, [TelegramMessage::REMOVE_SEARCH_COMMAND, TelegramButton::REMOVE])) {
            $this->resetAddingSearchProcessIfNeeded($user);
            $textArray = explode(' ', $messageText);
            if (sizeof($textArray) >= 2 && !empty($jsonResponse['callback_query'])) {
                array_shift($textArray);
                $searchId = implode(' ', $textArray);
                return new RemoveSearchByName($messageId, $messageText, $user, $searchId,$searchId === 'cancel');
            }
            return new RemoveSearchRequested($messageId, $messageText, $user);
        }

        if ($this->startsWithOne($messageText, [TelegramMessage::PLAN, TelegramButton::UPGRADE])) {
            $this->resetAddingSearchProcessIfNeeded($user);
            $planName = explode(' ', $messageText)[1] ?? null;
            // TODO: fix this workarounds
            if (!$planName || $planName === 'Upgrade') {
                return new RequestPlanUpgrade($messageId, $messageText, $user);
            }
            return new PlaceOrder($messageId, $messageText, $user, $planName, $planName === 'cancel');
        }
        if ($this->startsWithOne($messageText, [TelegramMessage::START_COMMAND])) {
            $this->resetAddingSearchProcessIfNeeded($user);
            return new StartMessage($messageId, $messageText, $user);
        }
        if ($this->startsWithOne($messageText, [TelegramMessage::HELP_COMMAND, TelegramButton::HELP])) {
            $this->resetAddingSearchProcessIfNeeded($user);
            $helpMessage = new HelpMessage($messageId, $messageText, $user);
            $helpMessage->setIsWithAnimation(true);
            return $helpMessage;
        }
        if ($this->startsWithOne($messageText, [TelegramMessage::LIST_SEARCH_COMMAND, TelegramButton::LIST])) {
            $this->resetAddingSearchProcessIfNeeded($user);
            return new ListSearchesMessage($messageId, $messageText, $user);
        }

        if ($this->startsWithOne($messageText, [TelegramMessage::ADD_SEARCH_COMMAND, TelegramButton::ADD])) {
            $this->resetAddingSearchProcessIfNeeded($user);
        }

        if ($workflow->can($user, 'add_search_stop_words')) {
            return new AddSearchStopWords($messageId, $messageText, $user);
        }
        if ($workflow->can($user, 'add_search_name')) {
            return new AddSearchName($messageId, $messageText, $user);
        }
        if ($workflow->can($user, 'add_search_link')) {
            return new AddSearchLink($messageId, $messageText, $user);
        }
        if ($workflow->can($user, 'add_new_search')
            && $this->startsWithOne($messageText, [TelegramMessage::ADD_SEARCH_COMMAND, TelegramButton::ADD])) {
            return new AddSearchStart($messageId, $messageText, $user);
        }

        return new UnrecognizedMessage($messageId, $messageText, $user);
    }

    private function resetAddingSearchProcessIfNeeded(User $user): void
    {
        if ($user->getCurrentPlace() && 'start' !== $user->getCurrentPlace()) {
            $workflow = $this->workflows->get($user);
            $workflow->apply($user, 'add_seach_cancel');
        }
    }

    private function getUserFromTelegram(TelegramUserStructure $userStructure): User
    {
        $userRepo = $this->entityManager->getRepository(User::class);
        $user = $userRepo->findByTelegramRef($userStructure->getId());
        if (empty($user)) {
            $user = User::createFromTelegram($userRepo->nextIdentity(), $userStructure);
            $userRepo->add($user);
            $this->entityManager->flush();
        }

        return $user;
    }

    private function startsWithOne(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (mb_strpos($haystack, $needle) === 0) {
                return true;
            }
        }
        return false;
    }
}
