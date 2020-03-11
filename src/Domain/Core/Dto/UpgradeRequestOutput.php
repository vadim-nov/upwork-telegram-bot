<?php


namespace App\Domain\Core\Dto;

final class UpgradeRequestOutput
{
    public $url;
    public $id;
    public $price;
    public $isPaid;
    public $plan;

    public function __construct(string $id, string $plan, string $price, string $url, bool $idPaid)
    {
        $this->url = $url;
        $this->id = $id;
        $this->price = $price;
        $this->isPaid = $idPaid;
        $this->plan = $plan;
    }
}
