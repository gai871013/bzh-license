<?php

/*
 * This file is part of the gai871013/bzh-license.
 *
 * (c) gai871013 <wang.gaichao@163.com>
 *
 * This source file is subject to the Apache license 2.0 that is bundled
 * with this source code in the file LICENSE.
 */

namespace Gai871013\License\Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @before
     */
    public function registerMockery()
    {
        \Mockery::globalHelpers();
    }

    /**
     * @after
     */
    public function closeMockery()
    {
        \Mockery::close();
    }
}
