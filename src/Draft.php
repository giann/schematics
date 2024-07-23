<?php

declare(strict_types=1);

namespace Giann\Schematics;

enum Draft: string
{
    case December2020 = 'https://json-schema.org/draft/2020-12/schema';
    case September2019 = 'https://json-schema.org/draft/2019-09/schema';
    case Draft07 = 'https://json-schema.org/draft-07/schema';
    case Draft06 = 'https://json-schema.org/draft-06/schema';
    case Draft04 = 'https://json-schema.org/draft-04/schema';
    case Draft03 = 'https://json-schema.org/draft-03/schema';
    case Draft02 = 'https://json-schema.org/draft-02/schema';
    case Draft01 = 'https://json-schema.org/draft-01/schema';
}
