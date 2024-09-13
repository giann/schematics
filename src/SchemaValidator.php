<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Giann\Schematics\December2020\SchemaValidator as December2020SchemaValidator;
use Giann\Schematics\Draft04\SchemaValidator as Draft04SchemaValidator;
use Giann\Schematics\Exception\InvalidSchemaException;
use Giann\Schematics\Exception\UnsupportedDraftException;
use Giann\Trunk\Trunk;
use ValueError;

class SchemaValidator
{
    public function __construct(
        private Draft $defaultDraft = Draft::December2020
    ) {}

    /**
     * Validate schema
     *
     * @param array<string,mixed>|Trunk $schema
     * @param bool $enforceSingleType If true will not allow several type in a schema with other keywords (which can't be expressed with schematics)
     * @return void
     * @throws InvalidSchemaException
     * @throws UnsupportedDraftException
     */
    public function validate(array|Trunk $schema, bool $enforceSingleType = false): void
    {
        $schema = $schema instanceof Trunk ? $schema : new Trunk($schema);

        $draft = $this->defaultDraft;
        try {
            $draft = Draft::from(
                str_replace(
                    'http://',
                    'https://',
                    trim($schema['$schema']->string() ?? $this->defaultDraft->value, '#')
                )
            );
        } catch (ValueError $e) {
            throw new UnsupportedDraftException('Draft ' . $schema['$schema']->stringValue() . ' is not supported');
        }

        switch ($draft) {
            case Draft::December2020:
            case Draft::September2019:
                (new December2020SchemaValidator())->validate($schema, enforceSingleType: $enforceSingleType);
                break;
            case Draft::Draft04:
                (new Draft04SchemaValidator())->validate($schema, enforceSingleType: $enforceSingleType);
                break;
            default:
                throw new UnsupportedDraftException('Draft ' . $schema['$schema']->stringValue() . ' is not supported');
        }
    }
}
