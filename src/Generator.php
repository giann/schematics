<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Giann\Schematics\December2020\Generator as December2020Generator;
use Giann\Schematics\Draft04\Generator as Draft04Generator;
use Giann\Schematics\Exception\InvalidSchemaException;
use Giann\Schematics\Exception\UnsupportedDraftException;
use Giann\Trunk\Trunk;
use PhpParser\Node\Expr;
use ValueError;

class Generator
{
    /**
     * Generate Schema expression from json schema
     * @param array<string, mixed>|Trunk $rawSchema
     * @throws InvalidSchemaException
     * @throws UnsupportedDraftException
     * @return Expr
     */
    public function generateSchema(array|Trunk $rawSchema): Expr
    {
        $rawSchema = $rawSchema instanceof Trunk ? $rawSchema : new Trunk($rawSchema);

        $draft = Draft::December2020;
        try {
            $draft = Draft::from(
                str_replace(
                    'http://',
                    'https://', 
                    trim($rawSchema['$schema']->string() ?? Draft::December2020->value, '#')
                )
            );
        } catch (ValueError $e) {
            throw new UnsupportedDraftException('Draft ' . $rawSchema['$schema']->stringValue() . ' is not supported');
        }

        switch ($draft) {
            case Draft::December2020:
            case Draft::September2019:
                return (new December2020Generator())->generateSchema($rawSchema);
            case Draft::Draft04:
                return (new Draft04Generator())->generateSchema($rawSchema);
            default:
                throw new UnsupportedDraftException('Draft ' . $rawSchema['$schema']->stringValue() . ' is not supported');
        }
    }
}
