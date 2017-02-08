<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Tests\Type;

use Mongator\Type\DateType;
use MongoDB\BSON\UTCDateTime;

class DateTypeTest extends TestCase
{
    public function testToMongo()
    {
        $type = new DateType();

        $time = time();
        $this->assertEquals(new UTCDateTime($time), $type->toMongo($time));

        $date = new \DateTime();
        $date->setTimestamp($time);
        $this->assertEquals(new UTCDateTime($time), $type->toMongo($date));

        $string = '2010-02-20';
        $this->assertEquals(new UTCDateTime(strtotime($string)), $type->toMongo($string));
    }

    public function testToPHP()
    {
        $type = new DateType();

        $time = time();
        $date = new \DateTime();
        $date->setTimestamp($time);

        $this->assertEquals($date, $type->toPHP(new UTCDateTime($time)));
    }

    public function testToMongoInString()
    {
        $type = new DateType();
        $function = $this->getTypeFunction($type->toMongoInString());

        $time = time();
        $this->assertEquals(new UTCDateTime($time), $function($time));

        $date = new \DateTime();
        $date->setTimestamp($time);
        $this->assertEquals(new UTCDateTime($time), $function($date));

        $string = '2010-02-20';
        $this->assertEquals(new UTCDateTime(strtotime($string)), $function($string));
    }

    public function testToPHPInString()
    {
        $type = new DateType();
        $function = $this->getTypeFunction($type->toPHPInString());

        $time = time();
        $date = new \DateTime();
        $date->setTimestamp($time);

        $this->assertEquals($date, $function(new UTCDateTime($time)));
    }
}
