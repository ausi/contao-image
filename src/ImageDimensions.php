<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\Image;

use Contao\ImagineSvg\RelativeBoxInterface;
use Contao\ImagineSvg\UndefinedBoxInterface;
use Imagine\Image\BoxInterface;

class ImageDimensions implements ImageDimensionsInterface
{
    /**
     * @var BoxInterface
     */
    private $size;

    /**
     * @var int
     */
    private $orientation;

    /**
     * @var bool
     */
    private $relative;

    /**
     * @var bool
     */
    private $undefined;

    public function __construct(BoxInterface $size, bool $relative = null, bool $undefined = null, int $orientation = self::ORIENTATION_NORMAL)
    {
        if ($orientation < 1 || $orientation > 8) {
            throw new \InvalidArgumentException(
                'Orientation must be one of the ImageDimensionsInterface::ORIENTATION_* constants'
            );
        }

        if (null === $relative) {
            $relative = $size instanceof RelativeBoxInterface;
        }

        if (null === $undefined) {
            $undefined = $size instanceof UndefinedBoxInterface;
        }

        $this->size = $size;
        $this->orientation = $orientation;
        $this->relative = $relative;
        $this->undefined = $undefined;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): BoxInterface
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrientation(): int
    {
        return $this->orientation;
    }

    /**
     * {@inheritdoc}
     */
    public function isRelative(): bool
    {
        return $this->relative;
    }

    /**
     * {@inheritdoc}
     */
    public function isUndefined(): bool
    {
        return $this->undefined;
    }
}
