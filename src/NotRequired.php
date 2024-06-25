<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
/**
 * Mark an object property as not required
 */
final class NotRequired
{
}
