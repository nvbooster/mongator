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
use MongoDB\Model\BSONDocument;

/**
 * ObjectType.
 *
 * @author Adam.Balint <adam.balint@srg.hu>
 *
 * @api
 */
class ObjectType extends ArrayObjectType
{
    /**
     * {@inheritdoc}
     */
    public function toMongo($value)
    {
    	if(empty($value)) $value = [];
    	else if(!is_array($value)) $value = [$value];

    	return new BSONDocument($value);
    }

    /**
     * {@inheritdoc}
     */
    public function toPHP($value)
    {
		/**
		 * @var $value BSONDocument
		 */
        return ArrayObjectType::BSONToArrayRecursive($value);
    }

    /**
     * {@inheritdoc}
     */
    public function toMongoInString()
    {
		return '%to% = %from%; if (empty(%to%)) { %to% = []; } elseif (!is_array(%to%)) { %to% = [%to%]; } %to% = new \MongoDB\Model\BSONDocument(%to%);';
    }

    /**
     * {@inheritdoc}
     */
    public function toPHPInString()
    {
        return '%to% = \Mongator\Type\ArrayObjectType::BSONToArrayRecursive(%from%);';
    }
}
