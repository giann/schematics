<?php

declare(strict_types=1);

namespace Giann\Schematics;

enum ContentEncoding: string
{
    case SevenBit = '7bit';
    case HeightBit = '8bit';
    case Binary = 'binary';
    case QuotedPrintable = 'quoted-printable';
    case Base16 = 'base16';
    case Base32 = 'base32';
    case Base64 = 'base64';
}
