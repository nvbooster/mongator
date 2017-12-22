<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Type;

use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;


/**
 * ArrayObjectType.
 *
 * Parent class for array and object types
 *
 * @author Adam.Balint <adam.balint@srg.hu>
 *
 * @api
 */
abstract class ArrayObjectType extends Type
{

    /**
     * Convert BSONDocument and BSONArray to array recursively
     *
     * @param mixed $value
     * @return mixed
     */
    static function BSONToArrayRecursive($value){
        if($value instanceof BSONDocument || $value instanceof BSONArray) {
            $value = $value->getArrayCopy();
        }
        if(is_array($value)){
            foreach($value as $key => $item){
                if(is_array($item) || $item instanceof BSONDocument || $item instanceof BSONArray){
                    $value[$key] = self::BSONToArrayRecursive($item);
                }
            }
        }
        return $value;
    }
}