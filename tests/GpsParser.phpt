<?php

use Tester\Assert;
use Teddy\Gps;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../lib/GpsParser.php';
require __DIR__ . '/../lib/Gps.php';
require __DIR__ . '/../lib/InvalidGpsFormatException.php';

Tester\Environment::setup();


class GpsParserTest extends Tester\TestCase
{

    // Parsing
    public function testOne()
    {
        $inputs = array(
            array(12.5, -32.754),
            array(6.2, 5.8),
            array('-12,3', '32.5'),
            array('0', '5'),
            array('S 26.12312', 'E 12.12312'),
            array('S 16.12312', 'W 12.543'),
            array('N 26° 42\' 45"', 'E 25° 42\' 32"'),
            array('N 26° 42\' 45', 'E 25° 42\' 32'),
            array('N 26 42 45', 'E 25° 42 32'),
            array('N 26° 42.75', 'E 25° 42\' 32"'),
            array('N 26° 42,75', 'E 25° 42\' 32"'),
        );
        $expected = array(
            new Gps\Gps(12.500000, -32.754000),
            new Gps\Gps(6.200000, 5.800000),
            new Gps\Gps(-12.300000, 32.500000),
            new Gps\Gps(0.000000, 5.000000),
            new Gps\Gps(-26.123120, 12.123120),
            new Gps\Gps(-16.123120, -12.543000),
            new Gps\Gps(26.712500, 25.708889),
            new Gps\Gps(26.712500, 25.708889),
            new Gps\Gps(26.712500, 25.708889),
            new Gps\Gps(26.712500, 25.708889),
            new Gps\Gps(26.712500, 25.708889),
        );
        foreach($inputs as $key => $input) {
            $parser = new Gps\GpsParser($input[0], $input[1]);
            $output = $parser->parse();
            Assert::equal($expected[$key], $output);
        }
    }

    // Check order
    public function testTwo()
    {
        // Should swap
        $inputs = array(
            array('W 26.12312', 'N 12.12312'),
            array('E 26° 42,75', 'N 25° 42\' 32"'),
        );
        $expected = array(
            new Gps\Gps(12.123120, -26.123120),
            new Gps\Gps(25.708889, 26.712500),
        );
        foreach($inputs as $key => $input) {
            $parser = new Gps\GpsParser($input[0], $input[1]);
            $output = $parser->parse();
            Assert::equal($expected[$key], $output);
        }

        // Disabled order checking
        $parser = new Gps\GpsParser('W 26.12312', 'N 12.12312');
        $parser->setCheckOrder(false);
        Assert::exception(function() use ($parser) {
            $parser->parse();
        }, 'Teddy\Gps\InvalidGpsFormatException');
    }

    // Wrong data
    public function testThree()
    {
        $inputs = array(
            array('W 1226.12312', 'N 12.12312'),
            array('E -1226° 42,75', 'N 25° 42\' 32"'),
            array('blabla', 'test'),
            array('trol', ''),
        );
        foreach($inputs as $input) {
            Assert::exception(function() use ($input) {
                $parser = new Gps\GpsParser($input[0], $input[1]);
                $parser->parse();
            }, 'Teddy\Gps\InvalidGpsFormatException');
        }
    }

    // Custom cardinalDirections
    public function testFour()
    {
        $inputs = array(
            array('S 26.12312', 'E 12.12312'),
            array('J 26.12312', 'V 12.12312'),
            array('N 26 42 45', 'E 25° 42 32'),
            array('S 26° 42,75', 'V 25° 42\' 32"'),
        );
        $expected = array(
            new Gps\Gps(-26.123120, 12.123120),
            new Gps\Gps(-26.123120, 12.123120),
            new Gps\Gps(26.712500, 25.708889),
            new Gps\Gps(26.712500, 25.708889),
        );
        foreach($inputs as $key => $input) {
            $parser = new Gps\GpsParser($input[0], $input[1]);
            $parser->addCardinalDirections('S', 'J', 'V', 'Z');
            $output = $parser->parse();
            Assert::equal($expected[$key], $output);
        }
    }

}

$testCase = new GpsParserTest();
$testCase->run();
