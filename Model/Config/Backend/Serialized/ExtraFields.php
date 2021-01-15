<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Config\Backend\Serialized;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;

/**
 * Extra Fields BeforeSave data refomat and unset
 */
class ExtraFields extends ArraySerialized
{

    /**
     * Reformat "Extra Fields" and uset unused.
     *
     * @return ArraySerialized
     */
    public function beforeSave()
    {
        $data = $this->getValue();
        /** @phpstan-ignore-next-line */
        if (is_array($data)) {
            foreach ($data as $key => $row) {
                if (empty($row['name']) || empty($row['attribute'])) {
                    unset($data[$key]);
                    continue;
                }
            }
        }
        $this->setValue($data);
        return parent::beforeSave();
    }
}
