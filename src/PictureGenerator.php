<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\Image;

class PictureGenerator implements PictureGeneratorInterface
{
    /**
     * @var ResizerInterface
     */
    private $resizer;

    /**
     * @var ResizeOptionsInterface
     */
    private $resizeOptions;

    /**
     * @param ResizerInterface $resizer
     */
    public function __construct(ResizerInterface $resizer)
    {
        $this->resizer = $resizer;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ImageInterface $image, PictureConfigurationInterface $config, ResizeOptionsInterface $options)
    {
        $this->resizeOptions = clone $options;
        $this->resizeOptions->setTargetPath(null);

        $sources = [];

        foreach ($config->getSizeItems() as $sizeItem) {
            $sources[] = $this->generateSource($image, $sizeItem);
        }

        return new Picture($this->generateSource($image, $config->getSize()), $sources);
    }

    /**
     * Generates the source.
     *
     * @param ImageInterface                    $image
     * @param PictureConfigurationItemInterface $config
     *
     * @return array
     */
    private function generateSource(ImageInterface $image, PictureConfigurationItemInterface $config)
    {
        $densities = [1];
        $sizesAttribute = $config->getSizes();

        $width1x = $this->resizer
            ->resize($image, $config->getResizeConfig(), $this->resizeOptions)
            ->getDimensions()
            ->getSize()
            ->getWidth()
        ;

        if ($config->getDensities()
            && ($config->getResizeConfig()->getWidth() || $config->getResizeConfig()->getHeight())
        ) {
            if (!$sizesAttribute && false !== strpos($config->getDensities(), 'w')) {
                $sizesAttribute = '100vw';
            }

            $densities = $this->parseDensities($config->getDensities(), $width1x);
        }

        $attributes = [];
        $srcset = [];

        $descriptorType = '';

        if (\count($densities) > 1) {
            $descriptorType = $sizesAttribute ? 'w' : 'x'; // use pixel density descriptors if the sizes attribute is empty
        }

        foreach ($densities as $density) {
            $srcset[] = $this->generateSrcsetItem($image, $config, $density, $descriptorType, $width1x);
        }

        $srcset = $this->removeDuplicateScrsetItems($srcset);

        $attributes['srcset'] = $srcset;
        $attributes['src'] = $srcset[0][0];

        if (
            !$attributes['src']->getDimensions()->isRelative() &&
            !$attributes['src']->getDimensions()->isUndefined()
        ) {
            $attributes['width'] = $attributes['src']->getDimensions()->getSize()->getWidth();
            $attributes['height'] = $attributes['src']->getDimensions()->getSize()->getHeight();
        }

        if ($sizesAttribute) {
            $attributes['sizes'] = $sizesAttribute;
        }

        if ($config->getMedia()) {
            $attributes['media'] = $config->getMedia();
        }

        return $attributes;
    }

    /**
     * Parse the densities string and return an array of scaling factors.
     *
     * @param string $densities
     * @param int    $width1x
     *
     * @return array<int,float>
     */
    private function parseDensities($densities, $width1x)
    {
        $densities = explode(',', $densities);

        $densities = array_map(
            function ($density) use ($width1x) {
                $type = substr(trim($density), -1);

                if ('w' === $type) {
                    return (int) $density / $width1x;
                }

                return (float) $density;
            },
            $densities
        );

        // Strip empty densities
        $densities = array_filter($densities);

        // Add 1x to the beginning of the list
        array_unshift($densities, 1);

        // Strip duplicates
        $densities = array_values(array_unique($densities));

        return $densities;
    }

    /**
     * Generates a srcset item.
     *
     * @param ImageInterface                    $image
     * @param PictureConfigurationItemInterface $config
     * @param float                             $density
     * @param string                            $descriptorType x, w or the empty string
     * @param int                               $width1x
     *
     * @return array Array containing an ImageInterface and an optional descriptor string
     */
    private function generateSrcsetItem(ImageInterface $image, PictureConfigurationItemInterface $config, $density, $descriptorType, $width1x)
    {
        $resizeConfig = clone $config->getResizeConfig();
        $resizeConfig->setWidth(round($resizeConfig->getWidth() * $density));
        $resizeConfig->setHeight(round($resizeConfig->getHeight() * $density));

        $resizedImage = $this->resizer->resize($image, $resizeConfig, $this->resizeOptions);

        $src = [$resizedImage];

        if ('x' === $descriptorType) {
            $srcX = $resizedImage->getDimensions()->getSize()->getWidth() / $width1x;
            $src[1] = rtrim(sprintf('%.3F', $srcX), '.0').'x';
        } elseif ('w' === $descriptorType) {
            $src[1] = $resizedImage->getDimensions()->getSize()->getWidth().'w';
        }

        return $src;
    }

    /**
     * Removes duplicate items from a srcset array.
     *
     * @param array $srcset Array containing an ImageInterface and an optional descriptor string
     *
     * @return array
     */
    private function removeDuplicateScrsetItems(array $srcset)
    {
        $srcset = array_filter(
            $srcset,
            function (array $item) use (&$usedPaths) {
                /** @var ImageInterface[] $item */
                $key = $item[0]->getPath();

                if (isset($usedPaths[$key])) {
                    return false;
                }

                $usedPaths[$key] = true;

                return true;
            }
        );

        return array_values($srcset);
    }
}
