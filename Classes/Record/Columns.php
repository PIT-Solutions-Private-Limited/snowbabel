<?php
namespace PITS\Snowbabel\Record;

/*
 *  Copyright notice
 *
 *  (c) 2011 Daniel Alder <info@snowflake.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

use PITS\Snowbabel\Service\Configuration;

/**
 * Class Columns.
 */
class Columns
{
    /**
     * @var \PITS\Snowbabel\Service\Configuration
     */
    protected $confObj;

    /**
     * @var
     */
    protected $ColumnsConfiguration;

    /**
     * @var array
     */
    protected $Columns = [];

    /**
     * @param Configuration $confObj
     */
    public function __construct($confObj)
    {
        $this->confObj = $confObj;

        // get Application params

        // get Extension params

        // get User parasm
        $this->ColumnsConfiguration = $this->confObj->getUserConfigurationColumns();

        $this->initColumns();
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        // get columns
        return $this->Columns;
    }

    /**
     * @return void
     */
    private function initColumns()
    {
        if (\is_array($this->ColumnsConfiguration)) {
            foreach ($this->ColumnsConfiguration as $Id => $Property) {
                $Label = $this->getColumnLabel($Id);

                array_push($this->Columns, [
                    'ColumnId' => $Id,
                    'ColumnName' => $Label,
                    'ColumnSelected' => $Property,
                ]);
            }
        }
    }

    /**
     * @param $Id
     *
     * @return string
     */
    private function getColumnLabel($Id)
    {
        $LabelName = 'translation_columnselection_'.$Id;

        return $this->confObj->getLL($LabelName);
    }
}
