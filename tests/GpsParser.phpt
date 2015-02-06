<?php

use Tester\Assert;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../lib/GpsParser.php';
require __DIR__ . '/../lib/InvalidGpsFormatException.php';

Tester\Environment::setup();

class GpsParserTest extends Tester\TestCase
{
    // Parsování
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
            array('lat' => '12.500000', 'lon' => '-32.754000'),
            array('lat' => '6.200000', 'lon' => '5.800000'),
            array('lat' => '-12.300000', 'lon' => '32.500000'),
            array('lat' => '0.000000', 'lon' => '5.000000'),
            array('lat' => '-26.123120', 'lon' => '12.123120'),
            array('lat' => '-16.123120', 'lon' => '-12.543000'),
            array('lat' => '26.712500', 'lon' => '25.708889'),
            array('lat' => '26.712500', 'lon' => '25.708889'),
            array('lat' => '26.712500', 'lon' => '25.708889'),
            array('lat' => '26.712500', 'lon' => '25.708889'),
            array('lat' => '26.712500', 'lon' => '25.708889'),
        );
        foreach($inputs as $key => $input) {
            $parser = new GpsParser($input[0], $input[1]);
            $output = $parser->parse();
            Assert::equal($expected[$key]['lat'], $output['lat']);
            Assert::equal($expected[$key]['lon'], $output['lon']);
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
            array('lat' => '12.123120', 'lon' => '-26.123120'),
            array('lat' => '25.708889', 'lon' => '26.712500'),
        );
        foreach($inputs as $key => $input) {
            $parser = new GpsParser($input[0], $input[1]);
            $output = $parser->parse();
            Assert::equal($expected[$key]['lat'], $output['lat']);
            Assert::equal($expected[$key]['lon'], $output['lon']);
        }

        // Disabled order checking
        $parser = new GpsParser('W 26.12312', 'N 12.12312');
        $parser->setCheckOrder(false);
        Assert::exception(function() use ($parser) {
            $parser->parse();
        }, 'InvalidGpsFormatException');
    }

    // Shitty data
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
                $parser = new GpsParser($input[0], $input[1]);
                $parser->parse();
            }, 'InvalidGpsFormatException');
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
            array('lat' => '-26.123120', 'lon' => '12.123120'),
            array('lat' => '-26.123120', 'lon' => '12.123120'),
            array('lat' => '26.712500', 'lon' => '25.708889'),
            array('lat' => '26.712500', 'lon' => '25.708889'),
        );
        foreach($inputs as $key => $input) {
            $parser = new GpsParser($input[0], $input[1]);
            $parser->addCardinalDirections('S', 'J', 'V', 'Z');
            $output = $parser->parse();
            Assert::equal($expected[$key]['lat'], $output['lat']);
            Assert::equal($expected[$key]['lon'], $output['lon']);
        }
    }

    // Different formats
    public function testFive()
    {
        $inputs = array(
            array('5.21312', '-12.12312'),
            array('S 26° 42\' 45"', 'E 25° 42\' 32"'),
        );
        $expected = array(
            'decimal' => array(
                array('5.213120', '-12.123120'),
                array('-26.712500', '25.708889'),
            ),
            'degrees' => array(
                array('N 5° 12\' 47.232"', 'W 12° 7\' 23.232"'),
                array('S 26° 42\' 45.000"', 'E 25° 42\' 32.000"'),
            ),
            'noSeconds' => array(
                array('N 5° 12.7872\'', 'W 12° 7.3872\''),
                array('S 26° 42.7500\'', 'E 25° 42.5333\''),
            ),
        );
        foreach($inputs as $key => $input) {
            $parser = new GpsParser($input[0], $input[1]);
            $output = $parser->parse();
            Assert::equal($expected['decimal'][$key][0], $output['lat']);
            Assert::equal($expected['decimal'][$key][1], $output['lon']);

            $parser = new GpsParser($input[0], $input[1]);
            $output = $parser->parse('degrees');
            Assert::equal($expected['degrees'][$key][0], $output['lat']);
            Assert::equal($expected['degrees'][$key][1], $output['lon']);

            $parser = new GpsParser($input[0], $input[1]);
            $output = $parser->parse('noSeconds');
            Assert::equal($expected['noSeconds'][$key][0], $output['lat']);
            Assert::equal($expected['noSeconds'][$key][1], $output['lon']);
        }
    }
}

$testCase = new GpsParserTest();
$testCase->run();