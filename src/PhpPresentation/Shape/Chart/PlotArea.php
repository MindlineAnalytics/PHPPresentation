<?php
/**
 * This file is part of PHPPresentation - A pure PHP library for reading and writing
 * presentations documents.
 *
 * PHPPresentation is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPPresentation/contributors.
 *
 * @see        https://github.com/PHPOffice/PHPPresentation
 *
 * @copyright   2009-2015 PHPPresentation contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

declare(strict_types=1);

namespace PhpOffice\PhpPresentation\Shape\Chart;

use PhpOffice\PhpPresentation\ComparableInterface;
use PhpOffice\PhpPresentation\Exception\UndefinedChartTypeException;
use PhpOffice\PhpPresentation\Shape\Chart\Type\AbstractType;

/**
 * \PhpOffice\PhpPresentation\Shape\Chart\PlotArea.
 */
class PlotArea implements ComparableInterface
{
    /**
     * Plot areas chart types.
     *
     * @var array<string,AbstractType>
     */
    private $chartTypes = array();

    /**
     * Primary X-axis.
     *
     * @var Axis
     */
    private $primaryAxisX;

    /**
     * Primary Y-axis.
     *
     * @var Axis
     */
    private $primaryAxisY;

    /**
     * Secondary X-axis.
     *
     * @var Axis|null
     */
    private $secondaryAxisX;

    /**
     * Secondary Y-axis.
     *
     * @var Axis|null
     */
    private $secondaryAxisY;

    /**
     * OffsetX (as a fraction of the chart).
     *
     * @var float
     */
    private $offsetX = 0;

    /**
     * OffsetY (as a fraction of the chart).
     *
     * @var float
     */
    private $offsetY = 0;

    /**
     * Width (as a fraction of the chart).
     *
     * @var float
     */
    private $width = 0;

    /**
     * Height (as a fraction of the chart).
     *
     * @var float
     */
    private $height = 0;

    public function __construct()
    {
        $this->primaryAxisX = new Axis(Axis::AXIS_X, Axis::AXIS_TYPE_CATEGORY, true);
        $this->primaryAxisY = new Axis(Axis::AXIS_Y, Axis::AXIS_TYPE_VALUE, true);
    }

    public function __clone()
    {
        $this->primaryAxisX = clone $this->primaryAxisX;
        $this->primaryAxisY = clone $this->primaryAxisY;

        if ($this->secondaryAxisX !== null) {
            $this->secondaryAxisX = clone $this->secondaryAxisX;
        }
        if ($this->secondaryAxisY !== null) {
            $this->secondaryAxisY = clone $this->secondaryAxisY;
        }
    }

    /**
     * @return AbstractType|null
     * @throws UndefinedChartTypeException
     * 
     * @deprecated 1.1.1 No longer used, as PlotArea now supports multiple chart types, use `getTypes` instead.
     */
    public function getType(): ?AbstractType
    {
        if (empty($this->chartTypes)) {
            throw new UndefinedChartTypeException();
        }

        return reset($this->chartTypes);
    }

    /**
     * @return self
     * 
     * @deprecated 1.1.1 No longer used, as PlotArea now supports multiple chart types, use `addType` instead.
     */
    public function setType(AbstractType $value): self
    {
        $this->addType($value);

        return $this;
    }

    /**
     * Get array of plot areas chart types
     * 
     * @return array<string,AbstractType>
     */
    public function getTypes(): array
    {
        return $this->chartTypes;
    }

    /**
     * Adds or replaces a chart type to the plot area.
     * 
     * If the chart uses a secondary axis and a secondary axis is not already present, it will be instantiated.
     * 
     * @param AbstractType $subject
     * 
     * @return self
     */
    public function addType(AbstractType $subject): self
    {
        $chartType = (new \ReflectionClass($subject))->getShortName();

        if (!is_null($this->chartTypes[$chartType] ?? null)) {
            $this->removeType($chartType);
        }

        if ($subject->isOnPrimaryAxis() === false && $this->hasSecondaryAxis() === false) {
            if ( $subject instanceof Type\Scatter) {
                $this->secondaryAxisX = new Axis(Axis::AXIS_X, Axis::AXIS_TYPE_VALUE, false);
            } else {
                $this->secondaryAxisX = new Axis(Axis::AXIS_X, Axis::AXIS_TYPE_CATEGORY, false);
            }
            $this->secondaryAxisY = new Axis(Axis::AXIS_Y, Axis::AXIS_TYPE_VALUE, false);
        }

        $this->chartTypes[$chartType] = $subject;

        return $this;
    }

    /**
     * Removes a chart type from plot area
     * 
     * @param string $chartType
     * 
     * @return self
     */
    public function removeType(string $chartType): self
    {
        $subject = $this->chartTypes[$chartType] ?? null;

        if (is_null($subject)) {
            return $this;
        }

        unset($this->chartTypes[$chartType]);
        
        if ($subject->isOnPrimaryAxis() === false && $this->hasSecondaryAxis() === false) {
            $this->secondaryAxisX = null;
            $this->secondaryAxisY = null;
        }

        return $this;
    }

    /**
     * Checks weather plot area uses a primary or secondary axis of type x/y
     * 
     * @param bool $primary
     * @param string $axisType
     * 
     * @return bool
     */
    public function hasAxis(bool $primary = true, string $axisType = Axis::AXIS_X): bool
    {
        foreach ($this->chartTypes as $type) {
            if ($type->isOnPrimaryAxis() === $primary && (
                    ($axisType === Axis::AXIS_X && $type->hasAxisX())
                    || ($axisType === Axis::AXIS_Y && $type->hasAxisY())
            )) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks weather plot area uses a secondary axis
     */
    public function hasSecondaryAxis(): bool
    {
        foreach ($this->chartTypes as $type) {
            if( $type->isOnPrimaryAxis() === false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get primary X-axis.
     * 
     * @return Axis
     * 
     * @deprecated 1.1.1 No longer used, as PlotArea now supports primary/secondary axes, use `getPrimaryAxisX` instead.
     */
    public function getAxisX(): Axis
    {
        return $this->primaryAxisX;
    }

    /**
     * Get primary X-axis.
     * 
     * @return Axis
     */
    public function getPrimaryAxisX(): Axis
    {
        return $this->primaryAxisX;
    }

    /**
     * Get primary Y-axis.
     * 
     * @return Axis
     * 
     * @deprecated 1.1.1 No longer used, as PlotArea now supports primary/secondary axes, use `getPrimaryAxisY` instead.
     */
    public function getAxisY(): Axis
    {
        return $this->primaryAxisY;
    }

    /**
     * Get primary Y-axis.
     * 
     * @return Axis
     */
    public function getPrimaryAxisY(): Axis
    {
        return $this->primaryAxisY;
    }

    /**
     * Get secondary X-axis.
     * 
     * @return Axis|null
     */
    public function getSecondaryAxisX(): ?Axis
    {
        return $this->secondaryAxisX;
    }

    /**
     * Get secondary Y-axis.
     * 
     * @return Axis|null
     */
    public function getSecondaryAxisY(): ?Axis
    {
        return $this->secondaryAxisY;
    }

    /**
     * Get OffsetX (as a fraction of the chart).
     */
    public function getOffsetX(): float
    {
        return $this->offsetX;
    }

    /**
     * Set OffsetX (as a fraction of the chart).
     */
    public function setOffsetX(float $pValue = 0): self
    {
        $this->offsetX = $pValue;

        return $this;
    }

    /**
     * Get OffsetY (as a fraction of the chart).
     */
    public function getOffsetY(): float
    {
        return $this->offsetY;
    }

    /**
     * Set OffsetY (as a fraction of the chart).
     *
     * @return \PhpOffice\PhpPresentation\Shape\Chart\PlotArea
     */
    public function setOffsetY(float $pValue = 0): self
    {
        $this->offsetY = $pValue;

        return $this;
    }

    /**
     * Get Width (as a fraction of the chart).
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * Set Width (as a fraction of the chart).
     */
    public function setWidth(int $pValue = 0): self
    {
        $this->width = $pValue;

        return $this;
    }

    /**
     * Get Height (as a fraction of the chart).
     */
    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * Set Height (as a fraction of the chart).
     *
     * @return \PhpOffice\PhpPresentation\Shape\Chart\PlotArea
     */
    public function setHeight(float $value = 0): self
    {
        $this->height = $value;

        return $this;
    }

    /**
     * Get hash code.
     *
     * @return string Hash code
     */
    public function getHashCode(): string
    {
        $chartTypeHashes = array();
        foreach ($this->chartTypes as $type) {
            $chartTypeHashes[] = $type->getHashCode();
        }

        return md5((empty($chartTypeHashes) ? 'null' : implode('', $chartTypeHashes)) . $this->primaryAxisX->getHashCode() . $this->primaryAxisY->getHashCode() . $this->offsetX . $this->offsetY . $this->width . $this->height . __CLASS__);
    }

    /**
     * Hash index.
     *
     * @var int
     */
    private $hashIndex;

    /**
     * Get hash index.
     *
     * Note that this index may vary during script execution! Only reliable moment is
     * while doing a write of a workbook and when changes are not allowed.
     *
     * @return int|null Hash index
     */
    public function getHashIndex(): ?int
    {
        return $this->hashIndex;
    }

    /**
     * Set hash index.
     *
     * Note that this index may vary during script execution! Only reliable moment is
     * while doing a write of a workbook and when changes are not allowed.
     *
     * @param int $value Hash index
     *
     * @return PlotArea
     */
    public function setHashIndex(int $value)
    {
        $this->hashIndex = $value;

        return $this;
    }
}
