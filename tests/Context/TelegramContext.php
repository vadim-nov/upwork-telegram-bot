<?php

namespace App\Tests\Context;

use Behatch\Context\RestContext;

class TelegramContext extends RestContext
{
    /**
     * @Given I send a message to bot with text :text
     */
    public function iSendAMessageToBotWithText($message)
    {
        $body = '
{
  "update_id": 160037896,
  "message": {
    "message_id": 1373,
    "from": {
      "id": 569776780,
      "is_bot": false,
      "first_name": "Vadim",
      "username": "vadim_n0v",
      "language_code": "en"
    },
    "chat": {
      "id": 569776780,
      "first_name": "Vadim",
      "username": "vadim_n0v",
      "type": "private"
    },
    "date": 1554733423,
    "text": "/invalid"
  }
}';

        $body = json_decode($body, true);
        $body['message']['text'] = $message;
        $body = json_encode($body);

        return $this->request->send('POST', $this->locatePath('/clb/telegram/test'), [], [], $body);
    }

    /**
     * @Given I send a message to bot with callback query :text
     */
    public function iSendAMessageToBotWithCallbackQuery($callbackQuery)
    {
        $body = '
{
  "update_id": 160037896,
  "callback_query": {
    "message": {
      "message_id": 1373,
      "from": {
        "id": 569776780,
        "is_bot": false,
        "first_name": "Vadim",
        "username": "vadim_n0v",
        "language_code": "en"
      },
      "chat": {
        "id": 569776780,
        "first_name": "Vadim",
        "username": "vadim_n0v",
        "type": "private"
      },
      "date": 1554733423
    },
    "from": {
      "id": 569776780,
      "is_bot": false,
      "first_name": "Vadim",
      "username": "vadim_n0v",
      "language_code": "en"
     },
    "data": "/invalid"
  }
}';

        $body = json_decode($body, true);
        $body['callback_query']['data'] = $callbackQuery;
        $body = json_encode($body);

        return $this->request->send('POST', $this->locatePath('/clb/telegram/test'), [], [], $body);
    }
}