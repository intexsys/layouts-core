<?php

declare(strict_types=1);

use Netgen\BlockManager\Utils\DateTimeUtils;
use Symfony\Bridge\PhpUnit\ClockMock;

require_once __DIR__ . '/../vendor/autoload.php';

ClockMock::register(DateTimeUtils::class);
