<?php

declare(strict_types=1);

namespace App\Infrastructure\Integration;

use ApiPlatform\Core\Api\UrlGeneratorInterface;
use App\Domain\Payment\PaymentCallback;
use App\Domain\Payment\PaymentGatewayInterface;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;

class Payeer implements PaymentGatewayInterface
{
    private $shopId;
    private $secretKey;
    private $appHost;
    private $decimalMoneyParser;
    private $decimalMoneyFormatter;

    public function __construct(
        DecimalMoneyParser $decimalMoneyParser,
        DecimalMoneyFormatter $decimalMoneyFormatter
    ) {
        $this->shopId = getenv('PAYEER_SHOP_ID');
        $this->secretKey = getenv('PAYEER_SECRET_KEY');
        $this->appHost = getenv('APP_HOST');
        $this->decimalMoneyParser = $decimalMoneyParser;
        $this->decimalMoneyFormatter = $decimalMoneyFormatter;
    }

    public function generatePaymentPageUrl(string $orderId, Money $money): string
    {
        $currency = $money->getCurrency()->getCode();
        $amount = $this->decimalMoneyFormatter->format($money);
        $description = base64_encode('Order ID: ' . $orderId);

        $hash = [
            $this->shopId,
            $orderId,
            $amount,
            $currency,
            $description,
        ];

        $host = rtrim($this->appHost, '/');
        $callbackUrls = [
            'success_url' => $host . '/clb/payeer/success',
            'fail_url' => $host . '/clb/payeer/fail',
            'status_url' => $host . '/clb/payeer/status',
        ];
        $key = md5($this->secretKey . $orderId);
        $additionalParams = urlencode(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, json_encode($callbackUrls), MCRYPT_MODE_ECB)));
        $hash[] = $additionalParams;
        $hash[] = $this->secretKey;

        $sign = strtoupper(hash('sha256', implode(':', $hash)));

        $queryParams = [
            'm_shop' => $this->shopId,
            'm_orderid' => $orderId,
            'm_amount' => $amount,
            'm_curr' => $currency,
            'm_desc' => $description,
            'm_sign' => $sign,
            'm_params' => $additionalParams,
            'm_process' => 'send'
        ];

        return 'https://payeer.com/merchant/?' . http_build_query($queryParams);
    }

    public function parsePaymentCallback(array $body): PaymentCallback
    {
        $callbackHash = [
            $body['m_operation_id'],
            $body['m_operation_ps'],
            $body['m_operation_date'],
            $body['m_operation_pay_date'],
            $body['m_shop'],
            $body['m_orderid'],
            $body['m_amount'],
            $body['m_curr'],
            $body['m_desc'],
            $body['m_status'],
            $this->secretKey
        ];

        $signHash = strtoupper(hash('sha256', implode(':', $callbackHash)));
        $orderId = $body['m_orderid'];
        $isPaymentSuccess = $body['m_sign'] === $signHash && $body['m_status'] === 'success';
        if ($isPaymentSuccess) {
            $money = $this->decimalMoneyParser->parse($body['m_amount'], $body['m_curr']);
        } else {
            $money = null;
        }

        return new PaymentCallback($orderId, $isPaymentSuccess, $money);
    }
}