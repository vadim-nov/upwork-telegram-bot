<?php


namespace App\Domain\Core\Dto;

final class PlanOutput
{
    public $name;
    public $price;
    public $searchCount;
    public $updateFrequency;
    public $isCurrent;
    public $subTitle;

    public function __construct(string $name, string $price, int $searchCount, int $updateFrequency, bool $isCurrent)
    {
        $this->name = $name;
        $this->price = $price;
        $this->searchCount = $searchCount;
        $this->updateFrequency = $updateFrequency;
        $this->isCurrent = $isCurrent;
        if($updateFrequency===1){
            $this->subTitle = 'Every minute update';
        }else{
            $this->subTitle = "Every {$this->updateFrequency} mins update";
        }
    }
}
