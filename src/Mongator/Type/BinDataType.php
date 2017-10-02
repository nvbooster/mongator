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

/**
 * BinDataType.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 *
 * @api
 */
class BinDataType extends Type
{
    /**
     * {@inheritdoc}
     */
    public function toMongo($value)
    {
        if (is_file($value)) {
            $value = file_get_contents($value);
        }

        return new \MongoDB\BSON\Binary($value,  \MongoDB\BSON\Binary::TYPE_GENERIC);
    }

    /**
     * {@inheritdoc}
     */
    public function toPHP($value)
    {
        return $value->bin;
    }

    /**
     * {@inheritdoc}
     */
    public function toMongoInString()
    {
        return 'if (is_file(%from%)) { %from% = file_get_contents(%from%); } %to% = new \MongoDB\BSON\Binary(%from%,  \MongoDB\BSON\Binary::TYPE_GENERIC);';
    }

    /**
     * {@inheritdoc}
     */
    public function toPHPInString()
    {
        return '%to% = %from%->bin;';
    }
}
