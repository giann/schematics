<?php

declare(strict_types=1);

namespace Giann\Schematics\Draft04;

// https://json-schema.org/understanding-json-schema/reference/string.html#id8
enum Format: string
{
    case DateTime = 'date-time';
    case Email = 'email';
    case Hostname = 'hostname';
    case IdnHostname = 'idn-hostname';
    case IpV4 = 'ipv4';
    case IpV6 = 'ipv6';
    case Uri = 'uri';
}
