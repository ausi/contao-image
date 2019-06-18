<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\Image\Tests;

use Contao\Image\ResizeOptions;
use PHPUnit\Framework\TestCase;

class ResizeOptionsTest extends TestCase
{
    public function testSetImagineOptions(): void
    {
        $options = new ResizeOptions();

        $this->assertSame([], $options->getImagineOptions());
        $this->assertSame($options, $options->setImagineOptions(['jpeg_quality' => 95]));
        $this->assertSame(['jpeg_quality' => 95], $options->getImagineOptions());
    }

    public function testSetTargetPath(): void
    {
        $options = new ResizeOptions();

        $this->assertNull($options->getTargetPath());
        $this->assertSame($options, $options->setTargetPath('/target/path'));
        $this->assertSame('/target/path', $options->getTargetPath());

        $options->setTargetPath(null);

        $this->assertNull($options->getTargetPath());

        $this->expectException('InvalidArgumentException');

        $options->setTargetPath('invalid/relative/path');
    }

    public function testSetBypassCache(): void
    {
        $options = new ResizeOptions();

        $this->assertFalse($options->getBypassCache());
        $this->assertSame($options, $options->setBypassCache(true));
        $this->assertTrue($options->getBypassCache());
    }

    public function testSetForceReEncoding(): void
    {
        $options = new ResizeOptions();

        $this->assertFalse($options->getForceReEncoding());
        $this->assertSame($options, $options->setForceReEncoding(true));
        $this->assertTrue($options->getForceReEncoding());
    }
}
