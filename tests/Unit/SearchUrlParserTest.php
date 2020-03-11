<?php


namespace App\Tests\Unit;


use App\Domain\Upwork\Exception\InvalidUpworkUrlException;
use App\Domain\Upwork\SearchUrlParser;
use PHPUnit\Framework\TestCase;

class SearchUrlParserTest extends TestCase
{
    public function testEmptyQ(){
        $url = 'https://www.upwork.com/search/jobs';
        $parser = new SearchUrlParser();
        $result = $parser->parse($url);
        $this->assertEquals('', $result->getQuery());
    }

    public function testNormalQ(){
        $url = 'https://www.upwork.com/search/jobs/?q=react&sort=recency';
        $parser = new SearchUrlParser();
        $result = $parser->parse($url);
        $this->assertEquals('react', $result->getQuery());
        $this->assertEquals('recency', $result->getSort());
    }

    public function testSkill(){
        $url = 'https://www.upwork.com/search/jobs/skill/mobile-app-development/';
        $parser = new SearchUrlParser();
        $result = $parser->parse($url);
        $this->assertEquals('mobile-app-development', $result->getSkill());
    }

    public function testInvalidUrl(){
        $url = 'https://www.invalid.com/';
        $parser = new SearchUrlParser();
        $this->expectException(InvalidUpworkUrlException::class);
        $parser->assertValidSearchUrl($url);
    }
}
