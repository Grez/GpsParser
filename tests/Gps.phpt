<?php

use Tester\Assert;
use Teddy\Gps;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../lib/Gps.php';
require __DIR__ . '/../lib/InvalidGpsFormatException.php';

Tester\Environment::setup();


class GpsTest extends Tester\TestCase
{

    // Different formats
    public function testOne()
    {
        $inputs = array(
            new Gps\Gps('5.213120', '-12.123120'),
            new Gps\Gps('-26.712500', '25.708889'),
        );

        $expected = array(
            'decimal' => array(
                array('lat' => '5.213120', 'lon' => '-12.123120'),
                array('lat' => '-26.712500', 'lon' => '25.708889'),
            ),
            'degrees' => array(
                array('lat' => 'N 5° 12\' 47.232"', 'lon' => 'W 12° 7\' 23.232"'),
                array('lat' => 'S 26° 42\' 45.000"', 'lon' => 'E 25° 42\' 32.000"'),
            ),
            'noSeconds' => array(
                array('lat' => 'N 5° 12.7872\'', 'lon' => 'W 12° 7.3872\''),
                array('lat' => 'S 26° 42.7500\'', 'lon' => 'E 25° 42.5333\''),
            ),
        );
        foreach($inputs as $key => $input) {
            Assert::equal($expected['decimal'][$key], $input->getDecimal());
        }
    }
}

$testCase = new GpsTest();
$testCase->run();
