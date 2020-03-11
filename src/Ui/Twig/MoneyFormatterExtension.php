<?php


namespace App\Ui\Twig;


use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MoneyFormatterExtension extends AbstractExtension
{
    private $moneyFormatter;

    public function __construct(IntlMoneyFormatter $moneyFormatter)
    {
        $this->moneyFormatter = $moneyFormatter;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('format_money', [$this, 'formatMoney']),
        ];
    }

    public function formatMoney(Money $value)
    {
        return $this->moneyFormatter->format($value);
    }
}
