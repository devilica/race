<?php

use App\Controller\CsvImportController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CsvImportControllerTest extends WebTestCase
{


    public function testCalculateAverageTime(): void
    {
        $controller = new CsvImportController();

        $raceTimes = ['06:04:12', '01:20:00', '02:09:31'];

        $expectedAverageTime = '03:11:14';
        $actualAverageTime = $controller->calculateAverageTime($raceTimes);

        $this->assertSame($expectedAverageTime, $actualAverageTime);
    }
}