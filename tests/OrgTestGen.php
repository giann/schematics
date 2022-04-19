<?php

declare(strict_types=1);

use Giann\Schematics\InvalidSchemaValueException;
use Giann\Schematics\NotYetImplementedException;
use Giann\Schematics\Schema;
use PHPUnit\Framework\TestCase;

final class OrgTestGen extends TestCase
{
    public function testAnchor(): void
    {
        $schema = Schema::fromJson('{"$ref":"#foo","$defs":{"A":{"$anchor":"foo","type":"integer"}}}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'Location-independent identifier: match. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'Location-independent identifier: match. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Location-independent identifier: match. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"a"'));
            $this->assertTrue(false, 'Location-independent identifier: mismatch. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Location-independent identifier: mismatch. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Location-independent identifier: mismatch. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$ref":"http:\/\/localhost:1234\/bar#foo","$defs":{"A":{"$id":"http:\/\/localhost:1234\/bar","$anchor":"foo","type":"integer"}}}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'Location-independent identifier with absolute URI: match. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'Location-independent identifier with absolute URI: match. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Location-independent identifier with absolute URI: match. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"a"'));
            $this->assertTrue(false, 'Location-independent identifier with absolute URI: mismatch. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Location-independent identifier with absolute URI: mismatch. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Location-independent identifier with absolute URI: mismatch. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/localhost:1234\/root","$ref":"http:\/\/localhost:1234\/nested.json#foo","$defs":{"A":{"$id":"nested.json","$defs":{"B":{"$anchor":"foo","type":"integer"}}}}}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'Location-independent identifier with base URI change in subschema: match. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'Location-independent identifier with base URI change in subschema: match. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Location-independent identifier with base URI change in subschema: match. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"a"'));
            $this->assertTrue(false, 'Location-independent identifier with base URI change in subschema: mismatch. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Location-independent identifier with base URI change in subschema: mismatch. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Location-independent identifier with base URI change in subschema: mismatch. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$defs":{"anchor_in_enum":{"enum":[{"$anchor":"my_anchor","type":"null"}]},"real_identifier_in_schema":{"$anchor":"my_anchor","type":"string"},"zzz_anchor_in_const":{"const":{"$anchor":"my_anchor","type":"null"}}},"anyOf":[{"$ref":"#\/$defs\/anchor_in_enum"},{"$ref":"#my_anchor"}]}');
        try {
            $schema->validate(json_decode('{"$anchor":"my_anchor","type":"null"}'));
            $this->assertTrue(true, '$anchor inside an enum is not a real identifier: exact match to enum, and type matches. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, '$anchor inside an enum is not a real identifier: exact match to enum, and type matches. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$anchor inside an enum is not a real identifier: exact match to enum, and type matches. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"type":"null"}'));
            $this->assertTrue(false, '$anchor inside an enum is not a real identifier: in implementations that strip $anchor, this may match either $def. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, '$anchor inside an enum is not a real identifier: in implementations that strip $anchor, this may match either $def. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$anchor inside an enum is not a real identifier: in implementations that strip $anchor, this may match either $def. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"a string to match #\/$defs\/anchor_in_enum"'));
            $this->assertTrue(true, '$anchor inside an enum is not a real identifier: match $ref to $anchor. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, '$anchor inside an enum is not a real identifier: match $ref to $anchor. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$anchor inside an enum is not a real identifier: match $ref to $anchor. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, '$anchor inside an enum is not a real identifier: no match on enum or $ref to $anchor. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, '$anchor inside an enum is not a real identifier: no match on enum or $ref to $anchor. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$anchor inside an enum is not a real identifier: no match on enum or $ref to $anchor. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/localhost:1234\/foobar","$defs":{"A":{"$id":"child1","allOf":[{"$id":"child2","$anchor":"my_anchor","type":"number"},{"$anchor":"my_anchor","type":"string"}]}},"$ref":"child1#my_anchor"}');
        try {
            $schema->validate(json_decode('"a"'));
            $this->assertTrue(true, 'same $anchor with different base uri: $ref should resolve to /$defs/A/allOf/1. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'same $anchor with different base uri: $ref should resolve to /$defs/A/allOf/1. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'same $anchor with different base uri: $ref should resolve to /$defs/A/allOf/1. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'same $anchor with different base uri: $ref should not resolve to /$defs/A/allOf/0. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'same $anchor with different base uri: $ref should not resolve to /$defs/A/allOf/0. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'same $anchor with different base uri: $ref should not resolve to /$defs/A/allOf/0. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testContent(): void
    {
        $schema = Schema::fromJson('{"contentMediaType":"application\/json"}');
        try {
            $schema->validate(json_decode('"{\"foo\": \"bar\"}"'));
            $this->assertTrue(true, 'validation of string-encoded content based on media type: a valid JSON document. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of string-encoded content based on media type: a valid JSON document. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of string-encoded content based on media type: a valid JSON document. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"{:}"'));
            $this->assertTrue(true, 'validation of string-encoded content based on media type: an invalid JSON document; validates true. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of string-encoded content based on media type: an invalid JSON document; validates true. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of string-encoded content based on media type: an invalid JSON document; validates true. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('100'));
            $this->assertTrue(true, 'validation of string-encoded content based on media type: ignores non-strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of string-encoded content based on media type: ignores non-strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of string-encoded content based on media type: ignores non-strings. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contentEncoding":"base64"}');
        try {
            $schema->validate(json_decode('"eyJmb28iOiAiYmFyIn0K"'));
            $this->assertTrue(true, 'validation of binary string-encoding: a valid base64 string. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary string-encoding: a valid base64 string. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary string-encoding: a valid base64 string. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"eyJmb28iOi%iYmFyIn0K"'));
            $this->assertTrue(true, 'validation of binary string-encoding: an invalid base64 string (% is not a valid character); validates true. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary string-encoding: an invalid base64 string (% is not a valid character); validates true. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary string-encoding: an invalid base64 string (% is not a valid character); validates true. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('100'));
            $this->assertTrue(true, 'validation of binary string-encoding: ignores non-strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary string-encoding: ignores non-strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary string-encoding: ignores non-strings. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contentMediaType":"application\/json","contentEncoding":"base64"}');
        try {
            $schema->validate(json_decode('"eyJmb28iOiAiYmFyIn0K"'));
            $this->assertTrue(true, 'validation of binary-encoded media type documents: a valid base64-encoded JSON document. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary-encoded media type documents: a valid base64-encoded JSON document. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary-encoded media type documents: a valid base64-encoded JSON document. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"ezp9Cg=="'));
            $this->assertTrue(true, 'validation of binary-encoded media type documents: a validly-encoded invalid JSON document; validates true. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary-encoded media type documents: a validly-encoded invalid JSON document; validates true. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary-encoded media type documents: a validly-encoded invalid JSON document; validates true. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"{}"'));
            $this->assertTrue(true, 'validation of binary-encoded media type documents: an invalid base64 string that is valid JSON; validates true. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary-encoded media type documents: an invalid base64 string that is valid JSON; validates true. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary-encoded media type documents: an invalid base64 string that is valid JSON; validates true. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('100'));
            $this->assertTrue(true, 'validation of binary-encoded media type documents: ignores non-strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary-encoded media type documents: ignores non-strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary-encoded media type documents: ignores non-strings. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contentMediaType":"application\/json","contentEncoding":"base64","contentSchema":{"required":["foo"],"properties":{"foo":{"type":"string"}}}}');
        try {
            $schema->validate(json_decode('"eyJmb28iOiAiYmFyIn0K"'));
            $this->assertTrue(true, 'validation of binary-encoded media type documents with schema: a valid base64-encoded JSON document. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: a valid base64-encoded JSON document. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: a valid base64-encoded JSON document. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"eyJib28iOiAyMCwgImZvbyI6ICJiYXoifQ=="'));
            $this->assertTrue(true, 'validation of binary-encoded media type documents with schema: another valid base64-encoded JSON document. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: another valid base64-encoded JSON document. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: another valid base64-encoded JSON document. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"eyJib28iOiAyMH0="'));
            $this->assertTrue(true, 'validation of binary-encoded media type documents with schema: an invalid base64-encoded JSON document; validates true. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: an invalid base64-encoded JSON document; validates true. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: an invalid base64-encoded JSON document; validates true. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"e30="'));
            $this->assertTrue(true, 'validation of binary-encoded media type documents with schema: an empty object as a base64-encoded JSON document; validates true. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: an empty object as a base64-encoded JSON document; validates true. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: an empty object as a base64-encoded JSON document; validates true. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"W10="'));
            $this->assertTrue(true, 'validation of binary-encoded media type documents with schema: an empty array as a base64-encoded JSON document. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: an empty array as a base64-encoded JSON document. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: an empty array as a base64-encoded JSON document. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"ezp9Cg=="'));
            $this->assertTrue(true, 'validation of binary-encoded media type documents with schema: a validly-encoded invalid JSON document; validates true. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: a validly-encoded invalid JSON document; validates true. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: a validly-encoded invalid JSON document; validates true. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"{}"'));
            $this->assertTrue(true, 'validation of binary-encoded media type documents with schema: an invalid base64 string that is valid JSON; validates true. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: an invalid base64 string that is valid JSON; validates true. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: an invalid base64 string that is valid JSON; validates true. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('100'));
            $this->assertTrue(true, 'validation of binary-encoded media type documents with schema: ignores non-strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: ignores non-strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validation of binary-encoded media type documents with schema: ignores non-strings. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testUniqueItems(): void
    {
        $schema = Schema::fromJson('{"uniqueItems":true}');
        try {
            $schema->validate(json_decode('[1,2]'));
            $this->assertTrue(true, 'uniqueItems validation: unique array of integers is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: unique array of integers is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: unique array of integers is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1]'));
            $this->assertTrue(false, 'uniqueItems validation: non-unique array of integers is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems validation: non-unique array of integers is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: non-unique array of integers is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2,1]'));
            $this->assertTrue(false, 'uniqueItems validation: non-unique array of more than two integers is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems validation: non-unique array of more than two integers is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: non-unique array of more than two integers is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1,1]'));
            $this->assertTrue(false, 'uniqueItems validation: numbers are unique if mathematically unequal. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems validation: numbers are unique if mathematically unequal. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: numbers are unique if mathematically unequal. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[0,false]'));
            $this->assertTrue(true, 'uniqueItems validation: false is not equal to zero. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: false is not equal to zero. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: false is not equal to zero. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,true]'));
            $this->assertTrue(true, 'uniqueItems validation: true is not equal to one. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: true is not equal to one. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: true is not equal to one. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo","bar","baz"]'));
            $this->assertTrue(true, 'uniqueItems validation: unique array of strings is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: unique array of strings is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: unique array of strings is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo","bar","foo"]'));
            $this->assertTrue(false, 'uniqueItems validation: non-unique array of strings is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems validation: non-unique array of strings is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: non-unique array of strings is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{"foo":"bar"},{"foo":"baz"}]'));
            $this->assertTrue(true, 'uniqueItems validation: unique array of objects is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: unique array of objects is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: unique array of objects is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{"foo":"bar"},{"foo":"bar"}]'));
            $this->assertTrue(false, 'uniqueItems validation: non-unique array of objects is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems validation: non-unique array of objects is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: non-unique array of objects is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{"foo":{"bar":{"baz":true}}},{"foo":{"bar":{"baz":false}}}]'));
            $this->assertTrue(true, 'uniqueItems validation: unique array of nested objects is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: unique array of nested objects is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: unique array of nested objects is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{"foo":{"bar":{"baz":true}}},{"foo":{"bar":{"baz":true}}}]'));
            $this->assertTrue(false, 'uniqueItems validation: non-unique array of nested objects is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems validation: non-unique array of nested objects is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: non-unique array of nested objects is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[["foo"],["bar"]]'));
            $this->assertTrue(true, 'uniqueItems validation: unique array of arrays is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: unique array of arrays is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: unique array of arrays is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[["foo"],["foo"]]'));
            $this->assertTrue(false, 'uniqueItems validation: non-unique array of arrays is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems validation: non-unique array of arrays is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: non-unique array of arrays is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[["foo"],["bar"],["foo"]]'));
            $this->assertTrue(false, 'uniqueItems validation: non-unique array of more than two arrays is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems validation: non-unique array of more than two arrays is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: non-unique array of more than two arrays is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,true]'));
            $this->assertTrue(true, 'uniqueItems validation: 1 and true are unique. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: 1 and true are unique. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: 1 and true are unique. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[0,false]'));
            $this->assertTrue(true, 'uniqueItems validation: 0 and false are unique. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: 0 and false are unique. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: 0 and false are unique. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[[1],[true]]'));
            $this->assertTrue(true, 'uniqueItems validation: [1] and [true] are unique. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: [1] and [true] are unique. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: [1] and [true] are unique. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[[0],[false]]'));
            $this->assertTrue(true, 'uniqueItems validation: [0] and [false] are unique. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: [0] and [false] are unique. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: [0] and [false] are unique. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[[[1],"foo"],[[true],"foo"]]'));
            $this->assertTrue(true, 'uniqueItems validation: nested [1] and [true] are unique. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: nested [1] and [true] are unique. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: nested [1] and [true] are unique. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[[[0],"foo"],[[false],"foo"]]'));
            $this->assertTrue(true, 'uniqueItems validation: nested [0] and [false] are unique. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: nested [0] and [false] are unique. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: nested [0] and [false] are unique. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{},[1],true,null,1,"{}"]'));
            $this->assertTrue(true, 'uniqueItems validation: unique heterogeneous types are valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: unique heterogeneous types are valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: unique heterogeneous types are valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{},[1],true,null,{},1]'));
            $this->assertTrue(false, 'uniqueItems validation: non-unique heterogeneous types are invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems validation: non-unique heterogeneous types are invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: non-unique heterogeneous types are invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{"a":1,"b":2},{"a":2,"b":1}]'));
            $this->assertTrue(true, 'uniqueItems validation: different objects are unique. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: different objects are unique. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: different objects are unique. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{"a":1,"b":2},{"b":2,"a":1}]'));
            $this->assertTrue(false, 'uniqueItems validation: objects are non-unique despite key order. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems validation: objects are non-unique despite key order. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: objects are non-unique despite key order. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{"a":false},{"a":0}]'));
            $this->assertTrue(true, 'uniqueItems validation: {"a": false} and {"a": 0} are unique. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: {"a": false} and {"a": 0} are unique. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: {"a": false} and {"a": 0} are unique. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{"a":true},{"a":1}]'));
            $this->assertTrue(true, 'uniqueItems validation: {"a": true} and {"a": 1} are unique. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems validation: {"a": true} and {"a": 1} are unique. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems validation: {"a": true} and {"a": 1} are unique. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"prefixItems":[{"type":"boolean"},{"type":"boolean"}],"uniqueItems":true}');
        try {
            $schema->validate(json_decode('[false,true]'));
            $this->assertTrue(true, 'uniqueItems with an array of items: [false, true] from items array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems with an array of items: [false, true] from items array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems with an array of items: [false, true] from items array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[true,false]'));
            $this->assertTrue(true, 'uniqueItems with an array of items: [true, false] from items array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems with an array of items: [true, false] from items array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems with an array of items: [true, false] from items array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[false,false]'));
            $this->assertTrue(false, 'uniqueItems with an array of items: [false, false] from items array is not valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems with an array of items: [false, false] from items array is not valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems with an array of items: [false, false] from items array is not valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[true,true]'));
            $this->assertTrue(false, 'uniqueItems with an array of items: [true, true] from items array is not valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems with an array of items: [true, true] from items array is not valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems with an array of items: [true, true] from items array is not valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[false,true,"foo","bar"]'));
            $this->assertTrue(true, 'uniqueItems with an array of items: unique array extended from [false, true] is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems with an array of items: unique array extended from [false, true] is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems with an array of items: unique array extended from [false, true] is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[true,false,"foo","bar"]'));
            $this->assertTrue(true, 'uniqueItems with an array of items: unique array extended from [true, false] is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems with an array of items: unique array extended from [true, false] is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems with an array of items: unique array extended from [true, false] is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[false,true,"foo","foo"]'));
            $this->assertTrue(false, 'uniqueItems with an array of items: non-unique array extended from [false, true] is not valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems with an array of items: non-unique array extended from [false, true] is not valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems with an array of items: non-unique array extended from [false, true] is not valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[true,false,"foo","foo"]'));
            $this->assertTrue(false, 'uniqueItems with an array of items: non-unique array extended from [true, false] is not valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems with an array of items: non-unique array extended from [true, false] is not valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems with an array of items: non-unique array extended from [true, false] is not valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"prefixItems":[{"type":"boolean"},{"type":"boolean"}],"uniqueItems":true,"items":false}');
        try {
            $schema->validate(json_decode('[false,true]'));
            $this->assertTrue(true, 'uniqueItems with an array of items and additionalItems=false: [false, true] from items array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems with an array of items and additionalItems=false: [false, true] from items array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems with an array of items and additionalItems=false: [false, true] from items array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[true,false]'));
            $this->assertTrue(true, 'uniqueItems with an array of items and additionalItems=false: [true, false] from items array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems with an array of items and additionalItems=false: [true, false] from items array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems with an array of items and additionalItems=false: [true, false] from items array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[false,false]'));
            $this->assertTrue(false, 'uniqueItems with an array of items and additionalItems=false: [false, false] from items array is not valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems with an array of items and additionalItems=false: [false, false] from items array is not valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems with an array of items and additionalItems=false: [false, false] from items array is not valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[true,true]'));
            $this->assertTrue(false, 'uniqueItems with an array of items and additionalItems=false: [true, true] from items array is not valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems with an array of items and additionalItems=false: [true, true] from items array is not valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems with an array of items and additionalItems=false: [true, true] from items array is not valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[false,true,null]'));
            $this->assertTrue(false, 'uniqueItems with an array of items and additionalItems=false: extra items are invalid even if unique. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems with an array of items and additionalItems=false: extra items are invalid even if unique. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems with an array of items and additionalItems=false: extra items are invalid even if unique. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"uniqueItems":false}');
        try {
            $schema->validate(json_decode('[1,2]'));
            $this->assertTrue(true, 'uniqueItems=false validation: unique array of integers is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: unique array of integers is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: unique array of integers is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1]'));
            $this->assertTrue(true, 'uniqueItems=false validation: non-unique array of integers is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: non-unique array of integers is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: non-unique array of integers is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1,1]'));
            $this->assertTrue(true, 'uniqueItems=false validation: numbers are unique if mathematically unequal. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: numbers are unique if mathematically unequal. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: numbers are unique if mathematically unequal. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[0,false]'));
            $this->assertTrue(true, 'uniqueItems=false validation: false is not equal to zero. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: false is not equal to zero. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: false is not equal to zero. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,true]'));
            $this->assertTrue(true, 'uniqueItems=false validation: true is not equal to one. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: true is not equal to one. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: true is not equal to one. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{"foo":"bar"},{"foo":"baz"}]'));
            $this->assertTrue(true, 'uniqueItems=false validation: unique array of objects is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: unique array of objects is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: unique array of objects is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{"foo":"bar"},{"foo":"bar"}]'));
            $this->assertTrue(true, 'uniqueItems=false validation: non-unique array of objects is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: non-unique array of objects is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: non-unique array of objects is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{"foo":{"bar":{"baz":true}}},{"foo":{"bar":{"baz":false}}}]'));
            $this->assertTrue(true, 'uniqueItems=false validation: unique array of nested objects is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: unique array of nested objects is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: unique array of nested objects is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{"foo":{"bar":{"baz":true}}},{"foo":{"bar":{"baz":true}}}]'));
            $this->assertTrue(true, 'uniqueItems=false validation: non-unique array of nested objects is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: non-unique array of nested objects is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: non-unique array of nested objects is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[["foo"],["bar"]]'));
            $this->assertTrue(true, 'uniqueItems=false validation: unique array of arrays is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: unique array of arrays is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: unique array of arrays is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[["foo"],["foo"]]'));
            $this->assertTrue(true, 'uniqueItems=false validation: non-unique array of arrays is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: non-unique array of arrays is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: non-unique array of arrays is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,true]'));
            $this->assertTrue(true, 'uniqueItems=false validation: 1 and true are unique. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: 1 and true are unique. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: 1 and true are unique. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[0,false]'));
            $this->assertTrue(true, 'uniqueItems=false validation: 0 and false are unique. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: 0 and false are unique. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: 0 and false are unique. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{},[1],true,null,1]'));
            $this->assertTrue(true, 'uniqueItems=false validation: unique heterogeneous types are valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: unique heterogeneous types are valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: unique heterogeneous types are valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{},[1],true,null,{},1]'));
            $this->assertTrue(true, 'uniqueItems=false validation: non-unique heterogeneous types are valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false validation: non-unique heterogeneous types are valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false validation: non-unique heterogeneous types are valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"prefixItems":[{"type":"boolean"},{"type":"boolean"}],"uniqueItems":false}');
        try {
            $schema->validate(json_decode('[false,true]'));
            $this->assertTrue(true, 'uniqueItems=false with an array of items: [false, true] from items array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: [false, true] from items array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: [false, true] from items array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[true,false]'));
            $this->assertTrue(true, 'uniqueItems=false with an array of items: [true, false] from items array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: [true, false] from items array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: [true, false] from items array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[false,false]'));
            $this->assertTrue(true, 'uniqueItems=false with an array of items: [false, false] from items array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: [false, false] from items array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: [false, false] from items array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[true,true]'));
            $this->assertTrue(true, 'uniqueItems=false with an array of items: [true, true] from items array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: [true, true] from items array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: [true, true] from items array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[false,true,"foo","bar"]'));
            $this->assertTrue(true, 'uniqueItems=false with an array of items: unique array extended from [false, true] is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: unique array extended from [false, true] is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: unique array extended from [false, true] is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[true,false,"foo","bar"]'));
            $this->assertTrue(true, 'uniqueItems=false with an array of items: unique array extended from [true, false] is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: unique array extended from [true, false] is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: unique array extended from [true, false] is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[false,true,"foo","foo"]'));
            $this->assertTrue(true, 'uniqueItems=false with an array of items: non-unique array extended from [false, true] is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: non-unique array extended from [false, true] is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: non-unique array extended from [false, true] is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[true,false,"foo","foo"]'));
            $this->assertTrue(true, 'uniqueItems=false with an array of items: non-unique array extended from [true, false] is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: non-unique array extended from [true, false] is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false with an array of items: non-unique array extended from [true, false] is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"prefixItems":[{"type":"boolean"},{"type":"boolean"}],"uniqueItems":false,"items":false}');
        try {
            $schema->validate(json_decode('[false,true]'));
            $this->assertTrue(true, 'uniqueItems=false with an array of items and additionalItems=false: [false, true] from items array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false with an array of items and additionalItems=false: [false, true] from items array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false with an array of items and additionalItems=false: [false, true] from items array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[true,false]'));
            $this->assertTrue(true, 'uniqueItems=false with an array of items and additionalItems=false: [true, false] from items array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false with an array of items and additionalItems=false: [true, false] from items array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false with an array of items and additionalItems=false: [true, false] from items array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[false,false]'));
            $this->assertTrue(true, 'uniqueItems=false with an array of items and additionalItems=false: [false, false] from items array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false with an array of items and additionalItems=false: [false, false] from items array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false with an array of items and additionalItems=false: [false, false] from items array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[true,true]'));
            $this->assertTrue(true, 'uniqueItems=false with an array of items and additionalItems=false: [true, true] from items array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uniqueItems=false with an array of items and additionalItems=false: [true, true] from items array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false with an array of items and additionalItems=false: [true, true] from items array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[false,true,null]'));
            $this->assertTrue(false, 'uniqueItems=false with an array of items and additionalItems=false: extra items are invalid even if unique. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'uniqueItems=false with an array of items and additionalItems=false: extra items are invalid even if unique. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uniqueItems=false with an array of items and additionalItems=false: extra items are invalid even if unique. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testMinItems(): void
    {
        $schema = Schema::fromJson('{"minItems":1}');
        try {
            $schema->validate(json_decode('[1,2]'));
            $this->assertTrue(true, 'minItems validation: longer is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minItems validation: longer is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minItems validation: longer is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(true, 'minItems validation: exact length is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minItems validation: exact length is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minItems validation: exact length is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'minItems validation: too short is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minItems validation: too short is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minItems validation: too short is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('""'));
            $this->assertTrue(true, 'minItems validation: ignores non-arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minItems validation: ignores non-arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minItems validation: ignores non-arrays. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testAdditionalProperties(): void
    {
        $schema = Schema::fromJson('{"properties":{"foo":{},"bar":{}},"patternProperties":{"^v":{}},"additionalProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'additionalProperties being false does not allow other properties: no additional properties is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'additionalProperties being false does not allow other properties: no additional properties is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additionalProperties being false does not allow other properties: no additional properties is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2,"quux":"boom"}'));
            $this->assertTrue(false, 'additionalProperties being false does not allow other properties: an additional property is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'additionalProperties being false does not allow other properties: an additional property is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additionalProperties being false does not allow other properties: an additional property is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2,3]'));
            $this->assertTrue(true, 'additionalProperties being false does not allow other properties: ignores arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'additionalProperties being false does not allow other properties: ignores arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additionalProperties being false does not allow other properties: ignores arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foobarbaz"'));
            $this->assertTrue(true, 'additionalProperties being false does not allow other properties: ignores strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'additionalProperties being false does not allow other properties: ignores strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additionalProperties being false does not allow other properties: ignores strings. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'additionalProperties being false does not allow other properties: ignores other non-objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'additionalProperties being false does not allow other properties: ignores other non-objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additionalProperties being false does not allow other properties: ignores other non-objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"vroom":2}'));
            $this->assertTrue(true, 'additionalProperties being false does not allow other properties: patternProperties are not additional properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'additionalProperties being false does not allow other properties: patternProperties are not additional properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additionalProperties being false does not allow other properties: patternProperties are not additional properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"patternProperties":{"^\u00e1":{}},"additionalProperties":false}');
        try {
            $schema->validate(json_decode('{"\u00e1rm\u00e1nyos":2}'));
            $this->assertTrue(true, 'non-ASCII pattern with additionalProperties: matching the pattern is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'non-ASCII pattern with additionalProperties: matching the pattern is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'non-ASCII pattern with additionalProperties: matching the pattern is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"\u00e9lm\u00e9ny":2}'));
            $this->assertTrue(false, 'non-ASCII pattern with additionalProperties: not matching the pattern is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'non-ASCII pattern with additionalProperties: not matching the pattern is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'non-ASCII pattern with additionalProperties: not matching the pattern is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"foo":{},"bar":{}},"additionalProperties":{"type":"boolean"}}');
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'additionalProperties allows a schema which should validate: no additional properties is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'additionalProperties allows a schema which should validate: no additional properties is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additionalProperties allows a schema which should validate: no additional properties is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2,"quux":true}'));
            $this->assertTrue(true, 'additionalProperties allows a schema which should validate: an additional valid property is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'additionalProperties allows a schema which should validate: an additional valid property is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additionalProperties allows a schema which should validate: an additional valid property is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2,"quux":12}'));
            $this->assertTrue(false, 'additionalProperties allows a schema which should validate: an additional invalid property is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'additionalProperties allows a schema which should validate: an additional invalid property is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additionalProperties allows a schema which should validate: an additional invalid property is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"additionalProperties":{"type":"boolean"}}');
        try {
            $schema->validate(json_decode('{"foo":true}'));
            $this->assertTrue(true, 'additionalProperties can exist by itself: an additional valid property is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'additionalProperties can exist by itself: an additional valid property is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additionalProperties can exist by itself: an additional valid property is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(false, 'additionalProperties can exist by itself: an additional invalid property is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'additionalProperties can exist by itself: an additional invalid property is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additionalProperties can exist by itself: an additional invalid property is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"foo":{},"bar":{}}}');
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2,"quux":true}'));
            $this->assertTrue(true, 'additionalProperties are allowed by default: additional properties are allowed. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'additionalProperties are allowed by default: additional properties are allowed. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additionalProperties are allowed by default: additional properties are allowed. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[{"properties":{"foo":{}}}],"additionalProperties":{"type":"boolean"}}');
        try {
            $schema->validate(json_decode('{"foo":1,"bar":true}'));
            $this->assertTrue(false, 'additionalProperties should not look in applicators: properties defined in allOf are not examined. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'additionalProperties should not look in applicators: properties defined in allOf are not examined. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additionalProperties should not look in applicators: properties defined in allOf are not examined. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testExclusiveMinimum(): void
    {
        $schema = Schema::fromJson('{"exclusiveMinimum":1.1}');
        try {
            $schema->validate(json_decode('1.2'));
            $this->assertTrue(true, 'exclusiveMinimum validation: above the exclusiveMinimum is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'exclusiveMinimum validation: above the exclusiveMinimum is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'exclusiveMinimum validation: above the exclusiveMinimum is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1.1'));
            $this->assertTrue(false, 'exclusiveMinimum validation: boundary point is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'exclusiveMinimum validation: boundary point is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'exclusiveMinimum validation: boundary point is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0.6'));
            $this->assertTrue(false, 'exclusiveMinimum validation: below the exclusiveMinimum is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'exclusiveMinimum validation: below the exclusiveMinimum is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'exclusiveMinimum validation: below the exclusiveMinimum is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"x"'));
            $this->assertTrue(true, 'exclusiveMinimum validation: ignores non-numbers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'exclusiveMinimum validation: ignores non-numbers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'exclusiveMinimum validation: ignores non-numbers. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testConst(): void
    {
        $schema = Schema::fromJson('{"const":2}');
        try {
            $schema->validate(json_decode('2'));
            $this->assertTrue(true, 'const validation: same value is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const validation: same value is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const validation: same value is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('5'));
            $this->assertTrue(false, 'const validation: another value is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const validation: another value is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const validation: another value is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"a"'));
            $this->assertTrue(false, 'const validation: another type is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const validation: another type is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const validation: another type is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":{"foo":"bar","baz":"bax"}}');
        try {
            $schema->validate(json_decode('{"foo":"bar","baz":"bax"}'));
            $this->assertTrue(true, 'const with object: same object is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with object: same object is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with object: same object is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"baz":"bax","foo":"bar"}'));
            $this->assertTrue(true, 'const with object: same object with different property order is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with object: same object with different property order is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with object: same object with different property order is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"bar"}'));
            $this->assertTrue(false, 'const with object: another object is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with object: another object is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with object: another object is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2]'));
            $this->assertTrue(false, 'const with object: another type is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with object: another type is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with object: another type is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":[{"foo":"bar"}]}');
        try {
            $schema->validate(json_decode('[{"foo":"bar"}]'));
            $this->assertTrue(true, 'const with array: same array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with array: same array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with array: same array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[2]'));
            $this->assertTrue(false, 'const with array: another array item is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with array: another array item is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with array: another array item is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2,3]'));
            $this->assertTrue(false, 'const with array: array with additional items is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with array: array with additional items is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with array: array with additional items is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":null}');
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'const with null: null is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with null: null is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with null: null is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(false, 'const with null: not null is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with null: not null is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with null: not null is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":false}');
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'const with false does not match 0: false is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with false does not match 0: false is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with false does not match 0: false is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(false, 'const with false does not match 0: integer zero is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with false does not match 0: integer zero is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with false does not match 0: integer zero is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(false, 'const with false does not match 0: float zero is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with false does not match 0: float zero is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with false does not match 0: float zero is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":true}');
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(true, 'const with true does not match 1: true is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with true does not match 1: true is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with true does not match 1: true is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'const with true does not match 1: integer one is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with true does not match 1: integer one is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with true does not match 1: integer one is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'const with true does not match 1: float one is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with true does not match 1: float one is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with true does not match 1: float one is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":[false]}');
        try {
            $schema->validate(json_decode('[false]'));
            $this->assertTrue(true, 'const with [false] does not match [0]: [false] is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with [false] does not match [0]: [false] is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with [false] does not match [0]: [false] is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[0]'));
            $this->assertTrue(false, 'const with [false] does not match [0]: [0] is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with [false] does not match [0]: [0] is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with [false] does not match [0]: [0] is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[0]'));
            $this->assertTrue(false, 'const with [false] does not match [0]: [0.0] is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with [false] does not match [0]: [0.0] is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with [false] does not match [0]: [0.0] is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":[true]}');
        try {
            $schema->validate(json_decode('[true]'));
            $this->assertTrue(true, 'const with [true] does not match [1]: [true] is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with [true] does not match [1]: [true] is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with [true] does not match [1]: [true] is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(false, 'const with [true] does not match [1]: [1] is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with [true] does not match [1]: [1] is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with [true] does not match [1]: [1] is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(false, 'const with [true] does not match [1]: [1.0] is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with [true] does not match [1]: [1.0] is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with [true] does not match [1]: [1.0] is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":{"a":false}}');
        try {
            $schema->validate(json_decode('{"a":false}'));
            $this->assertTrue(true, 'const with {"a": false} does not match {"a": 0}: {"a": false} is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with {"a": false} does not match {"a": 0}: {"a": false} is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with {"a": false} does not match {"a": 0}: {"a": false} is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":0}'));
            $this->assertTrue(false, 'const with {"a": false} does not match {"a": 0}: {"a": 0} is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with {"a": false} does not match {"a": 0}: {"a": 0} is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with {"a": false} does not match {"a": 0}: {"a": 0} is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":0}'));
            $this->assertTrue(false, 'const with {"a": false} does not match {"a": 0}: {"a": 0.0} is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with {"a": false} does not match {"a": 0}: {"a": 0.0} is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with {"a": false} does not match {"a": 0}: {"a": 0.0} is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":{"a":true}}');
        try {
            $schema->validate(json_decode('{"a":true}'));
            $this->assertTrue(true, 'const with {"a": true} does not match {"a": 1}: {"a": true} is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with {"a": true} does not match {"a": 1}: {"a": true} is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with {"a": true} does not match {"a": 1}: {"a": true} is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":1}'));
            $this->assertTrue(false, 'const with {"a": true} does not match {"a": 1}: {"a": 1} is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with {"a": true} does not match {"a": 1}: {"a": 1} is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with {"a": true} does not match {"a": 1}: {"a": 1} is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":1}'));
            $this->assertTrue(false, 'const with {"a": true} does not match {"a": 1}: {"a": 1.0} is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with {"a": true} does not match {"a": 1}: {"a": 1.0} is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with {"a": true} does not match {"a": 1}: {"a": 1.0} is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":0}');
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(false, 'const with 0 does not match other zero-like types: false is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with 0 does not match other zero-like types: false is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with 0 does not match other zero-like types: false is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(true, 'const with 0 does not match other zero-like types: integer zero is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with 0 does not match other zero-like types: integer zero is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with 0 does not match other zero-like types: integer zero is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(true, 'const with 0 does not match other zero-like types: float zero is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with 0 does not match other zero-like types: float zero is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with 0 does not match other zero-like types: float zero is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(false, 'const with 0 does not match other zero-like types: empty object is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with 0 does not match other zero-like types: empty object is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with 0 does not match other zero-like types: empty object is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'const with 0 does not match other zero-like types: empty array is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with 0 does not match other zero-like types: empty array is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with 0 does not match other zero-like types: empty array is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('""'));
            $this->assertTrue(false, 'const with 0 does not match other zero-like types: empty string is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with 0 does not match other zero-like types: empty string is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with 0 does not match other zero-like types: empty string is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":1}');
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(false, 'const with 1 does not match true: true is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with 1 does not match true: true is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with 1 does not match true: true is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'const with 1 does not match true: integer one is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with 1 does not match true: integer one is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with 1 does not match true: integer one is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'const with 1 does not match true: float one is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with 1 does not match true: float one is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with 1 does not match true: float one is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":-2}');
        try {
            $schema->validate(json_decode('-2'));
            $this->assertTrue(true, 'const with -2.0 matches integer and float types: integer -2 is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with -2.0 matches integer and float types: integer -2 is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with -2.0 matches integer and float types: integer -2 is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('2'));
            $this->assertTrue(false, 'const with -2.0 matches integer and float types: integer 2 is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with -2.0 matches integer and float types: integer 2 is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with -2.0 matches integer and float types: integer 2 is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('-2'));
            $this->assertTrue(true, 'const with -2.0 matches integer and float types: float -2.0 is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'const with -2.0 matches integer and float types: float -2.0 is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with -2.0 matches integer and float types: float -2.0 is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('2'));
            $this->assertTrue(false, 'const with -2.0 matches integer and float types: float 2.0 is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with -2.0 matches integer and float types: float 2.0 is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with -2.0 matches integer and float types: float 2.0 is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('-2.00001'));
            $this->assertTrue(false, 'const with -2.0 matches integer and float types: float -2.00001 is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'const with -2.0 matches integer and float types: float -2.00001 is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'const with -2.0 matches integer and float types: float -2.00001 is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":9007199254740992}');
        try {
            $schema->validate(json_decode('9007199254740992'));
            $this->assertTrue(true, 'float and integers are equal up to 64-bit representation limits: integer is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'float and integers are equal up to 64-bit representation limits: integer is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'float and integers are equal up to 64-bit representation limits: integer is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('9007199254740991'));
            $this->assertTrue(false, 'float and integers are equal up to 64-bit representation limits: integer minus one is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'float and integers are equal up to 64-bit representation limits: integer minus one is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'float and integers are equal up to 64-bit representation limits: integer minus one is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('9007199254740992'));
            $this->assertTrue(true, 'float and integers are equal up to 64-bit representation limits: float is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'float and integers are equal up to 64-bit representation limits: float is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'float and integers are equal up to 64-bit representation limits: float is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('9007199254740991'));
            $this->assertTrue(false, 'float and integers are equal up to 64-bit representation limits: float minus one is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'float and integers are equal up to 64-bit representation limits: float minus one is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'float and integers are equal up to 64-bit representation limits: float minus one is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"const":"hello\u0000there"}');
        try {
            $schema->validate(json_decode('"hello\u0000there"'));
            $this->assertTrue(true, 'nul characters in strings: match string with nul. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'nul characters in strings: match string with nul. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nul characters in strings: match string with nul. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"hellothere"'));
            $this->assertTrue(false, 'nul characters in strings: do not match string lacking nul. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'nul characters in strings: do not match string lacking nul. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nul characters in strings: do not match string lacking nul. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testDefs(): void
    {
        $schema = Schema::fromJson('{"$ref":"https:\/\/json-schema.org\/draft\/2020-12\/schema"}');
        try {
            $schema->validate(json_decode('{"$defs":{"foo":{"type":"integer"}}}'));
            $this->assertTrue(true, 'validate definition against metaschema: valid definition schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validate definition against metaschema: valid definition schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validate definition against metaschema: valid definition schema. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$defs":{"foo":{"type":1}}}'));
            $this->assertTrue(false, 'validate definition against metaschema: invalid definition schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'validate definition against metaschema: invalid definition schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validate definition against metaschema: invalid definition schema. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testAnyOf(): void
    {
        $schema = Schema::fromJson('{"anyOf":[{"type":"integer"},{"minimum":2}]}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'anyOf: first anyOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'anyOf: first anyOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf: first anyOf valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('2.5'));
            $this->assertTrue(true, 'anyOf: second anyOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'anyOf: second anyOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf: second anyOf valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('3'));
            $this->assertTrue(true, 'anyOf: both anyOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'anyOf: both anyOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf: both anyOf valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1.5'));
            $this->assertTrue(false, 'anyOf: neither anyOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'anyOf: neither anyOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf: neither anyOf valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"string","anyOf":[{"maxLength":2},{"minLength":4}]}');
        try {
            $schema->validate(json_decode('3'));
            $this->assertTrue(false, 'anyOf with base schema: mismatch base schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'anyOf with base schema: mismatch base schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf with base schema: mismatch base schema. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foobar"'));
            $this->assertTrue(true, 'anyOf with base schema: one anyOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'anyOf with base schema: one anyOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf with base schema: one anyOf valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'anyOf with base schema: both anyOf invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'anyOf with base schema: both anyOf invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf with base schema: both anyOf invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"anyOf":[true,true]}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'anyOf with boolean schemas, all true: any value is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'anyOf with boolean schemas, all true: any value is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf with boolean schemas, all true: any value is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"anyOf":[true,false]}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'anyOf with boolean schemas, some true: any value is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'anyOf with boolean schemas, some true: any value is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf with boolean schemas, some true: any value is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"anyOf":[false,false]}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'anyOf with boolean schemas, all false: any value is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'anyOf with boolean schemas, all false: any value is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf with boolean schemas, all false: any value is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"anyOf":[{"properties":{"bar":{"type":"integer"}},"required":["bar"]},{"properties":{"foo":{"type":"string"}},"required":["foo"]}]}');
        try {
            $schema->validate(json_decode('{"bar":2}'));
            $this->assertTrue(true, 'anyOf complex types: first anyOf valid (complex). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'anyOf complex types: first anyOf valid (complex). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf complex types: first anyOf valid (complex). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"baz"}'));
            $this->assertTrue(true, 'anyOf complex types: second anyOf valid (complex). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'anyOf complex types: second anyOf valid (complex). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf complex types: second anyOf valid (complex). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"baz","bar":2}'));
            $this->assertTrue(true, 'anyOf complex types: both anyOf valid (complex). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'anyOf complex types: both anyOf valid (complex). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf complex types: both anyOf valid (complex). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":2,"bar":"quux"}'));
            $this->assertTrue(false, 'anyOf complex types: neither anyOf valid (complex). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'anyOf complex types: neither anyOf valid (complex). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf complex types: neither anyOf valid (complex). Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"anyOf":[{"type":"number"},{}]}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'anyOf with one empty schema: string is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'anyOf with one empty schema: string is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf with one empty schema: string is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('123'));
            $this->assertTrue(true, 'anyOf with one empty schema: number is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'anyOf with one empty schema: number is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'anyOf with one empty schema: number is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"anyOf":[{"anyOf":[{"type":"null"}]}]}');
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'nested anyOf, to check validation semantics: null is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'nested anyOf, to check validation semantics: null is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested anyOf, to check validation semantics: null is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('123'));
            $this->assertTrue(false, 'nested anyOf, to check validation semantics: anything non-null is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'nested anyOf, to check validation semantics: anything non-null is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested anyOf, to check validation semantics: anything non-null is invalid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testPropertyNames(): void
    {
        $schema = Schema::fromJson('{"propertyNames":{"maxLength":3}}');
        try {
            $schema->validate(json_decode('{"f":{},"foo":{}}'));
            $this->assertTrue(true, 'propertyNames validation: all property names valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'propertyNames validation: all property names valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'propertyNames validation: all property names valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":{},"foobar":{}}'));
            $this->assertTrue(false, 'propertyNames validation: some property names invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'propertyNames validation: some property names invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'propertyNames validation: some property names invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'propertyNames validation: object without properties is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'propertyNames validation: object without properties is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'propertyNames validation: object without properties is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2,3,4]'));
            $this->assertTrue(true, 'propertyNames validation: ignores arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'propertyNames validation: ignores arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'propertyNames validation: ignores arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foobar"'));
            $this->assertTrue(true, 'propertyNames validation: ignores strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'propertyNames validation: ignores strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'propertyNames validation: ignores strings. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'propertyNames validation: ignores other non-objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'propertyNames validation: ignores other non-objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'propertyNames validation: ignores other non-objects. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"propertyNames":true}');
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'propertyNames with boolean schema true: object with any properties is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'propertyNames with boolean schema true: object with any properties is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'propertyNames with boolean schema true: object with any properties is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'propertyNames with boolean schema true: empty object is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'propertyNames with boolean schema true: empty object is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'propertyNames with boolean schema true: empty object is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"propertyNames":false}');
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(false, 'propertyNames with boolean schema false: object with any properties is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'propertyNames with boolean schema false: object with any properties is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'propertyNames with boolean schema false: object with any properties is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'propertyNames with boolean schema false: empty object is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'propertyNames with boolean schema false: empty object is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'propertyNames with boolean schema false: empty object is valid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testUnknownKeyword(): void
    {
        $schema = Schema::fromJson('{"$defs":{"id_in_unknown0":{"not":{"array_of_schemas":[{"$id":"https:\/\/localhost:1234\/unknownKeyword\/my_identifier.json","type":"null"}]}},"real_id_in_schema":{"$id":"https:\/\/localhost:1234\/unknownKeyword\/my_identifier.json","type":"string"},"id_in_unknown1":{"not":{"object_of_schemas":{"foo":{"$id":"https:\/\/localhost:1234\/unknownKeyword\/my_identifier.json","type":"integer"}}}}},"anyOf":[{"$ref":"#\/$defs\/id_in_unknown0"},{"$ref":"#\/$defs\/id_in_unknown1"},{"$ref":"https:\/\/localhost:1234\/unknownKeyword\/my_identifier.json"}]}');
        try {
            $schema->validate(json_decode('"a string"'));
            $this->assertTrue(true, '$id inside an unknown keyword is not a real identifier: type matches second anyOf, which has a real schema in it. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, '$id inside an unknown keyword is not a real identifier: type matches second anyOf, which has a real schema in it. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$id inside an unknown keyword is not a real identifier: type matches second anyOf, which has a real schema in it. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(false, '$id inside an unknown keyword is not a real identifier: type matches non-schema in first anyOf. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, '$id inside an unknown keyword is not a real identifier: type matches non-schema in first anyOf. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$id inside an unknown keyword is not a real identifier: type matches non-schema in first anyOf. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, '$id inside an unknown keyword is not a real identifier: type matches non-schema in third anyOf. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, '$id inside an unknown keyword is not a real identifier: type matches non-schema in third anyOf. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$id inside an unknown keyword is not a real identifier: type matches non-schema in third anyOf. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testUnevaluatedItems(): void
    {
        $schema = Schema::fromJson('{"type":"array","unevaluatedItems":true}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'unevaluatedItems true: with no unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems true: with no unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems true: with no unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo"]'));
            $this->assertTrue(true, 'unevaluatedItems true: with unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems true: with unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems true: with unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'unevaluatedItems false: with no unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems false: with no unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems false: with no unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo"]'));
            $this->assertTrue(false, 'unevaluatedItems false: with unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems false: with unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems false: with unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","unevaluatedItems":{"type":"string"}}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'unevaluatedItems as schema: with no unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems as schema: with no unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems as schema: with no unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo"]'));
            $this->assertTrue(true, 'unevaluatedItems as schema: with valid unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems as schema: with valid unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems as schema: with valid unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[42]'));
            $this->assertTrue(false, 'unevaluatedItems as schema: with invalid unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems as schema: with invalid unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems as schema: with invalid unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","items":{"type":"string"},"unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('["foo","bar"]'));
            $this->assertTrue(true, 'unevaluatedItems with uniform items: unevaluatedItems doesn\'t apply. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with uniform items: unevaluatedItems doesn\'t apply. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with uniform items: unevaluatedItems doesn\'t apply. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","prefixItems":[{"type":"string"}],"unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('["foo"]'));
            $this->assertTrue(true, 'unevaluatedItems with tuple: with no unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with tuple: with no unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with tuple: with no unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo","bar"]'));
            $this->assertTrue(false, 'unevaluatedItems with tuple: with unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems with tuple: with unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with tuple: with unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","prefixItems":[{"type":"string"}],"items":true,"unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('["foo",42]'));
            $this->assertTrue(true, 'unevaluatedItems with items: unevaluatedItems doesn\'t apply. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with items: unevaluatedItems doesn\'t apply. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with items: unevaluatedItems doesn\'t apply. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","prefixItems":[{"type":"string"}],"allOf":[{"prefixItems":[true,{"type":"number"}]}],"unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('["foo",42]'));
            $this->assertTrue(true, 'unevaluatedItems with nested tuple: with no unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with nested tuple: with no unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with nested tuple: with no unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo",42,true]'));
            $this->assertTrue(false, 'unevaluatedItems with nested tuple: with unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems with nested tuple: with unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with nested tuple: with unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","allOf":[{"prefixItems":[{"type":"string"}],"items":true}],"unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('["foo"]'));
            $this->assertTrue(true, 'unevaluatedItems with nested items: with no additional items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with nested items: with no additional items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with nested items: with no additional items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo",42,true]'));
            $this->assertTrue(true, 'unevaluatedItems with nested items: with additional items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with nested items: with additional items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with nested items: with additional items. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","allOf":[{"prefixItems":[{"type":"string"}]},{"unevaluatedItems":true}],"unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('["foo"]'));
            $this->assertTrue(true, 'unevaluatedItems with nested unevaluatedItems: with no additional items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with nested unevaluatedItems: with no additional items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with nested unevaluatedItems: with no additional items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo",42,true]'));
            $this->assertTrue(true, 'unevaluatedItems with nested unevaluatedItems: with additional items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with nested unevaluatedItems: with additional items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with nested unevaluatedItems: with additional items. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","prefixItems":[{"const":"foo"}],"anyOf":[{"prefixItems":[true,{"const":"bar"}]},{"prefixItems":[true,true,{"const":"baz"}]}],"unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('["foo","bar"]'));
            $this->assertTrue(true, 'unevaluatedItems with anyOf: when one schema matches and has no unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with anyOf: when one schema matches and has no unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with anyOf: when one schema matches and has no unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo","bar",42]'));
            $this->assertTrue(false, 'unevaluatedItems with anyOf: when one schema matches and has unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems with anyOf: when one schema matches and has unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with anyOf: when one schema matches and has unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo","bar","baz"]'));
            $this->assertTrue(true, 'unevaluatedItems with anyOf: when two schemas match and has no unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with anyOf: when two schemas match and has no unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with anyOf: when two schemas match and has no unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo","bar","baz",42]'));
            $this->assertTrue(false, 'unevaluatedItems with anyOf: when two schemas match and has unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems with anyOf: when two schemas match and has unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with anyOf: when two schemas match and has unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","prefixItems":[{"const":"foo"}],"oneOf":[{"prefixItems":[true,{"const":"bar"}]},{"prefixItems":[true,{"const":"baz"}]}],"unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('["foo","bar"]'));
            $this->assertTrue(true, 'unevaluatedItems with oneOf: with no unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with oneOf: with no unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with oneOf: with no unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo","bar",42]'));
            $this->assertTrue(false, 'unevaluatedItems with oneOf: with unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems with oneOf: with unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with oneOf: with unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","prefixItems":[{"const":"foo"}],"not":{"not":{"prefixItems":[true,{"const":"bar"}]}},"unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('["foo","bar"]'));
            $this->assertTrue(false, 'unevaluatedItems with not: with unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems with not: with unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with not: with unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","prefixItems":[{"const":"foo"}],"if":{"prefixItems":[true,{"const":"bar"}]},"then":{"prefixItems":[true,true,{"const":"then"}]},"else":{"prefixItems":[true,true,true,{"const":"else"}]},"unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('["foo","bar","then"]'));
            $this->assertTrue(true, 'unevaluatedItems with if/then/else: when if matches and it has no unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with if/then/else: when if matches and it has no unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with if/then/else: when if matches and it has no unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo","bar","then","else"]'));
            $this->assertTrue(false, 'unevaluatedItems with if/then/else: when if matches and it has unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems with if/then/else: when if matches and it has unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with if/then/else: when if matches and it has unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo",42,42,"else"]'));
            $this->assertTrue(true, 'unevaluatedItems with if/then/else: when if doesn\'t match and it has no unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with if/then/else: when if doesn\'t match and it has no unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with if/then/else: when if doesn\'t match and it has no unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo",42,42,"else",42]'));
            $this->assertTrue(false, 'unevaluatedItems with if/then/else: when if doesn\'t match and it has unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems with if/then/else: when if doesn\'t match and it has unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with if/then/else: when if doesn\'t match and it has unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","allOf":[true],"unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'unevaluatedItems with boolean schemas: with no unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with boolean schemas: with no unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with boolean schemas: with no unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo"]'));
            $this->assertTrue(false, 'unevaluatedItems with boolean schemas: with unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems with boolean schemas: with unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with boolean schemas: with unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","$ref":"#\/$defs\/bar","prefixItems":[{"type":"string"}],"unevaluatedItems":false,"$defs":{"bar":{"prefixItems":[true,{"type":"string"}]}}}');
        try {
            $schema->validate(json_decode('["foo","bar"]'));
            $this->assertTrue(true, 'unevaluatedItems with $ref: with no unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems with $ref: with no unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with $ref: with no unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo","bar","baz"]'));
            $this->assertTrue(false, 'unevaluatedItems with $ref: with unevaluated items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems with $ref: with unevaluated items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems with $ref: with unevaluated items. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[{"prefixItems":[true]},{"unevaluatedItems":false}]}');
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(false, 'unevaluatedItems can\'t see inside cousins: always fails. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems can\'t see inside cousins: always fails. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems can\'t see inside cousins: always fails. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"array","prefixItems":[{"type":"string"}],"unevaluatedItems":false}},"anyOf":[{"properties":{"foo":{"prefixItems":[true,{"type":"string"}]}}}]}');
        try {
            $schema->validate(json_decode('{"foo":["test"]}'));
            $this->assertTrue(true, 'item is evaluated in an uncle schema to unevaluatedItems: no extra items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'item is evaluated in an uncle schema to unevaluatedItems: no extra items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'item is evaluated in an uncle schema to unevaluatedItems: no extra items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":["test","test"]}'));
            $this->assertTrue(false, 'item is evaluated in an uncle schema to unevaluatedItems: uncle keyword evaluation is not significant. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'item is evaluated in an uncle schema to unevaluatedItems: uncle keyword evaluation is not significant. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'item is evaluated in an uncle schema to unevaluatedItems: uncle keyword evaluation is not significant. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"prefixItems":[true],"contains":{"type":"string"},"unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('[1,"foo"]'));
            $this->assertTrue(true, 'unevaluatedItems depends on adjacent contains: second item is evaluated by contains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems depends on adjacent contains: second item is evaluated by contains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems depends on adjacent contains: second item is evaluated by contains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2]'));
            $this->assertTrue(false, 'unevaluatedItems depends on adjacent contains: contains fails, second item is not evaluated. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems depends on adjacent contains: contains fails, second item is not evaluated. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems depends on adjacent contains: contains fails, second item is not evaluated. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2,"foo"]'));
            $this->assertTrue(false, 'unevaluatedItems depends on adjacent contains: contains passes, second item is not evaluated. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems depends on adjacent contains: contains passes, second item is not evaluated. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems depends on adjacent contains: contains passes, second item is not evaluated. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[{"contains":{"multipleOf":2}},{"contains":{"multipleOf":3}}],"unevaluatedItems":{"multipleOf":5}}');
        try {
            $schema->validate(json_decode('[2,3,4,5,6]'));
            $this->assertTrue(true, 'unevaluatedItems depends on multiple nested contains: 5 not evaluated, passes unevaluatedItems. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems depends on multiple nested contains: 5 not evaluated, passes unevaluatedItems. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems depends on multiple nested contains: 5 not evaluated, passes unevaluatedItems. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[2,3,4,7,8]'));
            $this->assertTrue(false, 'unevaluatedItems depends on multiple nested contains: 7 not evaluated, fails unevaluatedItems. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems depends on multiple nested contains: 7 not evaluated, fails unevaluatedItems. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems depends on multiple nested contains: 7 not evaluated, fails unevaluatedItems. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"if":{"contains":{"const":"a"}},"then":{"if":{"contains":{"const":"b"}},"then":{"if":{"contains":{"const":"c"}}}},"unevaluatedItems":false}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'unevaluatedItems and contains interact to control item dependency relationship: empty array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: empty array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: empty array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["a","a"]'));
            $this->assertTrue(true, 'unevaluatedItems and contains interact to control item dependency relationship: only a\'s are valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: only a\'s are valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: only a\'s are valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["a","b","a","b","a"]'));
            $this->assertTrue(true, 'unevaluatedItems and contains interact to control item dependency relationship: a\'s and b\'s are valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: a\'s and b\'s are valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: a\'s and b\'s are valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["c","a","c","c","b","a"]'));
            $this->assertTrue(true, 'unevaluatedItems and contains interact to control item dependency relationship: a\'s, b\'s and c\'s are valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: a\'s, b\'s and c\'s are valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: a\'s, b\'s and c\'s are valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["b","b"]'));
            $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: only b\'s are invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems and contains interact to control item dependency relationship: only b\'s are invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: only b\'s are invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["c","c"]'));
            $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: only c\'s are invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems and contains interact to control item dependency relationship: only c\'s are invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: only c\'s are invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["c","b","c","b","c"]'));
            $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: only b\'s and c\'s are invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems and contains interact to control item dependency relationship: only b\'s and c\'s are invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: only b\'s and c\'s are invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["c","a","c","a","c"]'));
            $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: only a\'s and c\'s are invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedItems and contains interact to control item dependency relationship: only a\'s and c\'s are invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedItems and contains interact to control item dependency relationship: only a\'s and c\'s are invalid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testVocabulary(): void
    {
        $schema = Schema::fromJson('{"$id":"https:\/\/schema\/using\/no\/validation","$schema":"http:\/\/localhost:1234\/draft2020-12\/metaschema-no-validation.json","properties":{"badProperty":false,"numberProperty":{"minimum":10}}}');
        try {
            $schema->validate(json_decode('{"badProperty":"this property should not exist"}'));
            $this->assertTrue(false, 'schema that uses custom metaschema with with no validation vocabulary: applicator vocabulary still works. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'schema that uses custom metaschema with with no validation vocabulary: applicator vocabulary still works. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'schema that uses custom metaschema with with no validation vocabulary: applicator vocabulary still works. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"numberProperty":20}'));
            $this->assertTrue(true, 'schema that uses custom metaschema with with no validation vocabulary: no validation: valid number. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'schema that uses custom metaschema with with no validation vocabulary: no validation: valid number. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'schema that uses custom metaschema with with no validation vocabulary: no validation: valid number. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"numberProperty":1}'));
            $this->assertTrue(true, 'schema that uses custom metaschema with with no validation vocabulary: no validation: invalid number, but it still validates. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'schema that uses custom metaschema with with no validation vocabulary: no validation: invalid number, but it still validates. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'schema that uses custom metaschema with with no validation vocabulary: no validation: invalid number, but it still validates. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testBoolean_schema(): void
    {
        $schema = Schema::fromJson('true');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'boolean schema \'true\': number is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'boolean schema \'true\': number is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'true\': number is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'boolean schema \'true\': string is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'boolean schema \'true\': string is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'true\': string is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(true, 'boolean schema \'true\': boolean true is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'boolean schema \'true\': boolean true is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'true\': boolean true is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'boolean schema \'true\': boolean false is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'boolean schema \'true\': boolean false is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'true\': boolean false is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'boolean schema \'true\': null is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'boolean schema \'true\': null is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'true\': null is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"bar"}'));
            $this->assertTrue(true, 'boolean schema \'true\': object is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'boolean schema \'true\': object is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'true\': object is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'boolean schema \'true\': empty object is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'boolean schema \'true\': empty object is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'true\': empty object is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo"]'));
            $this->assertTrue(true, 'boolean schema \'true\': array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'boolean schema \'true\': array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'true\': array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'boolean schema \'true\': empty array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'boolean schema \'true\': empty array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'true\': empty array is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('false');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'boolean schema \'false\': number is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean schema \'false\': number is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'false\': number is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'boolean schema \'false\': string is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean schema \'false\': string is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'false\': string is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(false, 'boolean schema \'false\': boolean true is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean schema \'false\': boolean true is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'false\': boolean true is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(false, 'boolean schema \'false\': boolean false is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean schema \'false\': boolean false is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'false\': boolean false is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(false, 'boolean schema \'false\': null is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean schema \'false\': null is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'false\': null is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"bar"}'));
            $this->assertTrue(false, 'boolean schema \'false\': object is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean schema \'false\': object is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'false\': object is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(false, 'boolean schema \'false\': empty object is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean schema \'false\': empty object is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'false\': empty object is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo"]'));
            $this->assertTrue(false, 'boolean schema \'false\': array is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean schema \'false\': array is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'false\': array is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'boolean schema \'false\': empty array is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean schema \'false\': empty array is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean schema \'false\': empty array is invalid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testNot(): void
    {
        $schema = Schema::fromJson('{"not":{"type":"integer"}}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'not: allowed. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'not: allowed. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'not: allowed. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'not: disallowed. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'not: disallowed. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'not: disallowed. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"not":{"type":["integer","boolean"]}}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'not multiple types: valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'not multiple types: valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'not multiple types: valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'not multiple types: mismatch. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'not multiple types: mismatch. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'not multiple types: mismatch. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(false, 'not multiple types: other mismatch. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'not multiple types: other mismatch. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'not multiple types: other mismatch. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"not":{"type":"object","properties":{"foo":{"type":"string"}}}}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'not more complex schema: match. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'not more complex schema: match. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'not more complex schema: match. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'not more complex schema: other match. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'not more complex schema: other match. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'not more complex schema: other match. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"bar"}'));
            $this->assertTrue(false, 'not more complex schema: mismatch. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'not more complex schema: mismatch. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'not more complex schema: mismatch. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"foo":{"not":{}}}}');
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2}'));
            $this->assertTrue(false, 'forbidden property: property present. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'forbidden property: property present. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'forbidden property: property present. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":1,"baz":2}'));
            $this->assertTrue(true, 'forbidden property: property absent. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'forbidden property: property absent. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'forbidden property: property absent. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"not":true}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'not with boolean schema true: any value is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'not with boolean schema true: any value is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'not with boolean schema true: any value is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"not":false}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'not with boolean schema false: any value is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'not with boolean schema false: any value is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'not with boolean schema false: any value is valid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testItems(): void
    {
        $schema = Schema::fromJson('{"items":{"type":"integer"}}');
        try {
            $schema->validate(json_decode('[1,2,3]'));
            $this->assertTrue(true, 'a schema given for items: valid items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'a schema given for items: valid items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'a schema given for items: valid items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,"x"]'));
            $this->assertTrue(false, 'a schema given for items: wrong type of items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'a schema given for items: wrong type of items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'a schema given for items: wrong type of items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"bar"}'));
            $this->assertTrue(true, 'a schema given for items: ignores non-arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'a schema given for items: ignores non-arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'a schema given for items: ignores non-arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"0":"invalid","length":1}'));
            $this->assertTrue(true, 'a schema given for items: JavaScript pseudo-array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'a schema given for items: JavaScript pseudo-array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'a schema given for items: JavaScript pseudo-array is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"items":true}');
        try {
            $schema->validate(json_decode('[1,"foo",true]'));
            $this->assertTrue(true, 'items with boolean schema (true): any array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'items with boolean schema (true): any array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items with boolean schema (true): any array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'items with boolean schema (true): empty array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'items with boolean schema (true): empty array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items with boolean schema (true): empty array is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"items":false}');
        try {
            $schema->validate(json_decode('[1,"foo",true]'));
            $this->assertTrue(false, 'items with boolean schema (false): any non-empty array is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'items with boolean schema (false): any non-empty array is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items with boolean schema (false): any non-empty array is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'items with boolean schema (false): empty array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'items with boolean schema (false): empty array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items with boolean schema (false): empty array is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$defs":{"item":{"type":"array","items":false,"prefixItems":[{"$ref":"#\/$defs\/sub-item"},{"$ref":"#\/$defs\/sub-item"}]},"sub-item":{"type":"object","required":["foo"]}},"type":"array","items":false,"prefixItems":[{"$ref":"#\/$defs\/item"},{"$ref":"#\/$defs\/item"},{"$ref":"#\/$defs\/item"}]}');
        try {
            $schema->validate(json_decode('[[{"foo":null},{"foo":null}],[{"foo":null},{"foo":null}],[{"foo":null},{"foo":null}]]'));
            $this->assertTrue(true, 'items and subitems: valid items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'items and subitems: valid items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items and subitems: valid items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[[{"foo":null},{"foo":null}],[{"foo":null},{"foo":null}],[{"foo":null},{"foo":null}],[{"foo":null},{"foo":null}]]'));
            $this->assertTrue(false, 'items and subitems: too many items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'items and subitems: too many items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items and subitems: too many items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[[{"foo":null},{"foo":null},{"foo":null}],[{"foo":null},{"foo":null}],[{"foo":null},{"foo":null}]]'));
            $this->assertTrue(false, 'items and subitems: too many sub-items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'items and subitems: too many sub-items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items and subitems: too many sub-items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[{"foo":null},[{"foo":null},{"foo":null}],[{"foo":null},{"foo":null}]]'));
            $this->assertTrue(false, 'items and subitems: wrong item. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'items and subitems: wrong item. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items and subitems: wrong item. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[[{},{"foo":null}],[{"foo":null},{"foo":null}],[{"foo":null},{"foo":null}]]'));
            $this->assertTrue(false, 'items and subitems: wrong sub-item. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'items and subitems: wrong sub-item. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items and subitems: wrong sub-item. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[[{"foo":null}],[{"foo":null}]]'));
            $this->assertTrue(true, 'items and subitems: fewer items is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'items and subitems: fewer items is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items and subitems: fewer items is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array","items":{"type":"array","items":{"type":"array","items":{"type":"array","items":{"type":"number"}}}}}');
        try {
            $schema->validate(json_decode('[[[[1]],[[2],[3]]],[[[4],[5],[6]]]]'));
            $this->assertTrue(true, 'nested items: valid nested array. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'nested items: valid nested array. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested items: valid nested array. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[[[["1"]],[[2],[3]]],[[[4],[5],[6]]]]'));
            $this->assertTrue(false, 'nested items: nested array with invalid type. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'nested items: nested array with invalid type. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested items: nested array with invalid type. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[[[1],[2],[3]],[[4],[5],[6]]]'));
            $this->assertTrue(false, 'nested items: not deep enough. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'nested items: not deep enough. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested items: not deep enough. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"prefixItems":[{},{},{}],"items":false}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'prefixItems with no additional items allowed: empty array. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'prefixItems with no additional items allowed: empty array. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'prefixItems with no additional items allowed: empty array. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(true, 'prefixItems with no additional items allowed: fewer number of items present (1). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'prefixItems with no additional items allowed: fewer number of items present (1). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'prefixItems with no additional items allowed: fewer number of items present (1). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2]'));
            $this->assertTrue(true, 'prefixItems with no additional items allowed: fewer number of items present (2). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'prefixItems with no additional items allowed: fewer number of items present (2). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'prefixItems with no additional items allowed: fewer number of items present (2). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2,3]'));
            $this->assertTrue(true, 'prefixItems with no additional items allowed: equal number of items present. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'prefixItems with no additional items allowed: equal number of items present. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'prefixItems with no additional items allowed: equal number of items present. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2,3,4]'));
            $this->assertTrue(false, 'prefixItems with no additional items allowed: additional items are not permitted. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'prefixItems with no additional items allowed: additional items are not permitted. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'prefixItems with no additional items allowed: additional items are not permitted. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[{"prefixItems":[{"minimum":3}]}],"items":{"minimum":5}}');
        try {
            $schema->validate(json_decode('[3,5]'));
            $this->assertTrue(false, 'items should not look in applicators, valid case: prefixItems in allOf should not constrain items, invalid case. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'items should not look in applicators, valid case: prefixItems in allOf should not constrain items, invalid case. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items should not look in applicators, valid case: prefixItems in allOf should not constrain items, invalid case. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[5,5]'));
            $this->assertTrue(true, 'items should not look in applicators, valid case: prefixItems in allOf should not constrain items, valid case. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'items should not look in applicators, valid case: prefixItems in allOf should not constrain items, valid case. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items should not look in applicators, valid case: prefixItems in allOf should not constrain items, valid case. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"prefixItems":[{"type":"string"}],"items":{"type":"integer"}}');
        try {
            $schema->validate(json_decode('["x",2,3]'));
            $this->assertTrue(true, 'prefixItems validation adjusts the starting index for items: valid items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'prefixItems validation adjusts the starting index for items: valid items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'prefixItems validation adjusts the starting index for items: valid items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["x","y"]'));
            $this->assertTrue(false, 'prefixItems validation adjusts the starting index for items: wrong type of second item. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'prefixItems validation adjusts the starting index for items: wrong type of second item. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'prefixItems validation adjusts the starting index for items: wrong type of second item. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testEnum(): void
    {
        $schema = Schema::fromJson('{"enum":[1,2,3]}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'simple enum validation: one of the enum is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'simple enum validation: one of the enum is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'simple enum validation: one of the enum is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('4'));
            $this->assertTrue(false, 'simple enum validation: something else is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'simple enum validation: something else is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'simple enum validation: something else is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"enum":[6,"foo",[],true,{"foo":12}]}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'heterogeneous enum validation: one of the enum is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'heterogeneous enum validation: one of the enum is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'heterogeneous enum validation: one of the enum is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(false, 'heterogeneous enum validation: something else is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'heterogeneous enum validation: something else is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'heterogeneous enum validation: something else is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":false}'));
            $this->assertTrue(false, 'heterogeneous enum validation: objects are deep compared. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'heterogeneous enum validation: objects are deep compared. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'heterogeneous enum validation: objects are deep compared. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":12}'));
            $this->assertTrue(true, 'heterogeneous enum validation: valid object matches. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'heterogeneous enum validation: valid object matches. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'heterogeneous enum validation: valid object matches. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":12,"boo":42}'));
            $this->assertTrue(false, 'heterogeneous enum validation: extra properties in object is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'heterogeneous enum validation: extra properties in object is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'heterogeneous enum validation: extra properties in object is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"enum":[6,null]}');
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'heterogeneous enum-with-null validation: null is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'heterogeneous enum-with-null validation: null is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'heterogeneous enum-with-null validation: null is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('6'));
            $this->assertTrue(true, 'heterogeneous enum-with-null validation: number is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'heterogeneous enum-with-null validation: number is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'heterogeneous enum-with-null validation: number is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"test"'));
            $this->assertTrue(false, 'heterogeneous enum-with-null validation: something else is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'heterogeneous enum-with-null validation: something else is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'heterogeneous enum-with-null validation: something else is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"enum":["foo"]},"bar":{"enum":["bar"]}},"required":["bar"]}');
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(true, 'enums in properties: both properties are valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'enums in properties: both properties are valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enums in properties: both properties are valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foot","bar":"bar"}'));
            $this->assertTrue(false, 'enums in properties: wrong foo value. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'enums in properties: wrong foo value. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enums in properties: wrong foo value. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bart"}'));
            $this->assertTrue(false, 'enums in properties: wrong bar value. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'enums in properties: wrong bar value. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enums in properties: wrong bar value. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":"bar"}'));
            $this->assertTrue(true, 'enums in properties: missing optional property is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'enums in properties: missing optional property is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enums in properties: missing optional property is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(false, 'enums in properties: missing required property is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'enums in properties: missing required property is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enums in properties: missing required property is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(false, 'enums in properties: missing all properties is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'enums in properties: missing all properties is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enums in properties: missing all properties is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"enum":["foo\nbar","foo\rbar"]}');
        try {
            $schema->validate(json_decode('"foo\nbar"'));
            $this->assertTrue(true, 'enum with escaped characters: member 1 is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'enum with escaped characters: member 1 is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with escaped characters: member 1 is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo\rbar"'));
            $this->assertTrue(true, 'enum with escaped characters: member 2 is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'enum with escaped characters: member 2 is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with escaped characters: member 2 is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"abc"'));
            $this->assertTrue(false, 'enum with escaped characters: another string is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'enum with escaped characters: another string is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with escaped characters: another string is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"enum":[false]}');
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'enum with false does not match 0: false is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'enum with false does not match 0: false is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with false does not match 0: false is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(false, 'enum with false does not match 0: integer zero is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'enum with false does not match 0: integer zero is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with false does not match 0: integer zero is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(false, 'enum with false does not match 0: float zero is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'enum with false does not match 0: float zero is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with false does not match 0: float zero is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"enum":[true]}');
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(true, 'enum with true does not match 1: true is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'enum with true does not match 1: true is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with true does not match 1: true is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'enum with true does not match 1: integer one is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'enum with true does not match 1: integer one is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with true does not match 1: integer one is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'enum with true does not match 1: float one is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'enum with true does not match 1: float one is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with true does not match 1: float one is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"enum":[0]}');
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(false, 'enum with 0 does not match false: false is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'enum with 0 does not match false: false is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with 0 does not match false: false is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(true, 'enum with 0 does not match false: integer zero is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'enum with 0 does not match false: integer zero is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with 0 does not match false: integer zero is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(true, 'enum with 0 does not match false: float zero is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'enum with 0 does not match false: float zero is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with 0 does not match false: float zero is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"enum":[1]}');
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(false, 'enum with 1 does not match true: true is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'enum with 1 does not match true: true is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with 1 does not match true: true is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'enum with 1 does not match true: integer one is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'enum with 1 does not match true: integer one is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with 1 does not match true: integer one is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'enum with 1 does not match true: float one is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'enum with 1 does not match true: float one is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'enum with 1 does not match true: float one is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"enum":["hello\u0000there"]}');
        try {
            $schema->validate(json_decode('"hello\u0000there"'));
            $this->assertTrue(true, 'nul characters in strings: match string with nul. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'nul characters in strings: match string with nul. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nul characters in strings: match string with nul. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"hellothere"'));
            $this->assertTrue(false, 'nul characters in strings: do not match string lacking nul. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'nul characters in strings: do not match string lacking nul. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nul characters in strings: do not match string lacking nul. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testMinProperties(): void
    {
        $schema = Schema::fromJson('{"minProperties":1}');
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2}'));
            $this->assertTrue(true, 'minProperties validation: longer is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minProperties validation: longer is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minProperties validation: longer is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'minProperties validation: exact length is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minProperties validation: exact length is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minProperties validation: exact length is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(false, 'minProperties validation: too short is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minProperties validation: too short is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minProperties validation: too short is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'minProperties validation: ignores arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minProperties validation: ignores arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minProperties validation: ignores arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('""'));
            $this->assertTrue(true, 'minProperties validation: ignores strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minProperties validation: ignores strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minProperties validation: ignores strings. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'minProperties validation: ignores other non-objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minProperties validation: ignores other non-objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minProperties validation: ignores other non-objects. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testMinContains(): void
    {
        $schema = Schema::fromJson('{"minContains":1}');
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(true, 'minContains without contains is ignored: one item valid against lone minContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minContains without contains is ignored: one item valid against lone minContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains without contains is ignored: one item valid against lone minContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'minContains without contains is ignored: zero items still valid against lone minContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minContains without contains is ignored: zero items still valid against lone minContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains without contains is ignored: zero items still valid against lone minContains. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contains":{"const":1},"minContains":1}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'minContains=1 with contains: empty data. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minContains=1 with contains: empty data. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains=1 with contains: empty data. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[2]'));
            $this->assertTrue(false, 'minContains=1 with contains: no elements match. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minContains=1 with contains: no elements match. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains=1 with contains: no elements match. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(true, 'minContains=1 with contains: single element matches, valid minContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minContains=1 with contains: single element matches, valid minContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains=1 with contains: single element matches, valid minContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2]'));
            $this->assertTrue(true, 'minContains=1 with contains: some elements match, valid minContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minContains=1 with contains: some elements match, valid minContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains=1 with contains: some elements match, valid minContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1]'));
            $this->assertTrue(true, 'minContains=1 with contains: all elements match, valid minContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minContains=1 with contains: all elements match, valid minContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains=1 with contains: all elements match, valid minContains. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contains":{"const":1},"minContains":2}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'minContains=2 with contains: empty data. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minContains=2 with contains: empty data. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains=2 with contains: empty data. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(false, 'minContains=2 with contains: all elements match, invalid minContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minContains=2 with contains: all elements match, invalid minContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains=2 with contains: all elements match, invalid minContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2]'));
            $this->assertTrue(false, 'minContains=2 with contains: some elements match, invalid minContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minContains=2 with contains: some elements match, invalid minContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains=2 with contains: some elements match, invalid minContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1]'));
            $this->assertTrue(true, 'minContains=2 with contains: all elements match, valid minContains (exactly as needed). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minContains=2 with contains: all elements match, valid minContains (exactly as needed). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains=2 with contains: all elements match, valid minContains (exactly as needed). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1,1]'));
            $this->assertTrue(true, 'minContains=2 with contains: all elements match, valid minContains (more than needed). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minContains=2 with contains: all elements match, valid minContains (more than needed). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains=2 with contains: all elements match, valid minContains (more than needed). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2,1]'));
            $this->assertTrue(true, 'minContains=2 with contains: some elements match, valid minContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minContains=2 with contains: some elements match, valid minContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains=2 with contains: some elements match, valid minContains. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contains":{"const":1},"maxContains":2,"minContains":2}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'maxContains = minContains: empty data. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxContains = minContains: empty data. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains = minContains: empty data. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(false, 'maxContains = minContains: all elements match, invalid minContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxContains = minContains: all elements match, invalid minContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains = minContains: all elements match, invalid minContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1,1]'));
            $this->assertTrue(false, 'maxContains = minContains: all elements match, invalid maxContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxContains = minContains: all elements match, invalid maxContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains = minContains: all elements match, invalid maxContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1]'));
            $this->assertTrue(true, 'maxContains = minContains: all elements match, valid maxContains and minContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxContains = minContains: all elements match, valid maxContains and minContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains = minContains: all elements match, valid maxContains and minContains. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contains":{"const":1},"maxContains":1,"minContains":3}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'maxContains < minContains: empty data. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxContains < minContains: empty data. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains < minContains: empty data. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(false, 'maxContains < minContains: invalid minContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxContains < minContains: invalid minContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains < minContains: invalid minContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1,1]'));
            $this->assertTrue(false, 'maxContains < minContains: invalid maxContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxContains < minContains: invalid maxContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains < minContains: invalid maxContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1]'));
            $this->assertTrue(false, 'maxContains < minContains: invalid maxContains and minContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxContains < minContains: invalid maxContains and minContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains < minContains: invalid maxContains and minContains. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contains":{"const":1},"minContains":0}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'minContains = 0: empty data. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minContains = 0: empty data. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains = 0: empty data. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[2]'));
            $this->assertTrue(true, 'minContains = 0: minContains = 0 makes contains always pass. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minContains = 0: minContains = 0 makes contains always pass. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains = 0: minContains = 0 makes contains always pass. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contains":{"const":1},"minContains":0,"maxContains":1}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'minContains = 0 with maxContains: empty data. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minContains = 0 with maxContains: empty data. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains = 0 with maxContains: empty data. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(true, 'minContains = 0 with maxContains: not more than maxContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minContains = 0 with maxContains: not more than maxContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains = 0 with maxContains: not more than maxContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1]'));
            $this->assertTrue(false, 'minContains = 0 with maxContains: too many. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minContains = 0 with maxContains: too many. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains = 0 with maxContains: too many. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testProperties(): void
    {
        $schema = Schema::fromJson('{"properties":{"foo":{"type":"integer"},"bar":{"type":"string"}}}');
        try {
            $schema->validate(json_decode('{"foo":1,"bar":"baz"}'));
            $this->assertTrue(true, 'object properties validation: both properties present and valid is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'object properties validation: both properties present and valid is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'object properties validation: both properties present and valid is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":{}}'));
            $this->assertTrue(false, 'object properties validation: one property invalid is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'object properties validation: one property invalid is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'object properties validation: one property invalid is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":[],"bar":{}}'));
            $this->assertTrue(false, 'object properties validation: both properties invalid is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'object properties validation: both properties invalid is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'object properties validation: both properties invalid is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"quux":[]}'));
            $this->assertTrue(true, 'object properties validation: doesn\'t invalidate other properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'object properties validation: doesn\'t invalidate other properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'object properties validation: doesn\'t invalidate other properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'object properties validation: ignores arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'object properties validation: ignores arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'object properties validation: ignores arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'object properties validation: ignores other non-objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'object properties validation: ignores other non-objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'object properties validation: ignores other non-objects. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"foo":{"type":"array","maxItems":3},"bar":{"type":"array"}},"patternProperties":{"f.o":{"minItems":2}},"additionalProperties":{"type":"integer"}}');
        try {
            $schema->validate(json_decode('{"foo":[1,2]}'));
            $this->assertTrue(true, 'properties, patternProperties, additionalProperties interaction: property validates property. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: property validates property. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: property validates property. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":[1,2,3,4]}'));
            $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: property invalidates property. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'properties, patternProperties, additionalProperties interaction: property invalidates property. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: property invalidates property. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":[]}'));
            $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: patternProperty invalidates property. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'properties, patternProperties, additionalProperties interaction: patternProperty invalidates property. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: patternProperty invalidates property. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"fxo":[1,2]}'));
            $this->assertTrue(true, 'properties, patternProperties, additionalProperties interaction: patternProperty validates nonproperty. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: patternProperty validates nonproperty. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: patternProperty validates nonproperty. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"fxo":[]}'));
            $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: patternProperty invalidates nonproperty. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'properties, patternProperties, additionalProperties interaction: patternProperty invalidates nonproperty. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: patternProperty invalidates nonproperty. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":[]}'));
            $this->assertTrue(true, 'properties, patternProperties, additionalProperties interaction: additionalProperty ignores property. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: additionalProperty ignores property. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: additionalProperty ignores property. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"quux":3}'));
            $this->assertTrue(true, 'properties, patternProperties, additionalProperties interaction: additionalProperty validates others. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: additionalProperty validates others. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: additionalProperty validates others. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"quux":"foo"}'));
            $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: additionalProperty invalidates others. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'properties, patternProperties, additionalProperties interaction: additionalProperty invalidates others. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties, patternProperties, additionalProperties interaction: additionalProperty invalidates others. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"foo":true,"bar":false}}');
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'properties with boolean schema: no property present is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'properties with boolean schema: no property present is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties with boolean schema: no property present is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'properties with boolean schema: only \'true\' property present is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'properties with boolean schema: only \'true\' property present is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties with boolean schema: only \'true\' property present is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":2}'));
            $this->assertTrue(false, 'properties with boolean schema: only \'false\' property present is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'properties with boolean schema: only \'false\' property present is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties with boolean schema: only \'false\' property present is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2}'));
            $this->assertTrue(false, 'properties with boolean schema: both properties present is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'properties with boolean schema: both properties present is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties with boolean schema: both properties present is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"foo\nbar":{"type":"number"},"foo\"bar":{"type":"number"},"foo\\bar":{"type":"number"},"foo\rbar":{"type":"number"},"foo\tbar":{"type":"number"},"foo\fbar":{"type":"number"}}}');
        try {
            $schema->validate(json_decode('{"foo\nbar":1,"foo\"bar":1,"foo\\bar":1,"foo\rbar":1,"foo\tbar":1,"foo\fbar":1}'));
            $this->assertTrue(true, 'properties with escaped characters: object with all numbers is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'properties with escaped characters: object with all numbers is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties with escaped characters: object with all numbers is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo\nbar":"1","foo\"bar":"1","foo\\bar":"1","foo\rbar":"1","foo\tbar":"1","foo\fbar":"1"}'));
            $this->assertTrue(false, 'properties with escaped characters: object with strings is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'properties with escaped characters: object with strings is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'properties with escaped characters: object with strings is invalid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testMaxContains(): void
    {
        $schema = Schema::fromJson('{"maxContains":1}');
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(true, 'maxContains without contains is ignored: one item valid against lone maxContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxContains without contains is ignored: one item valid against lone maxContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains without contains is ignored: one item valid against lone maxContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2]'));
            $this->assertTrue(true, 'maxContains without contains is ignored: two items still valid against lone maxContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxContains without contains is ignored: two items still valid against lone maxContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains without contains is ignored: two items still valid against lone maxContains. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contains":{"const":1},"maxContains":1}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'maxContains with contains: empty data. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxContains with contains: empty data. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains with contains: empty data. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(true, 'maxContains with contains: all elements match, valid maxContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxContains with contains: all elements match, valid maxContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains with contains: all elements match, valid maxContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1]'));
            $this->assertTrue(false, 'maxContains with contains: all elements match, invalid maxContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxContains with contains: all elements match, invalid maxContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains with contains: all elements match, invalid maxContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2]'));
            $this->assertTrue(true, 'maxContains with contains: some elements match, valid maxContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxContains with contains: some elements match, valid maxContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains with contains: some elements match, valid maxContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2,1]'));
            $this->assertTrue(false, 'maxContains with contains: some elements match, invalid maxContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxContains with contains: some elements match, invalid maxContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxContains with contains: some elements match, invalid maxContains. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contains":{"const":1},"minContains":1,"maxContains":3}');
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'minContains < maxContains: actual < minContains < maxContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minContains < maxContains: actual < minContains < maxContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains < maxContains: actual < minContains < maxContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1]'));
            $this->assertTrue(true, 'minContains < maxContains: minContains < actual < maxContains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minContains < maxContains: minContains < actual < maxContains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains < maxContains: minContains < actual < maxContains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,1,1,1]'));
            $this->assertTrue(false, 'minContains < maxContains: minContains < maxContains < actual. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minContains < maxContains: minContains < maxContains < actual. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minContains < maxContains: minContains < maxContains < actual. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testMaxLength(): void
    {
        $schema = Schema::fromJson('{"maxLength":2}');
        try {
            $schema->validate(json_decode('"f"'));
            $this->assertTrue(true, 'maxLength validation: shorter is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxLength validation: shorter is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxLength validation: shorter is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"fo"'));
            $this->assertTrue(true, 'maxLength validation: exact length is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxLength validation: exact length is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxLength validation: exact length is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'maxLength validation: too long is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxLength validation: too long is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxLength validation: too long is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('100'));
            $this->assertTrue(true, 'maxLength validation: ignores non-strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxLength validation: ignores non-strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxLength validation: ignores non-strings. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"\ud83d\udca9\ud83d\udca9"'));
            $this->assertTrue(true, 'maxLength validation: two supplementary Unicode code points is long enough. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxLength validation: two supplementary Unicode code points is long enough. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxLength validation: two supplementary Unicode code points is long enough. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testDependentSchemas(): void
    {
        $schema = Schema::fromJson('{"dependentSchemas":{"bar":{"properties":{"foo":{"type":"integer"},"bar":{"type":"integer"}}}}}');
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2}'));
            $this->assertTrue(true, 'single dependency: valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'single dependency: valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"quux"}'));
            $this->assertTrue(true, 'single dependency: no dependency. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'single dependency: no dependency. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: no dependency. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"quux","bar":2}'));
            $this->assertTrue(false, 'single dependency: wrong type. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'single dependency: wrong type. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: wrong type. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":2,"bar":"quux"}'));
            $this->assertTrue(false, 'single dependency: wrong type other. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'single dependency: wrong type other. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: wrong type other. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"quux","bar":"quux"}'));
            $this->assertTrue(false, 'single dependency: wrong type both. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'single dependency: wrong type both. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: wrong type both. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["bar"]'));
            $this->assertTrue(true, 'single dependency: ignores arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'single dependency: ignores arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: ignores arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foobar"'));
            $this->assertTrue(true, 'single dependency: ignores strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'single dependency: ignores strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: ignores strings. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'single dependency: ignores other non-objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'single dependency: ignores other non-objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: ignores other non-objects. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"dependentSchemas":{"foo":true,"bar":false}}');
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'boolean subschemas: object with property having schema true is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'boolean subschemas: object with property having schema true is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean subschemas: object with property having schema true is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":2}'));
            $this->assertTrue(false, 'boolean subschemas: object with property having schema false is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean subschemas: object with property having schema false is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean subschemas: object with property having schema false is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2}'));
            $this->assertTrue(false, 'boolean subschemas: object with both properties is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean subschemas: object with both properties is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean subschemas: object with both properties is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'boolean subschemas: empty object is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'boolean subschemas: empty object is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean subschemas: empty object is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"dependentSchemas":{"foo\tbar":{"minProperties":4},"foo\'bar":{"required":["foo\"bar"]}}}');
        try {
            $schema->validate(json_decode('{"foo\tbar":1,"a":2,"b":3,"c":4}'));
            $this->assertTrue(true, 'dependencies with escaped characters: quoted tab. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'dependencies with escaped characters: quoted tab. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dependencies with escaped characters: quoted tab. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo\'bar":{"foo\"bar":1}}'));
            $this->assertTrue(false, 'dependencies with escaped characters: quoted quote. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dependencies with escaped characters: quoted quote. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dependencies with escaped characters: quoted quote. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo\tbar":1,"a":2}'));
            $this->assertTrue(false, 'dependencies with escaped characters: quoted tab invalid under dependent schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dependencies with escaped characters: quoted tab invalid under dependent schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dependencies with escaped characters: quoted tab invalid under dependent schema. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo\'bar":1}'));
            $this->assertTrue(false, 'dependencies with escaped characters: quoted quote invalid under dependent schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dependencies with escaped characters: quoted quote invalid under dependent schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dependencies with escaped characters: quoted quote invalid under dependent schema. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testInfinite_loop_detection(): void
    {
        $schema = Schema::fromJson('{"$defs":{"int":{"type":"integer"}},"allOf":[{"properties":{"foo":{"$ref":"#\/$defs\/int"}}},{"additionalProperties":{"$ref":"#\/$defs\/int"}}]}');
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'evaluating the same schema location against the same data location twice is not a sign of an infinite loop: passing case. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'evaluating the same schema location against the same data location twice is not a sign of an infinite loop: passing case. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'evaluating the same schema location against the same data location twice is not a sign of an infinite loop: passing case. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"a string"}'));
            $this->assertTrue(false, 'evaluating the same schema location against the same data location twice is not a sign of an infinite loop: failing case. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'evaluating the same schema location against the same data location twice is not a sign of an infinite loop: failing case. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'evaluating the same schema location against the same data location twice is not a sign of an infinite loop: failing case. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testExclusiveMaximum(): void
    {
        $schema = Schema::fromJson('{"exclusiveMaximum":3}');
        try {
            $schema->validate(json_decode('2.2'));
            $this->assertTrue(true, 'exclusiveMaximum validation: below the exclusiveMaximum is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'exclusiveMaximum validation: below the exclusiveMaximum is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'exclusiveMaximum validation: below the exclusiveMaximum is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('3'));
            $this->assertTrue(false, 'exclusiveMaximum validation: boundary point is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'exclusiveMaximum validation: boundary point is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'exclusiveMaximum validation: boundary point is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('3.5'));
            $this->assertTrue(false, 'exclusiveMaximum validation: above the exclusiveMaximum is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'exclusiveMaximum validation: above the exclusiveMaximum is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'exclusiveMaximum validation: above the exclusiveMaximum is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"x"'));
            $this->assertTrue(true, 'exclusiveMaximum validation: ignores non-numbers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'exclusiveMaximum validation: ignores non-numbers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'exclusiveMaximum validation: ignores non-numbers. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testPrefixItems(): void
    {
        $schema = Schema::fromJson('{"prefixItems":[{"type":"integer"},{"type":"string"}]}');
        try {
            $schema->validate(json_decode('[1,"foo"]'));
            $this->assertTrue(true, 'a schema given for prefixItems: correct types. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'a schema given for prefixItems: correct types. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'a schema given for prefixItems: correct types. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo",1]'));
            $this->assertTrue(false, 'a schema given for prefixItems: wrong types. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'a schema given for prefixItems: wrong types. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'a schema given for prefixItems: wrong types. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(true, 'a schema given for prefixItems: incomplete array of items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'a schema given for prefixItems: incomplete array of items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'a schema given for prefixItems: incomplete array of items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,"foo",true]'));
            $this->assertTrue(true, 'a schema given for prefixItems: array with additional items. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'a schema given for prefixItems: array with additional items. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'a schema given for prefixItems: array with additional items. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'a schema given for prefixItems: empty array. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'a schema given for prefixItems: empty array. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'a schema given for prefixItems: empty array. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"0":"invalid","1":"valid","length":2}'));
            $this->assertTrue(true, 'a schema given for prefixItems: JavaScript pseudo-array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'a schema given for prefixItems: JavaScript pseudo-array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'a schema given for prefixItems: JavaScript pseudo-array is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"prefixItems":[true,false]}');
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(true, 'prefixItems with boolean schemas: array with one item is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'prefixItems with boolean schemas: array with one item is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'prefixItems with boolean schemas: array with one item is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,"foo"]'));
            $this->assertTrue(false, 'prefixItems with boolean schemas: array with two items is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'prefixItems with boolean schemas: array with two items is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'prefixItems with boolean schemas: array with two items is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'prefixItems with boolean schemas: empty array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'prefixItems with boolean schemas: empty array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'prefixItems with boolean schemas: empty array is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"prefixItems":[{"type":"integer"}]}');
        try {
            $schema->validate(json_decode('[1,"foo",false]'));
            $this->assertTrue(true, 'additional items are allowed by default: only the first item is validated. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'additional items are allowed by default: only the first item is validated. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'additional items are allowed by default: only the first item is validated. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testMinimum(): void
    {
        $schema = Schema::fromJson('{"minimum":1.1}');
        try {
            $schema->validate(json_decode('2.6'));
            $this->assertTrue(true, 'minimum validation: above the minimum is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minimum validation: above the minimum is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minimum validation: above the minimum is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1.1'));
            $this->assertTrue(true, 'minimum validation: boundary point is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minimum validation: boundary point is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minimum validation: boundary point is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0.6'));
            $this->assertTrue(false, 'minimum validation: below the minimum is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minimum validation: below the minimum is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minimum validation: below the minimum is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"x"'));
            $this->assertTrue(true, 'minimum validation: ignores non-numbers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minimum validation: ignores non-numbers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minimum validation: ignores non-numbers. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"minimum":-2}');
        try {
            $schema->validate(json_decode('-1'));
            $this->assertTrue(true, 'minimum validation with signed integer: negative above the minimum is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minimum validation with signed integer: negative above the minimum is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minimum validation with signed integer: negative above the minimum is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(true, 'minimum validation with signed integer: positive above the minimum is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minimum validation with signed integer: positive above the minimum is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minimum validation with signed integer: positive above the minimum is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('-2'));
            $this->assertTrue(true, 'minimum validation with signed integer: boundary point is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minimum validation with signed integer: boundary point is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minimum validation with signed integer: boundary point is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('-2'));
            $this->assertTrue(true, 'minimum validation with signed integer: boundary point with float is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minimum validation with signed integer: boundary point with float is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minimum validation with signed integer: boundary point with float is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('-2.0001'));
            $this->assertTrue(false, 'minimum validation with signed integer: float below the minimum is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minimum validation with signed integer: float below the minimum is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minimum validation with signed integer: float below the minimum is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('-3'));
            $this->assertTrue(false, 'minimum validation with signed integer: int below the minimum is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minimum validation with signed integer: int below the minimum is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minimum validation with signed integer: int below the minimum is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"x"'));
            $this->assertTrue(true, 'minimum validation with signed integer: ignores non-numbers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minimum validation with signed integer: ignores non-numbers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minimum validation with signed integer: ignores non-numbers. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testOneOf(): void
    {
        $schema = Schema::fromJson('{"oneOf":[{"type":"integer"},{"minimum":2}]}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'oneOf: first oneOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'oneOf: first oneOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf: first oneOf valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('2.5'));
            $this->assertTrue(true, 'oneOf: second oneOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'oneOf: second oneOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf: second oneOf valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('3'));
            $this->assertTrue(false, 'oneOf: both oneOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf: both oneOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf: both oneOf valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1.5'));
            $this->assertTrue(false, 'oneOf: neither oneOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf: neither oneOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf: neither oneOf valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"string","oneOf":[{"minLength":2},{"maxLength":4}]}');
        try {
            $schema->validate(json_decode('3'));
            $this->assertTrue(false, 'oneOf with base schema: mismatch base schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf with base schema: mismatch base schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with base schema: mismatch base schema. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foobar"'));
            $this->assertTrue(true, 'oneOf with base schema: one oneOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'oneOf with base schema: one oneOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with base schema: one oneOf valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'oneOf with base schema: both oneOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf with base schema: both oneOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with base schema: both oneOf valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"oneOf":[true,true,true]}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'oneOf with boolean schemas, all true: any value is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf with boolean schemas, all true: any value is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with boolean schemas, all true: any value is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"oneOf":[true,false,false]}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'oneOf with boolean schemas, one true: any value is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'oneOf with boolean schemas, one true: any value is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with boolean schemas, one true: any value is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"oneOf":[true,true,false]}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'oneOf with boolean schemas, more than one true: any value is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf with boolean schemas, more than one true: any value is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with boolean schemas, more than one true: any value is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"oneOf":[false,false,false]}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'oneOf with boolean schemas, all false: any value is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf with boolean schemas, all false: any value is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with boolean schemas, all false: any value is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"oneOf":[{"properties":{"bar":{"type":"integer"}},"required":["bar"]},{"properties":{"foo":{"type":"string"}},"required":["foo"]}]}');
        try {
            $schema->validate(json_decode('{"bar":2}'));
            $this->assertTrue(true, 'oneOf complex types: first oneOf valid (complex). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'oneOf complex types: first oneOf valid (complex). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf complex types: first oneOf valid (complex). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"baz"}'));
            $this->assertTrue(true, 'oneOf complex types: second oneOf valid (complex). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'oneOf complex types: second oneOf valid (complex). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf complex types: second oneOf valid (complex). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"baz","bar":2}'));
            $this->assertTrue(false, 'oneOf complex types: both oneOf valid (complex). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf complex types: both oneOf valid (complex). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf complex types: both oneOf valid (complex). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":2,"bar":"quux"}'));
            $this->assertTrue(false, 'oneOf complex types: neither oneOf valid (complex). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf complex types: neither oneOf valid (complex). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf complex types: neither oneOf valid (complex). Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"oneOf":[{"type":"number"},{}]}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'oneOf with empty schema: one valid - valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'oneOf with empty schema: one valid - valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with empty schema: one valid - valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('123'));
            $this->assertTrue(false, 'oneOf with empty schema: both valid - invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf with empty schema: both valid - invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with empty schema: both valid - invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","oneOf":[{"required":["foo","bar"]},{"required":["foo","baz"]}]}');
        try {
            $schema->validate(json_decode('{"bar":2}'));
            $this->assertTrue(false, 'oneOf with required: both invalid - invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf with required: both invalid - invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with required: both invalid - invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2}'));
            $this->assertTrue(true, 'oneOf with required: first valid - valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'oneOf with required: first valid - valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with required: first valid - valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"baz":3}'));
            $this->assertTrue(true, 'oneOf with required: second valid - valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'oneOf with required: second valid - valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with required: second valid - valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2,"baz":3}'));
            $this->assertTrue(false, 'oneOf with required: both valid - invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf with required: both valid - invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with required: both valid - invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"oneOf":[{"properties":{"bar":true,"baz":true},"required":["bar"]},{"properties":{"foo":true},"required":["foo"]}]}');
        try {
            $schema->validate(json_decode('{"bar":8}'));
            $this->assertTrue(true, 'oneOf with missing optional property: first oneOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'oneOf with missing optional property: first oneOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with missing optional property: first oneOf valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(true, 'oneOf with missing optional property: second oneOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'oneOf with missing optional property: second oneOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with missing optional property: second oneOf valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":8}'));
            $this->assertTrue(false, 'oneOf with missing optional property: both oneOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf with missing optional property: both oneOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with missing optional property: both oneOf valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"baz":"quux"}'));
            $this->assertTrue(false, 'oneOf with missing optional property: neither oneOf valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'oneOf with missing optional property: neither oneOf valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'oneOf with missing optional property: neither oneOf valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"oneOf":[{"oneOf":[{"type":"null"}]}]}');
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'nested oneOf, to check validation semantics: null is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'nested oneOf, to check validation semantics: null is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested oneOf, to check validation semantics: null is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('123'));
            $this->assertTrue(false, 'nested oneOf, to check validation semantics: anything non-null is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'nested oneOf, to check validation semantics: anything non-null is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested oneOf, to check validation semantics: anything non-null is invalid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testIf_then_else(): void
    {
        $schema = Schema::fromJson('{"if":{"const":0}}');
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(true, 'ignore if without then or else: valid when valid against lone if. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ignore if without then or else: valid when valid against lone if. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ignore if without then or else: valid when valid against lone if. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"hello"'));
            $this->assertTrue(true, 'ignore if without then or else: valid when invalid against lone if. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ignore if without then or else: valid when invalid against lone if. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ignore if without then or else: valid when invalid against lone if. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"then":{"const":0}}');
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(true, 'ignore then without if: valid when valid against lone then. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ignore then without if: valid when valid against lone then. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ignore then without if: valid when valid against lone then. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"hello"'));
            $this->assertTrue(true, 'ignore then without if: valid when invalid against lone then. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ignore then without if: valid when invalid against lone then. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ignore then without if: valid when invalid against lone then. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"else":{"const":0}}');
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(true, 'ignore else without if: valid when valid against lone else. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ignore else without if: valid when valid against lone else. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ignore else without if: valid when valid against lone else. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"hello"'));
            $this->assertTrue(true, 'ignore else without if: valid when invalid against lone else. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ignore else without if: valid when invalid against lone else. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ignore else without if: valid when invalid against lone else. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"if":{"exclusiveMaximum":0},"then":{"minimum":-10}}');
        try {
            $schema->validate(json_decode('-1'));
            $this->assertTrue(true, 'if and then without else: valid through then. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'if and then without else: valid through then. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if and then without else: valid through then. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('-100'));
            $this->assertTrue(false, 'if and then without else: invalid through then. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'if and then without else: invalid through then. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if and then without else: invalid through then. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('3'));
            $this->assertTrue(true, 'if and then without else: valid when if test fails. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'if and then without else: valid when if test fails. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if and then without else: valid when if test fails. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"if":{"exclusiveMaximum":0},"else":{"multipleOf":2}}');
        try {
            $schema->validate(json_decode('-1'));
            $this->assertTrue(true, 'if and else without then: valid when if test passes. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'if and else without then: valid when if test passes. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if and else without then: valid when if test passes. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('4'));
            $this->assertTrue(true, 'if and else without then: valid through else. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'if and else without then: valid through else. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if and else without then: valid through else. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('3'));
            $this->assertTrue(false, 'if and else without then: invalid through else. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'if and else without then: invalid through else. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if and else without then: invalid through else. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"if":{"exclusiveMaximum":0},"then":{"minimum":-10},"else":{"multipleOf":2}}');
        try {
            $schema->validate(json_decode('-1'));
            $this->assertTrue(true, 'validate against correct branch, then vs else: valid through then. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validate against correct branch, then vs else: valid through then. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validate against correct branch, then vs else: valid through then. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('-100'));
            $this->assertTrue(false, 'validate against correct branch, then vs else: invalid through then. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'validate against correct branch, then vs else: invalid through then. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validate against correct branch, then vs else: invalid through then. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('4'));
            $this->assertTrue(true, 'validate against correct branch, then vs else: valid through else. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'validate against correct branch, then vs else: valid through else. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validate against correct branch, then vs else: valid through else. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('3'));
            $this->assertTrue(false, 'validate against correct branch, then vs else: invalid through else. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'validate against correct branch, then vs else: invalid through else. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'validate against correct branch, then vs else: invalid through else. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[{"if":{"exclusiveMaximum":0}},{"then":{"minimum":-10}},{"else":{"multipleOf":2}}]}');
        try {
            $schema->validate(json_decode('-100'));
            $this->assertTrue(true, 'non-interference across combined schemas: valid, but would have been invalid through then. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'non-interference across combined schemas: valid, but would have been invalid through then. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'non-interference across combined schemas: valid, but would have been invalid through then. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('3'));
            $this->assertTrue(true, 'non-interference across combined schemas: valid, but would have been invalid through else. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'non-interference across combined schemas: valid, but would have been invalid through else. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'non-interference across combined schemas: valid, but would have been invalid through else. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"if":true,"then":{"const":"then"},"else":{"const":"else"}}');
        try {
            $schema->validate(json_decode('"then"'));
            $this->assertTrue(true, 'if with boolean schema true: boolean schema true in if always chooses the then path (valid). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'if with boolean schema true: boolean schema true in if always chooses the then path (valid). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if with boolean schema true: boolean schema true in if always chooses the then path (valid). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"else"'));
            $this->assertTrue(false, 'if with boolean schema true: boolean schema true in if always chooses the then path (invalid). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'if with boolean schema true: boolean schema true in if always chooses the then path (invalid). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if with boolean schema true: boolean schema true in if always chooses the then path (invalid). Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"if":false,"then":{"const":"then"},"else":{"const":"else"}}');
        try {
            $schema->validate(json_decode('"then"'));
            $this->assertTrue(false, 'if with boolean schema false: boolean schema false in if always chooses the else path (invalid). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'if with boolean schema false: boolean schema false in if always chooses the else path (invalid). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if with boolean schema false: boolean schema false in if always chooses the else path (invalid). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"else"'));
            $this->assertTrue(true, 'if with boolean schema false: boolean schema false in if always chooses the else path (valid). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'if with boolean schema false: boolean schema false in if always chooses the else path (valid). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if with boolean schema false: boolean schema false in if always chooses the else path (valid). Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"then":{"const":"yes"},"else":{"const":"other"},"if":{"maxLength":4}}');
        try {
            $schema->validate(json_decode('"yes"'));
            $this->assertTrue(true, 'if appears at the end when serialized (keyword processing sequence): yes redirects to then and passes. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'if appears at the end when serialized (keyword processing sequence): yes redirects to then and passes. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if appears at the end when serialized (keyword processing sequence): yes redirects to then and passes. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"other"'));
            $this->assertTrue(true, 'if appears at the end when serialized (keyword processing sequence): other redirects to else and passes. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'if appears at the end when serialized (keyword processing sequence): other redirects to else and passes. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if appears at the end when serialized (keyword processing sequence): other redirects to else and passes. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"no"'));
            $this->assertTrue(false, 'if appears at the end when serialized (keyword processing sequence): no redirects to then and fails. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'if appears at the end when serialized (keyword processing sequence): no redirects to then and fails. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if appears at the end when serialized (keyword processing sequence): no redirects to then and fails. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"invalid"'));
            $this->assertTrue(false, 'if appears at the end when serialized (keyword processing sequence): invalid redirects to else and fails. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'if appears at the end when serialized (keyword processing sequence): invalid redirects to else and fails. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'if appears at the end when serialized (keyword processing sequence): invalid redirects to else and fails. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testPattern(): void
    {
        $schema = Schema::fromJson('{"pattern":"^a*$"}');
        try {
            $schema->validate(json_decode('"aaa"'));
            $this->assertTrue(true, 'pattern validation: a matching pattern is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'pattern validation: a matching pattern is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'pattern validation: a matching pattern is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"abc"'));
            $this->assertTrue(false, 'pattern validation: a non-matching pattern is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'pattern validation: a non-matching pattern is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'pattern validation: a non-matching pattern is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(true, 'pattern validation: ignores booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'pattern validation: ignores booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'pattern validation: ignores booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('123'));
            $this->assertTrue(true, 'pattern validation: ignores integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'pattern validation: ignores integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'pattern validation: ignores integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'pattern validation: ignores floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'pattern validation: ignores floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'pattern validation: ignores floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'pattern validation: ignores objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'pattern validation: ignores objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'pattern validation: ignores objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'pattern validation: ignores arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'pattern validation: ignores arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'pattern validation: ignores arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'pattern validation: ignores null. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'pattern validation: ignores null. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'pattern validation: ignores null. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"pattern":"a+"}');
        try {
            $schema->validate(json_decode('"xxaayy"'));
            $this->assertTrue(true, 'pattern is not anchored: matches a substring. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'pattern is not anchored: matches a substring. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'pattern is not anchored: matches a substring. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testUnevaluatedProperties(): void
    {
        $schema = Schema::fromJson('{"type":"object","unevaluatedProperties":true}');
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'unevaluatedProperties true: with no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties true: with no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties true: with no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(true, 'unevaluatedProperties true: with unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties true: with unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties true: with unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","unevaluatedProperties":{"type":"string","minLength":3}}');
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'unevaluatedProperties schema: with no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties schema: with no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties schema: with no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(true, 'unevaluatedProperties schema: with valid unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties schema: with valid unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties schema: with valid unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"fo"}'));
            $this->assertTrue(false, 'unevaluatedProperties schema: with invalid unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties schema: with invalid unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties schema: with invalid unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'unevaluatedProperties false: with no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties false: with no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties false: with no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(false, 'unevaluatedProperties false: with unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties false: with unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties false: with unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"string"}},"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(true, 'unevaluatedProperties with adjacent properties: with no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with adjacent properties: with no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with adjacent properties: with no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(false, 'unevaluatedProperties with adjacent properties: with unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with adjacent properties: with unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with adjacent properties: with unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","patternProperties":{"^foo":{"type":"string"}},"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(true, 'unevaluatedProperties with adjacent patternProperties: with no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with adjacent patternProperties: with no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with adjacent patternProperties: with no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(false, 'unevaluatedProperties with adjacent patternProperties: with unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with adjacent patternProperties: with unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with adjacent patternProperties: with unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"string"}},"additionalProperties":true,"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(true, 'unevaluatedProperties with adjacent additionalProperties: with no additional properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with adjacent additionalProperties: with no additional properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with adjacent additionalProperties: with no additional properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(true, 'unevaluatedProperties with adjacent additionalProperties: with additional properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with adjacent additionalProperties: with additional properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with adjacent additionalProperties: with additional properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"string"}},"allOf":[{"properties":{"bar":{"type":"string"}}}],"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(true, 'unevaluatedProperties with nested properties: with no additional properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with nested properties: with no additional properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with nested properties: with no additional properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar","baz":"baz"}'));
            $this->assertTrue(false, 'unevaluatedProperties with nested properties: with additional properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with nested properties: with additional properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with nested properties: with additional properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"string"}},"allOf":[{"patternProperties":{"^bar":{"type":"string"}}}],"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(true, 'unevaluatedProperties with nested patternProperties: with no additional properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with nested patternProperties: with no additional properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with nested patternProperties: with no additional properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar","baz":"baz"}'));
            $this->assertTrue(false, 'unevaluatedProperties with nested patternProperties: with additional properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with nested patternProperties: with additional properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with nested patternProperties: with additional properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"string"}},"allOf":[{"additionalProperties":true}],"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(true, 'unevaluatedProperties with nested additionalProperties: with no additional properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with nested additionalProperties: with no additional properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with nested additionalProperties: with no additional properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(true, 'unevaluatedProperties with nested additionalProperties: with additional properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with nested additionalProperties: with additional properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with nested additionalProperties: with additional properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"string"}},"allOf":[{"unevaluatedProperties":true}],"unevaluatedProperties":{"type":"string","maxLength":2}}');
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(true, 'unevaluatedProperties with nested unevaluatedProperties: with no nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with nested unevaluatedProperties: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with nested unevaluatedProperties: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(true, 'unevaluatedProperties with nested unevaluatedProperties: with nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with nested unevaluatedProperties: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with nested unevaluatedProperties: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"string"}},"anyOf":[{"properties":{"bar":{"const":"bar"}},"required":["bar"]},{"properties":{"baz":{"const":"baz"}},"required":["baz"]},{"properties":{"quux":{"const":"quux"}},"required":["quux"]}],"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(true, 'unevaluatedProperties with anyOf: when one matches and has no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with anyOf: when one matches and has no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with anyOf: when one matches and has no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar","baz":"not-baz"}'));
            $this->assertTrue(false, 'unevaluatedProperties with anyOf: when one matches and has unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with anyOf: when one matches and has unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with anyOf: when one matches and has unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar","baz":"baz"}'));
            $this->assertTrue(true, 'unevaluatedProperties with anyOf: when two match and has no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with anyOf: when two match and has no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with anyOf: when two match and has no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar","baz":"baz","quux":"not-quux"}'));
            $this->assertTrue(false, 'unevaluatedProperties with anyOf: when two match and has unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with anyOf: when two match and has unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with anyOf: when two match and has unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"string"}},"oneOf":[{"properties":{"bar":{"const":"bar"}},"required":["bar"]},{"properties":{"baz":{"const":"baz"}},"required":["baz"]}],"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(true, 'unevaluatedProperties with oneOf: with no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with oneOf: with no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with oneOf: with no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar","quux":"quux"}'));
            $this->assertTrue(false, 'unevaluatedProperties with oneOf: with unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with oneOf: with unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with oneOf: with unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"string"}},"not":{"not":{"properties":{"bar":{"const":"bar"}},"required":["bar"]}},"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(false, 'unevaluatedProperties with not: with unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with not: with unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with not: with unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","if":{"properties":{"foo":{"const":"then"}},"required":["foo"]},"then":{"properties":{"bar":{"type":"string"}},"required":["bar"]},"else":{"properties":{"baz":{"type":"string"}},"required":["baz"]},"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"then","bar":"bar"}'));
            $this->assertTrue(true, 'unevaluatedProperties with if/then/else: when if is true and has no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else: when if is true and has no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else: when if is true and has no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"then","bar":"bar","baz":"baz"}'));
            $this->assertTrue(false, 'unevaluatedProperties with if/then/else: when if is true and has unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with if/then/else: when if is true and has unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else: when if is true and has unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"baz":"baz"}'));
            $this->assertTrue(true, 'unevaluatedProperties with if/then/else: when if is false and has no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else: when if is false and has no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else: when if is false and has no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"else","baz":"baz"}'));
            $this->assertTrue(false, 'unevaluatedProperties with if/then/else: when if is false and has unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with if/then/else: when if is false and has unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else: when if is false and has unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","if":{"properties":{"foo":{"const":"then"}},"required":["foo"]},"else":{"properties":{"baz":{"type":"string"}},"required":["baz"]},"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"then","bar":"bar"}'));
            $this->assertTrue(false, 'unevaluatedProperties with if/then/else, then not defined: when if is true and has no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with if/then/else, then not defined: when if is true and has no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else, then not defined: when if is true and has no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"then","bar":"bar","baz":"baz"}'));
            $this->assertTrue(false, 'unevaluatedProperties with if/then/else, then not defined: when if is true and has unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with if/then/else, then not defined: when if is true and has unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else, then not defined: when if is true and has unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"baz":"baz"}'));
            $this->assertTrue(true, 'unevaluatedProperties with if/then/else, then not defined: when if is false and has no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else, then not defined: when if is false and has no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else, then not defined: when if is false and has no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"else","baz":"baz"}'));
            $this->assertTrue(false, 'unevaluatedProperties with if/then/else, then not defined: when if is false and has unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with if/then/else, then not defined: when if is false and has unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else, then not defined: when if is false and has unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","if":{"properties":{"foo":{"const":"then"}},"required":["foo"]},"then":{"properties":{"bar":{"type":"string"}},"required":["bar"]},"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"then","bar":"bar"}'));
            $this->assertTrue(true, 'unevaluatedProperties with if/then/else, else not defined: when if is true and has no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else, else not defined: when if is true and has no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else, else not defined: when if is true and has no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"then","bar":"bar","baz":"baz"}'));
            $this->assertTrue(false, 'unevaluatedProperties with if/then/else, else not defined: when if is true and has unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with if/then/else, else not defined: when if is true and has unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else, else not defined: when if is true and has unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"baz":"baz"}'));
            $this->assertTrue(false, 'unevaluatedProperties with if/then/else, else not defined: when if is false and has no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with if/then/else, else not defined: when if is false and has no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else, else not defined: when if is false and has no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"else","baz":"baz"}'));
            $this->assertTrue(false, 'unevaluatedProperties with if/then/else, else not defined: when if is false and has unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with if/then/else, else not defined: when if is false and has unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with if/then/else, else not defined: when if is false and has unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"string"}},"dependentSchemas":{"foo":{"properties":{"bar":{"const":"bar"}},"required":["bar"]}},"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(true, 'unevaluatedProperties with dependentSchemas: with no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with dependentSchemas: with no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with dependentSchemas: with no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":"bar"}'));
            $this->assertTrue(false, 'unevaluatedProperties with dependentSchemas: with unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with dependentSchemas: with unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with dependentSchemas: with unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"string"}},"allOf":[true],"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(true, 'unevaluatedProperties with boolean schemas: with no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with boolean schemas: with no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with boolean schemas: with no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":"bar"}'));
            $this->assertTrue(false, 'unevaluatedProperties with boolean schemas: with unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with boolean schemas: with unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with boolean schemas: with unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","$ref":"#\/$defs\/bar","properties":{"foo":{"type":"string"}},"unevaluatedProperties":false,"$defs":{"bar":{"properties":{"bar":{"type":"string"}}}}}');
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(true, 'unevaluatedProperties with $ref: with no unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties with $ref: with no unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with $ref: with no unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar","baz":"baz"}'));
            $this->assertTrue(false, 'unevaluatedProperties with $ref: with unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties with $ref: with unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties with $ref: with unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[{"properties":{"foo":true}},{"unevaluatedProperties":false}]}');
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(false, 'unevaluatedProperties can\'t see inside cousins: always fails. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties can\'t see inside cousins: always fails. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties can\'t see inside cousins: always fails. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"string"}},"allOf":[{"unevaluatedProperties":true}],"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(true, 'nested unevaluatedProperties, outer false, inner true, properties outside: with no nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'nested unevaluatedProperties, outer false, inner true, properties outside: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested unevaluatedProperties, outer false, inner true, properties outside: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(true, 'nested unevaluatedProperties, outer false, inner true, properties outside: with nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'nested unevaluatedProperties, outer false, inner true, properties outside: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested unevaluatedProperties, outer false, inner true, properties outside: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","allOf":[{"properties":{"foo":{"type":"string"}},"unevaluatedProperties":true}],"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(true, 'nested unevaluatedProperties, outer false, inner true, properties inside: with no nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'nested unevaluatedProperties, outer false, inner true, properties inside: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested unevaluatedProperties, outer false, inner true, properties inside: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(true, 'nested unevaluatedProperties, outer false, inner true, properties inside: with nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'nested unevaluatedProperties, outer false, inner true, properties inside: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested unevaluatedProperties, outer false, inner true, properties inside: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"string"}},"allOf":[{"unevaluatedProperties":false}],"unevaluatedProperties":true}');
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(false, 'nested unevaluatedProperties, outer true, inner false, properties outside: with no nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'nested unevaluatedProperties, outer true, inner false, properties outside: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested unevaluatedProperties, outer true, inner false, properties outside: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(false, 'nested unevaluatedProperties, outer true, inner false, properties outside: with nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'nested unevaluatedProperties, outer true, inner false, properties outside: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested unevaluatedProperties, outer true, inner false, properties outside: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","allOf":[{"properties":{"foo":{"type":"string"}},"unevaluatedProperties":false}],"unevaluatedProperties":true}');
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(true, 'nested unevaluatedProperties, outer true, inner false, properties inside: with no nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'nested unevaluatedProperties, outer true, inner false, properties inside: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested unevaluatedProperties, outer true, inner false, properties inside: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(false, 'nested unevaluatedProperties, outer true, inner false, properties inside: with nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'nested unevaluatedProperties, outer true, inner false, properties inside: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested unevaluatedProperties, outer true, inner false, properties inside: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","allOf":[{"properties":{"foo":{"type":"string"}},"unevaluatedProperties":true},{"unevaluatedProperties":false}]}');
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(false, 'cousin unevaluatedProperties, true and false, true with properties: with no nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'cousin unevaluatedProperties, true and false, true with properties: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'cousin unevaluatedProperties, true and false, true with properties: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(false, 'cousin unevaluatedProperties, true and false, true with properties: with nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'cousin unevaluatedProperties, true and false, true with properties: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'cousin unevaluatedProperties, true and false, true with properties: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","allOf":[{"unevaluatedProperties":true},{"properties":{"foo":{"type":"string"}},"unevaluatedProperties":false}]}');
        try {
            $schema->validate(json_decode('{"foo":"foo"}'));
            $this->assertTrue(true, 'cousin unevaluatedProperties, true and false, false with properties: with no nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'cousin unevaluatedProperties, true and false, false with properties: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'cousin unevaluatedProperties, true and false, false with properties: with no nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"foo","bar":"bar"}'));
            $this->assertTrue(false, 'cousin unevaluatedProperties, true and false, false with properties: with nested unevaluated properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'cousin unevaluatedProperties, true and false, false with properties: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'cousin unevaluatedProperties, true and false, false with properties: with nested unevaluated properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"foo":{"type":"object","properties":{"bar":{"type":"string"}},"unevaluatedProperties":false}},"anyOf":[{"properties":{"foo":{"properties":{"faz":{"type":"string"}}}}}]}');
        try {
            $schema->validate(json_decode('{"foo":{"bar":"test"}}'));
            $this->assertTrue(true, 'property is evaluated in an uncle schema to unevaluatedProperties: no extra properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'property is evaluated in an uncle schema to unevaluatedProperties: no extra properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'property is evaluated in an uncle schema to unevaluatedProperties: no extra properties. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":{"bar":"test","faz":"test"}}'));
            $this->assertTrue(false, 'property is evaluated in an uncle schema to unevaluatedProperties: uncle keyword evaluation is not significant. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'property is evaluated in an uncle schema to unevaluatedProperties: uncle keyword evaluation is not significant. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'property is evaluated in an uncle schema to unevaluatedProperties: uncle keyword evaluation is not significant. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","allOf":[{"properties":{"foo":true},"unevaluatedProperties":false}],"anyOf":[{"properties":{"bar":true}}]}');
        try {
            $schema->validate(json_decode('{"foo":1,"bar":1}'));
            $this->assertTrue(false, 'in-place applicator siblings, allOf has unevaluated: base case: both properties present. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'in-place applicator siblings, allOf has unevaluated: base case: both properties present. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'in-place applicator siblings, allOf has unevaluated: base case: both properties present. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'in-place applicator siblings, allOf has unevaluated: in place applicator siblings, bar is missing. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'in-place applicator siblings, allOf has unevaluated: in place applicator siblings, bar is missing. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'in-place applicator siblings, allOf has unevaluated: in place applicator siblings, bar is missing. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":1}'));
            $this->assertTrue(false, 'in-place applicator siblings, allOf has unevaluated: in place applicator siblings, foo is missing. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'in-place applicator siblings, allOf has unevaluated: in place applicator siblings, foo is missing. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'in-place applicator siblings, allOf has unevaluated: in place applicator siblings, foo is missing. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","allOf":[{"properties":{"foo":true}}],"anyOf":[{"properties":{"bar":true},"unevaluatedProperties":false}]}');
        try {
            $schema->validate(json_decode('{"foo":1,"bar":1}'));
            $this->assertTrue(false, 'in-place applicator siblings, anyOf has unevaluated: base case: both properties present. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'in-place applicator siblings, anyOf has unevaluated: base case: both properties present. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'in-place applicator siblings, anyOf has unevaluated: base case: both properties present. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(false, 'in-place applicator siblings, anyOf has unevaluated: in place applicator siblings, bar is missing. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'in-place applicator siblings, anyOf has unevaluated: in place applicator siblings, bar is missing. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'in-place applicator siblings, anyOf has unevaluated: in place applicator siblings, bar is missing. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":1}'));
            $this->assertTrue(true, 'in-place applicator siblings, anyOf has unevaluated: in place applicator siblings, foo is missing. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'in-place applicator siblings, anyOf has unevaluated: in place applicator siblings, foo is missing. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'in-place applicator siblings, anyOf has unevaluated: in place applicator siblings, foo is missing. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"x":{"$ref":"#"}},"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'unevaluatedProperties + single cyclic ref: Empty is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Empty is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Empty is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"x":{}}'));
            $this->assertTrue(true, 'unevaluatedProperties + single cyclic ref: Single is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Single is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Single is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"x":{},"y":{}}'));
            $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Unevaluated on 1st level is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties + single cyclic ref: Unevaluated on 1st level is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Unevaluated on 1st level is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"x":{"x":{}}}'));
            $this->assertTrue(true, 'unevaluatedProperties + single cyclic ref: Nested is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Nested is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Nested is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"x":{"x":{},"y":{}}}'));
            $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Unevaluated on 2nd level is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties + single cyclic ref: Unevaluated on 2nd level is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Unevaluated on 2nd level is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"x":{"x":{"x":{}}}}'));
            $this->assertTrue(true, 'unevaluatedProperties + single cyclic ref: Deep nested is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Deep nested is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Deep nested is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"x":{"x":{"x":{},"y":{}}}}'));
            $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Unevaluated on 3rd level is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties + single cyclic ref: Unevaluated on 3rd level is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + single cyclic ref: Unevaluated on 3rd level is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$defs":{"one":{"properties":{"a":true}},"two":{"required":["x"],"properties":{"x":true}}},"allOf":[{"$ref":"#\/$defs\/one"},{"properties":{"b":true}},{"oneOf":[{"$ref":"#\/$defs\/two"},{"required":["y"],"properties":{"y":true}}]}],"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: Empty is invalid (no x or y). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties + ref inside allOf / oneOf: Empty is invalid (no x or y). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: Empty is invalid (no x or y). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":1,"b":1}'));
            $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: a and b are invalid (no x or y). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties + ref inside allOf / oneOf: a and b are invalid (no x or y). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: a and b are invalid (no x or y). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"x":1,"y":1}'));
            $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: x and y are invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties + ref inside allOf / oneOf: x and y are invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: x and y are invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":1,"x":1}'));
            $this->assertTrue(true, 'unevaluatedProperties + ref inside allOf / oneOf: a and x are valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: a and x are valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: a and x are valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":1,"y":1}'));
            $this->assertTrue(true, 'unevaluatedProperties + ref inside allOf / oneOf: a and y are valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: a and y are valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: a and y are valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":1,"b":1,"x":1}'));
            $this->assertTrue(true, 'unevaluatedProperties + ref inside allOf / oneOf: a and b and x are valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: a and b and x are valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: a and b and x are valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":1,"b":1,"y":1}'));
            $this->assertTrue(true, 'unevaluatedProperties + ref inside allOf / oneOf: a and b and y are valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: a and b and y are valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: a and b and y are valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":1,"b":1,"x":1,"y":1}'));
            $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: a and b and x and y are invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'unevaluatedProperties + ref inside allOf / oneOf: a and b and x and y are invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'unevaluatedProperties + ref inside allOf / oneOf: a and b and x and y are invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$defs":{"one":{"oneOf":[{"$ref":"#\/$defs\/two"},{"required":["b"],"properties":{"b":true}},{"required":["xx"],"patternProperties":{"x":true}},{"required":["all"],"unevaluatedProperties":true}]},"two":{"oneOf":[{"required":["c"],"properties":{"c":true}},{"required":["d"],"properties":{"d":true}}]}},"oneOf":[{"$ref":"#\/$defs\/one"},{"required":["a"],"properties":{"a":true}}],"unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(false, 'dynamic evalation inside nested refs: Empty is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dynamic evalation inside nested refs: Empty is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: Empty is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":1}'));
            $this->assertTrue(true, 'dynamic evalation inside nested refs: a is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: a is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: a is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"b":1}'));
            $this->assertTrue(true, 'dynamic evalation inside nested refs: b is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: b is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: b is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"c":1}'));
            $this->assertTrue(true, 'dynamic evalation inside nested refs: c is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: c is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: c is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"d":1}'));
            $this->assertTrue(true, 'dynamic evalation inside nested refs: d is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: d is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: d is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":1,"b":1}'));
            $this->assertTrue(false, 'dynamic evalation inside nested refs: a + b is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dynamic evalation inside nested refs: a + b is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: a + b is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":1,"c":1}'));
            $this->assertTrue(false, 'dynamic evalation inside nested refs: a + c is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dynamic evalation inside nested refs: a + c is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: a + c is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":1,"d":1}'));
            $this->assertTrue(false, 'dynamic evalation inside nested refs: a + d is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dynamic evalation inside nested refs: a + d is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: a + d is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"b":1,"c":1}'));
            $this->assertTrue(false, 'dynamic evalation inside nested refs: b + c is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dynamic evalation inside nested refs: b + c is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: b + c is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"b":1,"d":1}'));
            $this->assertTrue(false, 'dynamic evalation inside nested refs: b + d is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dynamic evalation inside nested refs: b + d is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: b + d is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"c":1,"d":1}'));
            $this->assertTrue(false, 'dynamic evalation inside nested refs: c + d is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dynamic evalation inside nested refs: c + d is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: c + d is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"xx":1}'));
            $this->assertTrue(true, 'dynamic evalation inside nested refs: xx is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: xx is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: xx is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"xx":1,"foox":1}'));
            $this->assertTrue(true, 'dynamic evalation inside nested refs: xx + foox is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: xx + foox is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: xx + foox is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"xx":1,"foo":1}'));
            $this->assertTrue(false, 'dynamic evalation inside nested refs: xx + foo is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dynamic evalation inside nested refs: xx + foo is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: xx + foo is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"xx":1,"a":1}'));
            $this->assertTrue(false, 'dynamic evalation inside nested refs: xx + a is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dynamic evalation inside nested refs: xx + a is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: xx + a is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"xx":1,"b":1}'));
            $this->assertTrue(false, 'dynamic evalation inside nested refs: xx + b is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dynamic evalation inside nested refs: xx + b is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: xx + b is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"xx":1,"c":1}'));
            $this->assertTrue(false, 'dynamic evalation inside nested refs: xx + c is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dynamic evalation inside nested refs: xx + c is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: xx + c is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"xx":1,"d":1}'));
            $this->assertTrue(false, 'dynamic evalation inside nested refs: xx + d is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dynamic evalation inside nested refs: xx + d is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: xx + d is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"all":1}'));
            $this->assertTrue(true, 'dynamic evalation inside nested refs: all is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: all is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: all is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"all":1,"foo":1}'));
            $this->assertTrue(true, 'dynamic evalation inside nested refs: all + foo is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: all + foo is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: all + foo is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"all":1,"a":1}'));
            $this->assertTrue(false, 'dynamic evalation inside nested refs: all + a is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dynamic evalation inside nested refs: all + a is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dynamic evalation inside nested refs: all + a is invalid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testMaxProperties(): void
    {
        $schema = Schema::fromJson('{"maxProperties":2}');
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'maxProperties validation: shorter is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxProperties validation: shorter is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxProperties validation: shorter is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2}'));
            $this->assertTrue(true, 'maxProperties validation: exact length is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxProperties validation: exact length is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxProperties validation: exact length is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2,"baz":3}'));
            $this->assertTrue(false, 'maxProperties validation: too long is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxProperties validation: too long is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxProperties validation: too long is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2,3]'));
            $this->assertTrue(true, 'maxProperties validation: ignores arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxProperties validation: ignores arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxProperties validation: ignores arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foobar"'));
            $this->assertTrue(true, 'maxProperties validation: ignores strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxProperties validation: ignores strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxProperties validation: ignores strings. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'maxProperties validation: ignores other non-objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxProperties validation: ignores other non-objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxProperties validation: ignores other non-objects. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"maxProperties":0}');
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'maxProperties = 0 means the object is empty: no properties is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxProperties = 0 means the object is empty: no properties is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxProperties = 0 means the object is empty: no properties is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(false, 'maxProperties = 0 means the object is empty: one property is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxProperties = 0 means the object is empty: one property is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxProperties = 0 means the object is empty: one property is invalid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testDependentRequired(): void
    {
        $schema = Schema::fromJson('{"dependentRequired":{"bar":["foo"]}}');
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'single dependency: neither. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'single dependency: neither. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: neither. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'single dependency: nondependant. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'single dependency: nondependant. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: nondependant. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2}'));
            $this->assertTrue(true, 'single dependency: with dependency. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'single dependency: with dependency. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: with dependency. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":2}'));
            $this->assertTrue(false, 'single dependency: missing dependency. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'single dependency: missing dependency. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: missing dependency. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["bar"]'));
            $this->assertTrue(true, 'single dependency: ignores arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'single dependency: ignores arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: ignores arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foobar"'));
            $this->assertTrue(true, 'single dependency: ignores strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'single dependency: ignores strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: ignores strings. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'single dependency: ignores other non-objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'single dependency: ignores other non-objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'single dependency: ignores other non-objects. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"dependentRequired":{"bar":[]}}');
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'empty dependents: empty object. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'empty dependents: empty object. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'empty dependents: empty object. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":2}'));
            $this->assertTrue(true, 'empty dependents: object with one property. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'empty dependents: object with one property. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'empty dependents: object with one property. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'empty dependents: non-object is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'empty dependents: non-object is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'empty dependents: non-object is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"dependentRequired":{"quux":["foo","bar"]}}');
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'multiple dependents required: neither. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'multiple dependents required: neither. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple dependents required: neither. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2}'));
            $this->assertTrue(true, 'multiple dependents required: nondependants. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'multiple dependents required: nondependants. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple dependents required: nondependants. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2,"quux":3}'));
            $this->assertTrue(true, 'multiple dependents required: with dependencies. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'multiple dependents required: with dependencies. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple dependents required: with dependencies. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"quux":2}'));
            $this->assertTrue(false, 'multiple dependents required: missing dependency. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'multiple dependents required: missing dependency. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple dependents required: missing dependency. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":1,"quux":2}'));
            $this->assertTrue(false, 'multiple dependents required: missing other dependency. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'multiple dependents required: missing other dependency. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple dependents required: missing other dependency. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"quux":1}'));
            $this->assertTrue(false, 'multiple dependents required: missing both dependencies. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'multiple dependents required: missing both dependencies. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple dependents required: missing both dependencies. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"dependentRequired":{"foo\nbar":["foo\rbar"],"foo\"bar":["foo\'bar"]}}');
        try {
            $schema->validate(json_decode('{"foo\nbar":1,"foo\rbar":2}'));
            $this->assertTrue(true, 'dependencies with escaped characters: CRLF. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'dependencies with escaped characters: CRLF. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dependencies with escaped characters: CRLF. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo\'bar":1,"foo\"bar":2}'));
            $this->assertTrue(true, 'dependencies with escaped characters: quoted quotes. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'dependencies with escaped characters: quoted quotes. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dependencies with escaped characters: quoted quotes. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo\nbar":1,"foo":2}'));
            $this->assertTrue(false, 'dependencies with escaped characters: CRLF missing dependent. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dependencies with escaped characters: CRLF missing dependent. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dependencies with escaped characters: CRLF missing dependent. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo\"bar":2}'));
            $this->assertTrue(false, 'dependencies with escaped characters: quoted quotes missing dependent. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'dependencies with escaped characters: quoted quotes missing dependent. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'dependencies with escaped characters: quoted quotes missing dependent. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testRequired(): void
    {
        $schema = Schema::fromJson('{"properties":{"foo":{},"bar":{}},"required":["foo"]}');
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'required validation: present required property is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'required validation: present required property is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'required validation: present required property is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":1}'));
            $this->assertTrue(false, 'required validation: non-present required property is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'required validation: non-present required property is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'required validation: non-present required property is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'required validation: ignores arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'required validation: ignores arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'required validation: ignores arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('""'));
            $this->assertTrue(true, 'required validation: ignores strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'required validation: ignores strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'required validation: ignores strings. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'required validation: ignores other non-objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'required validation: ignores other non-objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'required validation: ignores other non-objects. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"foo":{}}}');
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'required default validation: not required by default. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'required default validation: not required by default. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'required default validation: not required by default. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"foo":{}},"required":[]}');
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'required with empty array: property not required. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'required with empty array: property not required. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'required with empty array: property not required. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"required":["foo\nbar","foo\"bar","foo\\bar","foo\rbar","foo\tbar","foo\fbar"]}');
        try {
            $schema->validate(json_decode('{"foo\nbar":1,"foo\"bar":1,"foo\\bar":1,"foo\rbar":1,"foo\tbar":1,"foo\fbar":1}'));
            $this->assertTrue(true, 'required with escaped characters: object with all properties present is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'required with escaped characters: object with all properties present is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'required with escaped characters: object with all properties present is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo\nbar":"1","foo\"bar":"1"}'));
            $this->assertTrue(false, 'required with escaped characters: object with some properties missing is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'required with escaped characters: object with some properties missing is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'required with escaped characters: object with some properties missing is invalid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testId(): void
    {
        $schema = Schema::fromJson('{"$ref":"https:\/\/json-schema.org\/draft\/2020-12\/schema"}');
        try {
            $schema->validate(json_decode('{"$ref":"#foo","$defs":{"A":{"$id":"#foo","type":"integer"}}}'));
            $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier name. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Invalid use of fragments in location-independent $id: Identifier name. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier name. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$defs":{"A":{"$id":"#foo"}}}'));
            $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier name and no ref. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Invalid use of fragments in location-independent $id: Identifier name and no ref. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier name and no ref. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$ref":"#\/a\/b","$defs":{"A":{"$id":"#\/a\/b","type":"integer"}}}'));
            $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier path. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Invalid use of fragments in location-independent $id: Identifier path. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier path. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$ref":"http:\/\/localhost:1234\/bar#foo","$defs":{"A":{"$id":"http:\/\/localhost:1234\/bar#foo","type":"integer"}}}'));
            $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier name with absolute URI. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Invalid use of fragments in location-independent $id: Identifier name with absolute URI. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier name with absolute URI. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$ref":"http:\/\/localhost:1234\/bar#\/a\/b","$defs":{"A":{"$id":"http:\/\/localhost:1234\/bar#\/a\/b","type":"integer"}}}'));
            $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier path with absolute URI. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Invalid use of fragments in location-independent $id: Identifier path with absolute URI. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier path with absolute URI. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$id":"http:\/\/localhost:1234\/root","$ref":"http:\/\/localhost:1234\/nested.json#foo","$defs":{"A":{"$id":"nested.json","$defs":{"B":{"$id":"#foo","type":"integer"}}}}}'));
            $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier name with base URI change in subschema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Invalid use of fragments in location-independent $id: Identifier name with base URI change in subschema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier name with base URI change in subschema. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$id":"http:\/\/localhost:1234\/root","$ref":"http:\/\/localhost:1234\/nested.json#\/a\/b","$defs":{"A":{"$id":"nested.json","$defs":{"B":{"$id":"#\/a\/b","type":"integer"}}}}}'));
            $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier path with base URI change in subschema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Invalid use of fragments in location-independent $id: Identifier path with base URI change in subschema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Invalid use of fragments in location-independent $id: Identifier path with base URI change in subschema. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$ref":"https:\/\/json-schema.org\/draft\/2020-12\/schema"}');
        try {
            $schema->validate(json_decode('{"$ref":"http:\/\/localhost:1234\/bar","$defs":{"A":{"$id":"http:\/\/localhost:1234\/bar#","type":"integer"}}}'));
            $this->assertTrue(true, 'Valid use of empty fragments in location-independent $id: Identifier name with absolute URI. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'Valid use of empty fragments in location-independent $id: Identifier name with absolute URI. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Valid use of empty fragments in location-independent $id: Identifier name with absolute URI. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$id":"http:\/\/localhost:1234\/root","$ref":"http:\/\/localhost:1234\/nested.json#\/$defs\/B","$defs":{"A":{"$id":"nested.json","$defs":{"B":{"$id":"#","type":"integer"}}}}}'));
            $this->assertTrue(true, 'Valid use of empty fragments in location-independent $id: Identifier name with base URI change in subschema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'Valid use of empty fragments in location-independent $id: Identifier name with base URI change in subschema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Valid use of empty fragments in location-independent $id: Identifier name with base URI change in subschema. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$ref":"https:\/\/json-schema.org\/draft\/2020-12\/schema"}');
        try {
            $schema->validate(json_decode('{"$ref":"http:\/\/localhost:1234\/foo\/baz","$defs":{"A":{"$id":"http:\/\/localhost:1234\/foo\/bar\/..\/baz","type":"integer"}}}'));
            $this->assertTrue(true, 'Unnormalized $ids are allowed but discouraged: Unnormalized identifier. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'Unnormalized $ids are allowed but discouraged: Unnormalized identifier. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Unnormalized $ids are allowed but discouraged: Unnormalized identifier. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$defs":{"A":{"$id":"http:\/\/localhost:1234\/foo\/bar\/..\/baz","type":"integer"}}}'));
            $this->assertTrue(true, 'Unnormalized $ids are allowed but discouraged: Unnormalized identifier and no ref. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'Unnormalized $ids are allowed but discouraged: Unnormalized identifier and no ref. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Unnormalized $ids are allowed but discouraged: Unnormalized identifier and no ref. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$ref":"http:\/\/localhost:1234\/foo\/baz","$defs":{"A":{"$id":"http:\/\/localhost:1234\/foo\/bar\/..\/baz#","type":"integer"}}}'));
            $this->assertTrue(true, 'Unnormalized $ids are allowed but discouraged: Unnormalized identifier with empty fragment. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'Unnormalized $ids are allowed but discouraged: Unnormalized identifier with empty fragment. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Unnormalized $ids are allowed but discouraged: Unnormalized identifier with empty fragment. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$defs":{"A":{"$id":"http:\/\/localhost:1234\/foo\/bar\/..\/baz#","type":"integer"}}}'));
            $this->assertTrue(true, 'Unnormalized $ids are allowed but discouraged: Unnormalized identifier with empty fragment and no ref. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'Unnormalized $ids are allowed but discouraged: Unnormalized identifier with empty fragment and no ref. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Unnormalized $ids are allowed but discouraged: Unnormalized identifier with empty fragment and no ref. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$defs":{"id_in_enum":{"enum":[{"$id":"https:\/\/localhost:1234\/id\/my_identifier.json","type":"null"}]},"real_id_in_schema":{"$id":"https:\/\/localhost:1234\/id\/my_identifier.json","type":"string"},"zzz_id_in_const":{"const":{"$id":"https:\/\/localhost:1234\/id\/my_identifier.json","type":"null"}}},"anyOf":[{"$ref":"#\/$defs\/id_in_enum"},{"$ref":"https:\/\/localhost:1234\/id\/my_identifier.json"}]}');
        try {
            $schema->validate(json_decode('{"$id":"https:\/\/localhost:1234\/id\/my_identifier.json","type":"null"}'));
            $this->assertTrue(true, '$id inside an enum is not a real identifier: exact match to enum, and type matches. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, '$id inside an enum is not a real identifier: exact match to enum, and type matches. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$id inside an enum is not a real identifier: exact match to enum, and type matches. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"a string to match #\/$defs\/id_in_enum"'));
            $this->assertTrue(true, '$id inside an enum is not a real identifier: match $ref to $id. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, '$id inside an enum is not a real identifier: match $ref to $id. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$id inside an enum is not a real identifier: match $ref to $id. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, '$id inside an enum is not a real identifier: no match on enum or $ref to $id. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, '$id inside an enum is not a real identifier: no match on enum or $ref to $id. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$id inside an enum is not a real identifier: no match on enum or $ref to $id. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testType(): void
    {
        $schema = Schema::fromJson('{"type":"integer"}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'integer type matches integers: an integer is an integer. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'integer type matches integers: an integer is an integer. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'integer type matches integers: an integer is an integer. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'integer type matches integers: a float with zero fractional part is an integer. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'integer type matches integers: a float with zero fractional part is an integer. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'integer type matches integers: a float with zero fractional part is an integer. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1.1'));
            $this->assertTrue(false, 'integer type matches integers: a float is not an integer. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'integer type matches integers: a float is not an integer. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'integer type matches integers: a float is not an integer. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'integer type matches integers: a string is not an integer. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'integer type matches integers: a string is not an integer. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'integer type matches integers: a string is not an integer. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"1"'));
            $this->assertTrue(false, 'integer type matches integers: a string is still not an integer, even if it looks like one. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'integer type matches integers: a string is still not an integer, even if it looks like one. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'integer type matches integers: a string is still not an integer, even if it looks like one. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(false, 'integer type matches integers: an object is not an integer. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'integer type matches integers: an object is not an integer. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'integer type matches integers: an object is not an integer. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'integer type matches integers: an array is not an integer. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'integer type matches integers: an array is not an integer. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'integer type matches integers: an array is not an integer. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(false, 'integer type matches integers: a boolean is not an integer. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'integer type matches integers: a boolean is not an integer. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'integer type matches integers: a boolean is not an integer. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(false, 'integer type matches integers: null is not an integer. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'integer type matches integers: null is not an integer. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'integer type matches integers: null is not an integer. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"number"}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'number type matches numbers: an integer is a number. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'number type matches numbers: an integer is a number. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'number type matches numbers: an integer is a number. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'number type matches numbers: a float with zero fractional part is a number (and an integer). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'number type matches numbers: a float with zero fractional part is a number (and an integer). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'number type matches numbers: a float with zero fractional part is a number (and an integer). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1.1'));
            $this->assertTrue(true, 'number type matches numbers: a float is a number. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'number type matches numbers: a float is a number. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'number type matches numbers: a float is a number. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'number type matches numbers: a string is not a number. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'number type matches numbers: a string is not a number. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'number type matches numbers: a string is not a number. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"1"'));
            $this->assertTrue(false, 'number type matches numbers: a string is still not a number, even if it looks like one. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'number type matches numbers: a string is still not a number, even if it looks like one. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'number type matches numbers: a string is still not a number, even if it looks like one. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(false, 'number type matches numbers: an object is not a number. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'number type matches numbers: an object is not a number. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'number type matches numbers: an object is not a number. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'number type matches numbers: an array is not a number. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'number type matches numbers: an array is not a number. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'number type matches numbers: an array is not a number. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(false, 'number type matches numbers: a boolean is not a number. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'number type matches numbers: a boolean is not a number. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'number type matches numbers: a boolean is not a number. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(false, 'number type matches numbers: null is not a number. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'number type matches numbers: null is not a number. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'number type matches numbers: null is not a number. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"string"}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'string type matches strings: 1 is not a string. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'string type matches strings: 1 is not a string. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'string type matches strings: 1 is not a string. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1.1'));
            $this->assertTrue(false, 'string type matches strings: a float is not a string. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'string type matches strings: a float is not a string. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'string type matches strings: a float is not a string. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'string type matches strings: a string is a string. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'string type matches strings: a string is a string. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'string type matches strings: a string is a string. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"1"'));
            $this->assertTrue(true, 'string type matches strings: a string is still a string, even if it looks like a number. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'string type matches strings: a string is still a string, even if it looks like a number. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'string type matches strings: a string is still a string, even if it looks like a number. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('""'));
            $this->assertTrue(true, 'string type matches strings: an empty string is still a string. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'string type matches strings: an empty string is still a string. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'string type matches strings: an empty string is still a string. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(false, 'string type matches strings: an object is not a string. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'string type matches strings: an object is not a string. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'string type matches strings: an object is not a string. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'string type matches strings: an array is not a string. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'string type matches strings: an array is not a string. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'string type matches strings: an array is not a string. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(false, 'string type matches strings: a boolean is not a string. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'string type matches strings: a boolean is not a string. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'string type matches strings: a boolean is not a string. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(false, 'string type matches strings: null is not a string. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'string type matches strings: null is not a string. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'string type matches strings: null is not a string. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object"}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'object type matches objects: an integer is not an object. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'object type matches objects: an integer is not an object. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'object type matches objects: an integer is not an object. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1.1'));
            $this->assertTrue(false, 'object type matches objects: a float is not an object. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'object type matches objects: a float is not an object. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'object type matches objects: a float is not an object. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'object type matches objects: a string is not an object. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'object type matches objects: a string is not an object. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'object type matches objects: a string is not an object. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'object type matches objects: an object is an object. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'object type matches objects: an object is an object. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'object type matches objects: an object is an object. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'object type matches objects: an array is not an object. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'object type matches objects: an array is not an object. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'object type matches objects: an array is not an object. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(false, 'object type matches objects: a boolean is not an object. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'object type matches objects: a boolean is not an object. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'object type matches objects: a boolean is not an object. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(false, 'object type matches objects: null is not an object. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'object type matches objects: null is not an object. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'object type matches objects: null is not an object. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"array"}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'array type matches arrays: an integer is not an array. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'array type matches arrays: an integer is not an array. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'array type matches arrays: an integer is not an array. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1.1'));
            $this->assertTrue(false, 'array type matches arrays: a float is not an array. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'array type matches arrays: a float is not an array. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'array type matches arrays: a float is not an array. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'array type matches arrays: a string is not an array. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'array type matches arrays: a string is not an array. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'array type matches arrays: a string is not an array. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(false, 'array type matches arrays: an object is not an array. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'array type matches arrays: an object is not an array. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'array type matches arrays: an object is not an array. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'array type matches arrays: an array is an array. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'array type matches arrays: an array is an array. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'array type matches arrays: an array is an array. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(false, 'array type matches arrays: a boolean is not an array. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'array type matches arrays: a boolean is not an array. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'array type matches arrays: a boolean is not an array. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(false, 'array type matches arrays: null is not an array. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'array type matches arrays: null is not an array. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'array type matches arrays: null is not an array. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"boolean"}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'boolean type matches booleans: an integer is not a boolean. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean type matches booleans: an integer is not a boolean. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean type matches booleans: an integer is not a boolean. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(false, 'boolean type matches booleans: zero is not a boolean. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean type matches booleans: zero is not a boolean. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean type matches booleans: zero is not a boolean. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1.1'));
            $this->assertTrue(false, 'boolean type matches booleans: a float is not a boolean. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean type matches booleans: a float is not a boolean. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean type matches booleans: a float is not a boolean. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'boolean type matches booleans: a string is not a boolean. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean type matches booleans: a string is not a boolean. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean type matches booleans: a string is not a boolean. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('""'));
            $this->assertTrue(false, 'boolean type matches booleans: an empty string is not a boolean. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean type matches booleans: an empty string is not a boolean. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean type matches booleans: an empty string is not a boolean. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(false, 'boolean type matches booleans: an object is not a boolean. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean type matches booleans: an object is not a boolean. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean type matches booleans: an object is not a boolean. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'boolean type matches booleans: an array is not a boolean. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean type matches booleans: an array is not a boolean. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean type matches booleans: an array is not a boolean. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(true, 'boolean type matches booleans: true is a boolean. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'boolean type matches booleans: true is a boolean. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean type matches booleans: true is a boolean. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'boolean type matches booleans: false is a boolean. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'boolean type matches booleans: false is a boolean. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean type matches booleans: false is a boolean. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(false, 'boolean type matches booleans: null is not a boolean. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'boolean type matches booleans: null is not a boolean. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'boolean type matches booleans: null is not a boolean. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"null"}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'null type matches only the null object: an integer is not null. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'null type matches only the null object: an integer is not null. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'null type matches only the null object: an integer is not null. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1.1'));
            $this->assertTrue(false, 'null type matches only the null object: a float is not null. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'null type matches only the null object: a float is not null. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'null type matches only the null object: a float is not null. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(false, 'null type matches only the null object: zero is not null. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'null type matches only the null object: zero is not null. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'null type matches only the null object: zero is not null. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'null type matches only the null object: a string is not null. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'null type matches only the null object: a string is not null. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'null type matches only the null object: a string is not null. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('""'));
            $this->assertTrue(false, 'null type matches only the null object: an empty string is not null. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'null type matches only the null object: an empty string is not null. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'null type matches only the null object: an empty string is not null. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(false, 'null type matches only the null object: an object is not null. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'null type matches only the null object: an object is not null. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'null type matches only the null object: an object is not null. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'null type matches only the null object: an array is not null. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'null type matches only the null object: an array is not null. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'null type matches only the null object: an array is not null. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(false, 'null type matches only the null object: true is not null. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'null type matches only the null object: true is not null. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'null type matches only the null object: true is not null. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(false, 'null type matches only the null object: false is not null. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'null type matches only the null object: false is not null. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'null type matches only the null object: false is not null. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'null type matches only the null object: null is null. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'null type matches only the null object: null is null. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'null type matches only the null object: null is null. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":["integer","string"]}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'multiple types can be specified in an array: an integer is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'multiple types can be specified in an array: an integer is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple types can be specified in an array: an integer is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'multiple types can be specified in an array: a string is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'multiple types can be specified in an array: a string is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple types can be specified in an array: a string is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1.1'));
            $this->assertTrue(false, 'multiple types can be specified in an array: a float is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'multiple types can be specified in an array: a float is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple types can be specified in an array: a float is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(false, 'multiple types can be specified in an array: an object is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'multiple types can be specified in an array: an object is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple types can be specified in an array: an object is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'multiple types can be specified in an array: an array is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'multiple types can be specified in an array: an array is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple types can be specified in an array: an array is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('true'));
            $this->assertTrue(false, 'multiple types can be specified in an array: a boolean is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'multiple types can be specified in an array: a boolean is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple types can be specified in an array: a boolean is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(false, 'multiple types can be specified in an array: null is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'multiple types can be specified in an array: null is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple types can be specified in an array: null is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":["string"]}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'type as array with one item: string is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'type as array with one item: string is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'type as array with one item: string is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('123'));
            $this->assertTrue(false, 'type as array with one item: number is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'type as array with one item: number is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'type as array with one item: number is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":["array","object"]}');
        try {
            $schema->validate(json_decode('[1,2,3]'));
            $this->assertTrue(true, 'type: array or object: array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'type: array or object: array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'type: array or object: array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":123}'));
            $this->assertTrue(true, 'type: array or object: object is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'type: array or object: object is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'type: array or object: object is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('123'));
            $this->assertTrue(false, 'type: array or object: number is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'type: array or object: number is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'type: array or object: number is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'type: array or object: string is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'type: array or object: string is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'type: array or object: string is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(false, 'type: array or object: null is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'type: array or object: null is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'type: array or object: null is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":["array","object","null"]}');
        try {
            $schema->validate(json_decode('[1,2,3]'));
            $this->assertTrue(true, 'type: array, object or null: array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'type: array, object or null: array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'type: array, object or null: array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":123}'));
            $this->assertTrue(true, 'type: array, object or null: object is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'type: array, object or null: object is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'type: array, object or null: object is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'type: array, object or null: null is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'type: array, object or null: null is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'type: array, object or null: null is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('123'));
            $this->assertTrue(false, 'type: array, object or null: number is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'type: array, object or null: number is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'type: array, object or null: number is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'type: array, object or null: string is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'type: array, object or null: string is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'type: array, object or null: string is invalid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testDefault(): void
    {
        $schema = Schema::fromJson('{"properties":{"foo":{"type":"integer","default":[]}}}');
        try {
            $schema->validate(json_decode('{"foo":13}'));
            $this->assertTrue(true, 'invalid type for default: valid when property is specified. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'invalid type for default: valid when property is specified. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'invalid type for default: valid when property is specified. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'invalid type for default: still valid when the invalid default is used. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'invalid type for default: still valid when the invalid default is used. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'invalid type for default: still valid when the invalid default is used. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"bar":{"type":"string","minLength":4,"default":"bad"}}}');
        try {
            $schema->validate(json_decode('{"bar":"good"}'));
            $this->assertTrue(true, 'invalid string value for default: valid when property is specified. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'invalid string value for default: valid when property is specified. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'invalid string value for default: valid when property is specified. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'invalid string value for default: still valid when the invalid default is used. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'invalid string value for default: still valid when the invalid default is used. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'invalid string value for default: still valid when the invalid default is used. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"object","properties":{"alpha":{"type":"number","maximum":3,"default":5}}}');
        try {
            $schema->validate(json_decode('{"alpha":1}'));
            $this->assertTrue(true, 'the default keyword does not do anything if the property is missing: an explicit property value is checked against maximum (passing). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'the default keyword does not do anything if the property is missing: an explicit property value is checked against maximum (passing). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'the default keyword does not do anything if the property is missing: an explicit property value is checked against maximum (passing). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"alpha":5}'));
            $this->assertTrue(false, 'the default keyword does not do anything if the property is missing: an explicit property value is checked against maximum (failing). Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'the default keyword does not do anything if the property is missing: an explicit property value is checked against maximum (failing). Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'the default keyword does not do anything if the property is missing: an explicit property value is checked against maximum (failing). Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'the default keyword does not do anything if the property is missing: missing properties are not filled in with the default. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'the default keyword does not do anything if the property is missing: missing properties are not filled in with the default. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'the default keyword does not do anything if the property is missing: missing properties are not filled in with the default. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testMultipleOf(): void
    {
        $schema = Schema::fromJson('{"multipleOf":2}');
        try {
            $schema->validate(json_decode('10'));
            $this->assertTrue(true, 'by int: int by int. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'by int: int by int. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'by int: int by int. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('7'));
            $this->assertTrue(false, 'by int: int by int fail. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'by int: int by int fail. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'by int: int by int fail. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'by int: ignores non-numbers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'by int: ignores non-numbers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'by int: ignores non-numbers. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"multipleOf":1.5}');
        try {
            $schema->validate(json_decode('0'));
            $this->assertTrue(true, 'by number: zero is multiple of anything. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'by number: zero is multiple of anything. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'by number: zero is multiple of anything. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('4.5'));
            $this->assertTrue(true, 'by number: 4.5 is multiple of 1.5. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'by number: 4.5 is multiple of 1.5. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'by number: 4.5 is multiple of 1.5. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('35'));
            $this->assertTrue(false, 'by number: 35 is not multiple of 1.5. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'by number: 35 is not multiple of 1.5. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'by number: 35 is not multiple of 1.5. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"multipleOf":0.0001}');
        try {
            $schema->validate(json_decode('0.0075'));
            $this->assertTrue(true, 'by small number: 0.0075 is multiple of 0.0001. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'by small number: 0.0075 is multiple of 0.0001. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'by small number: 0.0075 is multiple of 0.0001. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('0.00751'));
            $this->assertTrue(false, 'by small number: 0.00751 is not multiple of 0.0001. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'by small number: 0.00751 is not multiple of 0.0001. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'by small number: 0.00751 is not multiple of 0.0001. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"type":"integer","multipleOf":0.123456789}');
        try {
            $schema->validate(json_decode('1.0e+308'));
            $this->assertTrue(false, 'invalid instance should not raise error when float division = inf: always invalid, but naive implementations may raise an overflow error. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'invalid instance should not raise error when float division = inf: always invalid, but naive implementations may raise an overflow error. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'invalid instance should not raise error when float division = inf: always invalid, but naive implementations may raise an overflow error. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testPatternProperties(): void
    {
        $schema = Schema::fromJson('{"patternProperties":{"f.*o":{"type":"integer"}}}');
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'patternProperties validates properties matching a regex: a single valid match is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'patternProperties validates properties matching a regex: a single valid match is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'patternProperties validates properties matching a regex: a single valid match is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"foooooo":2}'));
            $this->assertTrue(true, 'patternProperties validates properties matching a regex: multiple valid matches is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'patternProperties validates properties matching a regex: multiple valid matches is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'patternProperties validates properties matching a regex: multiple valid matches is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"bar","fooooo":2}'));
            $this->assertTrue(false, 'patternProperties validates properties matching a regex: a single invalid match is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'patternProperties validates properties matching a regex: a single invalid match is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'patternProperties validates properties matching a regex: a single invalid match is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"bar","foooooo":"baz"}'));
            $this->assertTrue(false, 'patternProperties validates properties matching a regex: multiple invalid matches is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'patternProperties validates properties matching a regex: multiple invalid matches is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'patternProperties validates properties matching a regex: multiple invalid matches is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo"]'));
            $this->assertTrue(true, 'patternProperties validates properties matching a regex: ignores arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'patternProperties validates properties matching a regex: ignores arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'patternProperties validates properties matching a regex: ignores arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'patternProperties validates properties matching a regex: ignores strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'patternProperties validates properties matching a regex: ignores strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'patternProperties validates properties matching a regex: ignores strings. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'patternProperties validates properties matching a regex: ignores other non-objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'patternProperties validates properties matching a regex: ignores other non-objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'patternProperties validates properties matching a regex: ignores other non-objects. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"patternProperties":{"a*":{"type":"integer"},"aaa*":{"maximum":20}}}');
        try {
            $schema->validate(json_decode('{"a":21}'));
            $this->assertTrue(true, 'multiple simultaneous patternProperties are validated: a single valid match is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'multiple simultaneous patternProperties are validated: a single valid match is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple simultaneous patternProperties are validated: a single valid match is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"aaaa":18}'));
            $this->assertTrue(true, 'multiple simultaneous patternProperties are validated: a simultaneous match is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'multiple simultaneous patternProperties are validated: a simultaneous match is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple simultaneous patternProperties are validated: a simultaneous match is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":21,"aaaa":18}'));
            $this->assertTrue(true, 'multiple simultaneous patternProperties are validated: multiple matches is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'multiple simultaneous patternProperties are validated: multiple matches is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple simultaneous patternProperties are validated: multiple matches is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a":"bar"}'));
            $this->assertTrue(false, 'multiple simultaneous patternProperties are validated: an invalid due to one is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'multiple simultaneous patternProperties are validated: an invalid due to one is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple simultaneous patternProperties are validated: an invalid due to one is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"aaaa":31}'));
            $this->assertTrue(false, 'multiple simultaneous patternProperties are validated: an invalid due to the other is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'multiple simultaneous patternProperties are validated: an invalid due to the other is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple simultaneous patternProperties are validated: an invalid due to the other is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"aaa":"foo","aaaa":31}'));
            $this->assertTrue(false, 'multiple simultaneous patternProperties are validated: an invalid due to both is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'multiple simultaneous patternProperties are validated: an invalid due to both is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple simultaneous patternProperties are validated: an invalid due to both is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"patternProperties":{"[0-9]{2,}":{"type":"boolean"},"X_":{"type":"string"}}}');
        try {
            $schema->validate(json_decode('{"answer 1":"42"}'));
            $this->assertTrue(true, 'regexes are not anchored by default and are case sensitive: non recognized members are ignored. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'regexes are not anchored by default and are case sensitive: non recognized members are ignored. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'regexes are not anchored by default and are case sensitive: non recognized members are ignored. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a31b":null}'));
            $this->assertTrue(false, 'regexes are not anchored by default and are case sensitive: recognized members are accounted for. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'regexes are not anchored by default and are case sensitive: recognized members are accounted for. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'regexes are not anchored by default and are case sensitive: recognized members are accounted for. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a_x_3":3}'));
            $this->assertTrue(true, 'regexes are not anchored by default and are case sensitive: regexes are case sensitive. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'regexes are not anchored by default and are case sensitive: regexes are case sensitive. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'regexes are not anchored by default and are case sensitive: regexes are case sensitive. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"a_X_3":3}'));
            $this->assertTrue(false, 'regexes are not anchored by default and are case sensitive: regexes are case sensitive, 2. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'regexes are not anchored by default and are case sensitive: regexes are case sensitive, 2. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'regexes are not anchored by default and are case sensitive: regexes are case sensitive, 2. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"patternProperties":{"f.*":true,"b.*":false}}');
        try {
            $schema->validate(json_decode('{"foo":1}'));
            $this->assertTrue(true, 'patternProperties with boolean schemas: object with property matching schema true is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'patternProperties with boolean schemas: object with property matching schema true is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'patternProperties with boolean schemas: object with property matching schema true is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":2}'));
            $this->assertTrue(false, 'patternProperties with boolean schemas: object with property matching schema false is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'patternProperties with boolean schemas: object with property matching schema false is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'patternProperties with boolean schemas: object with property matching schema false is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":1,"bar":2}'));
            $this->assertTrue(false, 'patternProperties with boolean schemas: object with both properties is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'patternProperties with boolean schemas: object with both properties is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'patternProperties with boolean schemas: object with both properties is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foobar":1}'));
            $this->assertTrue(false, 'patternProperties with boolean schemas: object with a property matching both true and false is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'patternProperties with boolean schemas: object with a property matching both true and false is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'patternProperties with boolean schemas: object with a property matching both true and false is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'patternProperties with boolean schemas: empty object is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'patternProperties with boolean schemas: empty object is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'patternProperties with boolean schemas: empty object is valid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testRefRemote(): void
    {
        $schema = Schema::fromJson('{"$ref":"http:\/\/localhost:1234\/integer.json"}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'remote ref: remote ref valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'remote ref: remote ref valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'remote ref: remote ref valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"a"'));
            $this->assertTrue(false, 'remote ref: remote ref invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'remote ref: remote ref invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'remote ref: remote ref invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$ref":"http:\/\/localhost:1234\/subSchemas-defs.json#\/$defs\/integer"}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'fragment within remote ref: remote fragment valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'fragment within remote ref: remote fragment valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'fragment within remote ref: remote fragment valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"a"'));
            $this->assertTrue(false, 'fragment within remote ref: remote fragment invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'fragment within remote ref: remote fragment invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'fragment within remote ref: remote fragment invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$ref":"http:\/\/localhost:1234\/subSchemas-defs.json#\/$defs\/refToInteger"}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'ref within remote ref: ref within ref valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ref within remote ref: ref within ref valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ref within remote ref: ref within ref valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"a"'));
            $this->assertTrue(false, 'ref within remote ref: ref within ref invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'ref within remote ref: ref within ref invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ref within remote ref: ref within ref invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/localhost:1234\/","items":{"$id":"baseUriChange\/","items":{"$ref":"folderInteger.json"}}}');
        try {
            $schema->validate(json_decode('[[1]]'));
            $this->assertTrue(true, 'base URI change: base URI change ref valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'base URI change: base URI change ref valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'base URI change: base URI change ref valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[["a"]]'));
            $this->assertTrue(false, 'base URI change: base URI change ref invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'base URI change: base URI change ref invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'base URI change: base URI change ref invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/localhost:1234\/scope_change_defs1.json","type":"object","properties":{"list":{"$ref":"baseUriChangeFolder\/"}},"$defs":{"baz":{"$id":"baseUriChangeFolder\/","type":"array","items":{"$ref":"folderInteger.json"}}}}');
        try {
            $schema->validate(json_decode('{"list":[1]}'));
            $this->assertTrue(true, 'base URI change - change folder: number is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'base URI change - change folder: number is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'base URI change - change folder: number is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"list":["a"]}'));
            $this->assertTrue(false, 'base URI change - change folder: string is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'base URI change - change folder: string is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'base URI change - change folder: string is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/localhost:1234\/scope_change_defs2.json","type":"object","properties":{"list":{"$ref":"baseUriChangeFolderInSubschema\/#\/$defs\/bar"}},"$defs":{"baz":{"$id":"baseUriChangeFolderInSubschema\/","$defs":{"bar":{"type":"array","items":{"$ref":"folderInteger.json"}}}}}}');
        try {
            $schema->validate(json_decode('{"list":[1]}'));
            $this->assertTrue(true, 'base URI change - change folder in subschema: number is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'base URI change - change folder in subschema: number is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'base URI change - change folder in subschema: number is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"list":["a"]}'));
            $this->assertTrue(false, 'base URI change - change folder in subschema: string is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'base URI change - change folder in subschema: string is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'base URI change - change folder in subschema: string is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/localhost:1234\/object","type":"object","properties":{"name":{"$ref":"name-defs.json#\/$defs\/orNull"}}}');
        try {
            $schema->validate(json_decode('{"name":"foo"}'));
            $this->assertTrue(true, 'root ref in remote ref: string is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'root ref in remote ref: string is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'root ref in remote ref: string is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"name":null}'));
            $this->assertTrue(true, 'root ref in remote ref: null is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'root ref in remote ref: null is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'root ref in remote ref: null is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"name":{"name":null}}'));
            $this->assertTrue(false, 'root ref in remote ref: object is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'root ref in remote ref: object is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'root ref in remote ref: object is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/localhost:1234\/schema-remote-ref-ref-defs1.json","$ref":"ref-and-defs.json"}');
        try {
            $schema->validate(json_decode('{"bar":1}'));
            $this->assertTrue(false, 'remote ref with ref to defs: invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'remote ref with ref to defs: invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'remote ref with ref to defs: invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":"a"}'));
            $this->assertTrue(true, 'remote ref with ref to defs: valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'remote ref with ref to defs: valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'remote ref with ref to defs: valid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testAllOf(): void
    {
        $schema = Schema::fromJson('{"allOf":[{"properties":{"bar":{"type":"integer"}},"required":["bar"]},{"properties":{"foo":{"type":"string"}},"required":["foo"]}]}');
        try {
            $schema->validate(json_decode('{"foo":"baz","bar":2}'));
            $this->assertTrue(true, 'allOf: allOf. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'allOf: allOf. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf: allOf. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"baz"}'));
            $this->assertTrue(false, 'allOf: mismatch second. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf: mismatch second. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf: mismatch second. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":2}'));
            $this->assertTrue(false, 'allOf: mismatch first. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf: mismatch first. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf: mismatch first. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"baz","bar":"quux"}'));
            $this->assertTrue(false, 'allOf: wrong type. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf: wrong type. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf: wrong type. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"bar":{"type":"integer"}},"required":["bar"],"allOf":[{"properties":{"foo":{"type":"string"}},"required":["foo"]},{"properties":{"baz":{"type":"null"}},"required":["baz"]}]}');
        try {
            $schema->validate(json_decode('{"foo":"quux","bar":2,"baz":null}'));
            $this->assertTrue(true, 'allOf with base schema: valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'allOf with base schema: valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with base schema: valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"quux","baz":null}'));
            $this->assertTrue(false, 'allOf with base schema: mismatch base schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf with base schema: mismatch base schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with base schema: mismatch base schema. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":2,"baz":null}'));
            $this->assertTrue(false, 'allOf with base schema: mismatch first allOf. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf with base schema: mismatch first allOf. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with base schema: mismatch first allOf. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"quux","bar":2}'));
            $this->assertTrue(false, 'allOf with base schema: mismatch second allOf. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf with base schema: mismatch second allOf. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with base schema: mismatch second allOf. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":2}'));
            $this->assertTrue(false, 'allOf with base schema: mismatch both. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf with base schema: mismatch both. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with base schema: mismatch both. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[{"maximum":30},{"minimum":20}]}');
        try {
            $schema->validate(json_decode('25'));
            $this->assertTrue(true, 'allOf simple types: valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'allOf simple types: valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf simple types: valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('35'));
            $this->assertTrue(false, 'allOf simple types: mismatch one. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf simple types: mismatch one. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf simple types: mismatch one. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[true,true]}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'allOf with boolean schemas, all true: any value is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'allOf with boolean schemas, all true: any value is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with boolean schemas, all true: any value is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[true,false]}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'allOf with boolean schemas, some false: any value is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf with boolean schemas, some false: any value is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with boolean schemas, some false: any value is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[false,false]}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'allOf with boolean schemas, all false: any value is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf with boolean schemas, all false: any value is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with boolean schemas, all false: any value is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[{}]}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'allOf with one empty schema: any data is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'allOf with one empty schema: any data is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with one empty schema: any data is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[{},{}]}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'allOf with two empty schemas: any data is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'allOf with two empty schemas: any data is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with two empty schemas: any data is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[{},{"type":"number"}]}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'allOf with the first empty schema: number is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'allOf with the first empty schema: number is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with the first empty schema: number is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'allOf with the first empty schema: string is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf with the first empty schema: string is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with the first empty schema: string is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[{"type":"number"},{}]}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'allOf with the last empty schema: number is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'allOf with the last empty schema: number is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with the last empty schema: number is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, 'allOf with the last empty schema: string is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf with the last empty schema: string is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf with the last empty schema: string is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[{"allOf":[{"type":"null"}]}]}');
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'nested allOf, to check validation semantics: null is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'nested allOf, to check validation semantics: null is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested allOf, to check validation semantics: null is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('123'));
            $this->assertTrue(false, 'nested allOf, to check validation semantics: anything non-null is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'nested allOf, to check validation semantics: anything non-null is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested allOf, to check validation semantics: anything non-null is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"allOf":[{"multipleOf":2}],"anyOf":[{"multipleOf":3}],"oneOf":[{"multipleOf":5}]}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: false, anyOf: false, oneOf: false. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf combined with anyOf, oneOf: allOf: false, anyOf: false, oneOf: false. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: false, anyOf: false, oneOf: false. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('5'));
            $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: false, anyOf: false, oneOf: true. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf combined with anyOf, oneOf: allOf: false, anyOf: false, oneOf: true. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: false, anyOf: false, oneOf: true. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('3'));
            $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: false, anyOf: true, oneOf: false. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf combined with anyOf, oneOf: allOf: false, anyOf: true, oneOf: false. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: false, anyOf: true, oneOf: false. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('15'));
            $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: false, anyOf: true, oneOf: true. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf combined with anyOf, oneOf: allOf: false, anyOf: true, oneOf: true. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: false, anyOf: true, oneOf: true. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('2'));
            $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: true, anyOf: false, oneOf: false. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf combined with anyOf, oneOf: allOf: true, anyOf: false, oneOf: false. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: true, anyOf: false, oneOf: false. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('10'));
            $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: true, anyOf: false, oneOf: true. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf combined with anyOf, oneOf: allOf: true, anyOf: false, oneOf: true. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: true, anyOf: false, oneOf: true. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('6'));
            $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: true, anyOf: true, oneOf: false. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'allOf combined with anyOf, oneOf: allOf: true, anyOf: true, oneOf: false. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: true, anyOf: true, oneOf: false. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('30'));
            $this->assertTrue(true, 'allOf combined with anyOf, oneOf: allOf: true, anyOf: true, oneOf: true. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: true, anyOf: true, oneOf: true. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'allOf combined with anyOf, oneOf: allOf: true, anyOf: true, oneOf: true. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testFormat(): void
    {
        $schema = Schema::fromJson('{"format":"email"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'email format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'email format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'email format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'email format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'email format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'email format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'email format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'email format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'email format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'email format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'email format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'email format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'email format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'email format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'email format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'email format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'email format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'email format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"idn-email"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'idn-email format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'idn-email format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'idn-email format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'idn-email format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'idn-email format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'idn-email format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'idn-email format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'idn-email format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'idn-email format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'idn-email format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'idn-email format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'idn-email format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'idn-email format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'idn-email format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'idn-email format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'idn-email format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'idn-email format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'idn-email format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"regex"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'regex format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'regex format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'regex format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'regex format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'regex format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'regex format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'regex format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'regex format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'regex format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'regex format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'regex format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'regex format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'regex format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'regex format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'regex format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'regex format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'regex format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'regex format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"ipv4"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'ipv4 format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ipv4 format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ipv4 format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'ipv4 format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ipv4 format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ipv4 format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'ipv4 format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ipv4 format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ipv4 format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'ipv4 format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ipv4 format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ipv4 format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'ipv4 format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ipv4 format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ipv4 format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'ipv4 format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ipv4 format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ipv4 format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"ipv6"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'ipv6 format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ipv6 format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ipv6 format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'ipv6 format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ipv6 format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ipv6 format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'ipv6 format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ipv6 format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ipv6 format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'ipv6 format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ipv6 format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ipv6 format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'ipv6 format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ipv6 format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ipv6 format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'ipv6 format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ipv6 format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ipv6 format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"idn-hostname"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'idn-hostname format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'idn-hostname format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'idn-hostname format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'idn-hostname format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'idn-hostname format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'idn-hostname format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'idn-hostname format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'idn-hostname format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'idn-hostname format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'idn-hostname format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'idn-hostname format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'idn-hostname format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'idn-hostname format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'idn-hostname format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'idn-hostname format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'idn-hostname format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'idn-hostname format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'idn-hostname format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"hostname"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'hostname format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'hostname format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'hostname format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'hostname format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'hostname format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'hostname format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'hostname format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'hostname format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'hostname format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'hostname format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'hostname format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'hostname format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'hostname format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'hostname format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'hostname format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'hostname format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'hostname format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'hostname format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"date"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'date format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'date format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'date format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'date format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'date format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'date format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'date format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'date format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'date format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'date format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'date format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'date format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'date format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'date format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'date format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'date format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'date format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'date format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"date-time"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'date-time format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'date-time format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'date-time format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'date-time format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'date-time format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'date-time format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'date-time format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'date-time format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'date-time format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'date-time format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'date-time format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'date-time format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'date-time format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'date-time format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'date-time format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'date-time format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'date-time format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'date-time format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"time"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'time format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'time format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'time format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'time format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'time format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'time format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'time format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'time format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'time format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'time format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'time format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'time format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'time format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'time format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'time format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'time format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'time format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'time format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"json-pointer"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'json-pointer format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'json-pointer format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'json-pointer format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'json-pointer format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'json-pointer format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'json-pointer format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'json-pointer format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'json-pointer format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'json-pointer format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'json-pointer format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'json-pointer format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'json-pointer format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'json-pointer format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'json-pointer format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'json-pointer format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'json-pointer format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'json-pointer format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'json-pointer format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"relative-json-pointer"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'relative-json-pointer format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'relative-json-pointer format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'relative-json-pointer format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'relative-json-pointer format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'relative-json-pointer format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'relative-json-pointer format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'relative-json-pointer format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'relative-json-pointer format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'relative-json-pointer format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'relative-json-pointer format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'relative-json-pointer format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'relative-json-pointer format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'relative-json-pointer format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'relative-json-pointer format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'relative-json-pointer format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'relative-json-pointer format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'relative-json-pointer format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'relative-json-pointer format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"iri"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'iri format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'iri format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'iri format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'iri format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'iri format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'iri format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'iri format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'iri format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'iri format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'iri format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'iri format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'iri format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'iri format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'iri format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'iri format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'iri format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'iri format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'iri format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"iri-reference"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'iri-reference format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'iri-reference format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'iri-reference format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'iri-reference format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'iri-reference format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'iri-reference format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'iri-reference format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'iri-reference format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'iri-reference format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'iri-reference format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'iri-reference format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'iri-reference format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'iri-reference format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'iri-reference format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'iri-reference format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'iri-reference format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'iri-reference format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'iri-reference format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"uri"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'uri format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'uri format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'uri format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'uri format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'uri format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'uri format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"uri-reference"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'uri-reference format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri-reference format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri-reference format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'uri-reference format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri-reference format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri-reference format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'uri-reference format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri-reference format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri-reference format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'uri-reference format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri-reference format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri-reference format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'uri-reference format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri-reference format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri-reference format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'uri-reference format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri-reference format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri-reference format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"uri-template"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'uri-template format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri-template format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri-template format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'uri-template format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri-template format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri-template format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'uri-template format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri-template format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri-template format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'uri-template format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri-template format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri-template format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'uri-template format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri-template format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri-template format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'uri-template format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uri-template format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uri-template format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"uuid"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'uuid format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uuid format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uuid format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'uuid format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uuid format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uuid format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'uuid format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uuid format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uuid format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'uuid format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uuid format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uuid format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'uuid format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uuid format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uuid format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'uuid format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'uuid format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'uuid format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"format":"duration"}');
        try {
            $schema->validate(json_decode('12'));
            $this->assertTrue(true, 'duration format: all string formats ignore integers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'duration format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'duration format: all string formats ignore integers. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('13.7'));
            $this->assertTrue(true, 'duration format: all string formats ignore floats. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'duration format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'duration format: all string formats ignore floats. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'duration format: all string formats ignore objects. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'duration format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'duration format: all string formats ignore objects. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(true, 'duration format: all string formats ignore arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'duration format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'duration format: all string formats ignore arrays. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('false'));
            $this->assertTrue(true, 'duration format: all string formats ignore booleans. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'duration format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'duration format: all string formats ignore booleans. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'duration format: all string formats ignore nulls. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'duration format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'duration format: all string formats ignore nulls. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testRef(): void
    {
        $schema = Schema::fromJson('{"properties":{"foo":{"$ref":"#"}},"additionalProperties":false}');
        try {
            $schema->validate(json_decode('{"foo":false}'));
            $this->assertTrue(true, 'root pointer ref: match. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'root pointer ref: match. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'root pointer ref: match. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":{"foo":false}}'));
            $this->assertTrue(true, 'root pointer ref: recursive match. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'root pointer ref: recursive match. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'root pointer ref: recursive match. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":false}'));
            $this->assertTrue(false, 'root pointer ref: mismatch. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'root pointer ref: mismatch. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'root pointer ref: mismatch. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":{"bar":false}}'));
            $this->assertTrue(false, 'root pointer ref: recursive mismatch. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'root pointer ref: recursive mismatch. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'root pointer ref: recursive mismatch. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"foo":{"type":"integer"},"bar":{"$ref":"#\/properties\/foo"}}}');
        try {
            $schema->validate(json_decode('{"bar":3}'));
            $this->assertTrue(true, 'relative pointer ref to object: match. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'relative pointer ref to object: match. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'relative pointer ref to object: match. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"bar":true}'));
            $this->assertTrue(false, 'relative pointer ref to object: mismatch. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'relative pointer ref to object: mismatch. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'relative pointer ref to object: mismatch. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"prefixItems":[{"type":"integer"},{"$ref":"#\/prefixItems\/0"}]}');
        try {
            $schema->validate(json_decode('[1,2]'));
            $this->assertTrue(true, 'relative pointer ref to array: match array. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'relative pointer ref to array: match array. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'relative pointer ref to array: match array. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,"foo"]'));
            $this->assertTrue(false, 'relative pointer ref to array: mismatch array. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'relative pointer ref to array: mismatch array. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'relative pointer ref to array: mismatch array. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$defs":{"tilde~field":{"type":"integer"},"slash\/field":{"type":"integer"},"percent%field":{"type":"integer"}},"properties":{"tilde":{"$ref":"#\/$defs\/tilde~0field"},"slash":{"$ref":"#\/$defs\/slash~1field"},"percent":{"$ref":"#\/$defs\/percent%25field"}}}');
        try {
            $schema->validate(json_decode('{"slash":"aoeu"}'));
            $this->assertTrue(false, 'escaped pointer ref: slash invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'escaped pointer ref: slash invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'escaped pointer ref: slash invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"tilde":"aoeu"}'));
            $this->assertTrue(false, 'escaped pointer ref: tilde invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'escaped pointer ref: tilde invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'escaped pointer ref: tilde invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"percent":"aoeu"}'));
            $this->assertTrue(false, 'escaped pointer ref: percent invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'escaped pointer ref: percent invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'escaped pointer ref: percent invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"slash":123}'));
            $this->assertTrue(true, 'escaped pointer ref: slash valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'escaped pointer ref: slash valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'escaped pointer ref: slash valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"tilde":123}'));
            $this->assertTrue(true, 'escaped pointer ref: tilde valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'escaped pointer ref: tilde valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'escaped pointer ref: tilde valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"percent":123}'));
            $this->assertTrue(true, 'escaped pointer ref: percent valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'escaped pointer ref: percent valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'escaped pointer ref: percent valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$defs":{"a":{"type":"integer"},"b":{"$ref":"#\/$defs\/a"},"c":{"$ref":"#\/$defs\/b"}},"$ref":"#\/$defs\/c"}');
        try {
            $schema->validate(json_decode('5'));
            $this->assertTrue(true, 'nested refs: nested ref valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'nested refs: nested ref valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested refs: nested ref valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"a"'));
            $this->assertTrue(false, 'nested refs: nested ref invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'nested refs: nested ref invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'nested refs: nested ref invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$defs":{"reffed":{"type":"array"}},"properties":{"foo":{"$ref":"#\/$defs\/reffed","maxItems":2}}}');
        try {
            $schema->validate(json_decode('{"foo":[]}'));
            $this->assertTrue(true, 'ref applies alongside sibling keywords: ref valid, maxItems valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'ref applies alongside sibling keywords: ref valid, maxItems valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ref applies alongside sibling keywords: ref valid, maxItems valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":[1,2,3]}'));
            $this->assertTrue(false, 'ref applies alongside sibling keywords: ref valid, maxItems invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'ref applies alongside sibling keywords: ref valid, maxItems invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ref applies alongside sibling keywords: ref valid, maxItems invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"string"}'));
            $this->assertTrue(false, 'ref applies alongside sibling keywords: ref invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'ref applies alongside sibling keywords: ref invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ref applies alongside sibling keywords: ref invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$ref":"https:\/\/json-schema.org\/draft\/2020-12\/schema"}');
        try {
            $schema->validate(json_decode('{"minLength":1}'));
            $this->assertTrue(true, 'remote ref, containing refs itself: remote ref valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'remote ref, containing refs itself: remote ref valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'remote ref, containing refs itself: remote ref valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"minLength":-1}'));
            $this->assertTrue(false, 'remote ref, containing refs itself: remote ref invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'remote ref, containing refs itself: remote ref invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'remote ref, containing refs itself: remote ref invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"$ref":{"type":"string"}}}');
        try {
            $schema->validate(json_decode('{"$ref":"a"}'));
            $this->assertTrue(true, 'property named $ref that is not a reference: property named $ref valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'property named $ref that is not a reference: property named $ref valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'property named $ref that is not a reference: property named $ref valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$ref":2}'));
            $this->assertTrue(false, 'property named $ref that is not a reference: property named $ref invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'property named $ref that is not a reference: property named $ref invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'property named $ref that is not a reference: property named $ref invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"$ref":{"$ref":"#\/$defs\/is-string"}},"$defs":{"is-string":{"type":"string"}}}');
        try {
            $schema->validate(json_decode('{"$ref":"a"}'));
            $this->assertTrue(true, 'property named $ref, containing an actual $ref: property named $ref valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'property named $ref, containing an actual $ref: property named $ref valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'property named $ref, containing an actual $ref: property named $ref valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$ref":2}'));
            $this->assertTrue(false, 'property named $ref, containing an actual $ref: property named $ref invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'property named $ref, containing an actual $ref: property named $ref invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'property named $ref, containing an actual $ref: property named $ref invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$ref":"#\/$defs\/bool","$defs":{"bool":true}}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, '$ref to boolean schema true: any value is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, '$ref to boolean schema true: any value is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$ref to boolean schema true: any value is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$ref":"#\/$defs\/bool","$defs":{"bool":false}}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(false, '$ref to boolean schema false: any value is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, '$ref to boolean schema false: any value is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$ref to boolean schema false: any value is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/localhost:1234\/tree","description":"tree of nodes","type":"object","properties":{"meta":{"type":"string"},"nodes":{"type":"array","items":{"$ref":"node"}}},"required":["meta","nodes"],"$defs":{"node":{"$id":"http:\/\/localhost:1234\/node","description":"node","type":"object","properties":{"value":{"type":"number"},"subtree":{"$ref":"tree"}},"required":["value"]}}}');
        try {
            $schema->validate(json_decode('{"meta":"root","nodes":[{"value":1,"subtree":{"meta":"child","nodes":[{"value":1.1},{"value":1.2}]}},{"value":2,"subtree":{"meta":"child","nodes":[{"value":2.1},{"value":2.2}]}}]}'));
            $this->assertTrue(true, 'Recursive references between schemas: valid tree. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'Recursive references between schemas: valid tree. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Recursive references between schemas: valid tree. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"meta":"root","nodes":[{"value":1,"subtree":{"meta":"child","nodes":[{"value":"string is invalid"},{"value":1.2}]}},{"value":2,"subtree":{"meta":"child","nodes":[{"value":2.1},{"value":2.2}]}}]}'));
            $this->assertTrue(false, 'Recursive references between schemas: invalid tree. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Recursive references between schemas: invalid tree. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Recursive references between schemas: invalid tree. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"properties":{"foo\"bar":{"$ref":"#\/$defs\/foo%22bar"}},"$defs":{"foo\"bar":{"type":"number"}}}');
        try {
            $schema->validate(json_decode('{"foo\"bar":1}'));
            $this->assertTrue(true, 'refs with quote: object with numbers is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'refs with quote: object with numbers is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'refs with quote: object with numbers is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo\"bar":"1"}'));
            $this->assertTrue(false, 'refs with quote: object with strings is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'refs with quote: object with strings is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'refs with quote: object with strings is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$defs":{"A":{"unevaluatedProperties":false}},"properties":{"prop1":{"type":"string"}},"$ref":"#\/$defs\/A"}');
        try {
            $schema->validate(json_decode('{"prop1":"match"}'));
            $this->assertTrue(false, 'ref creates new scope when adjacent to keywords: referenced subschema doesn\'t see annotations from properties. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'ref creates new scope when adjacent to keywords: referenced subschema doesn\'t see annotations from properties. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'ref creates new scope when adjacent to keywords: referenced subschema doesn\'t see annotations from properties. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$defs":{"a_string":{"type":"string"}},"enum":[{"$ref":"#\/$defs\/a_string"}]}');
        try {
            $schema->validate(json_decode('"this is a string"'));
            $this->assertTrue(false, 'naive replacement of $ref with its destination is not correct: do not evaluate the $ref inside the enum, matching any string. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'naive replacement of $ref with its destination is not correct: do not evaluate the $ref inside the enum, matching any string. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'naive replacement of $ref with its destination is not correct: do not evaluate the $ref inside the enum, matching any string. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"type":"string"}'));
            $this->assertTrue(false, 'naive replacement of $ref with its destination is not correct: do not evaluate the $ref inside the enum, definition exact match. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'naive replacement of $ref with its destination is not correct: do not evaluate the $ref inside the enum, definition exact match. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'naive replacement of $ref with its destination is not correct: do not evaluate the $ref inside the enum, definition exact match. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"$ref":"#\/$defs\/a_string"}'));
            $this->assertTrue(true, 'naive replacement of $ref with its destination is not correct: match the enum exactly. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'naive replacement of $ref with its destination is not correct: match the enum exactly. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'naive replacement of $ref with its destination is not correct: match the enum exactly. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/example.com\/schema-relative-uri-defs1.json","properties":{"foo":{"$id":"schema-relative-uri-defs2.json","$defs":{"inner":{"properties":{"bar":{"type":"string"}}}},"$ref":"#\/$defs\/inner"}},"$ref":"schema-relative-uri-defs2.json"}');
        try {
            $schema->validate(json_decode('{"foo":{"bar":1},"bar":"a"}'));
            $this->assertTrue(false, 'refs with relative uris and defs: invalid on inner field. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'refs with relative uris and defs: invalid on inner field. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'refs with relative uris and defs: invalid on inner field. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":{"bar":"a"},"bar":1}'));
            $this->assertTrue(false, 'refs with relative uris and defs: invalid on outer field. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'refs with relative uris and defs: invalid on outer field. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'refs with relative uris and defs: invalid on outer field. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":{"bar":"a"},"bar":"a"}'));
            $this->assertTrue(true, 'refs with relative uris and defs: valid on both fields. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'refs with relative uris and defs: valid on both fields. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'refs with relative uris and defs: valid on both fields. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/example.com\/schema-refs-absolute-uris-defs1.json","properties":{"foo":{"$id":"http:\/\/example.com\/schema-refs-absolute-uris-defs2.json","$defs":{"inner":{"properties":{"bar":{"type":"string"}}}},"$ref":"#\/$defs\/inner"}},"$ref":"schema-refs-absolute-uris-defs2.json"}');
        try {
            $schema->validate(json_decode('{"foo":{"bar":1},"bar":"a"}'));
            $this->assertTrue(false, 'relative refs with absolute uris and defs: invalid on inner field. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'relative refs with absolute uris and defs: invalid on inner field. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'relative refs with absolute uris and defs: invalid on inner field. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":{"bar":"a"},"bar":1}'));
            $this->assertTrue(false, 'relative refs with absolute uris and defs: invalid on outer field. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'relative refs with absolute uris and defs: invalid on outer field. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'relative refs with absolute uris and defs: invalid on outer field. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":{"bar":"a"},"bar":"a"}'));
            $this->assertTrue(true, 'relative refs with absolute uris and defs: valid on both fields. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'relative refs with absolute uris and defs: valid on both fields. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'relative refs with absolute uris and defs: valid on both fields. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/example.com\/a.json","$defs":{"x":{"$id":"http:\/\/example.com\/b\/c.json","not":{"$defs":{"y":{"$id":"d.json","type":"number"}}}}},"allOf":[{"$ref":"http:\/\/example.com\/b\/d.json"}]}');
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, '$id must be resolved against nearest parent, not just immediate parent: number should pass. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, '$id must be resolved against nearest parent, not just immediate parent: number should pass. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$id must be resolved against nearest parent, not just immediate parent: number should pass. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"a"'));
            $this->assertTrue(false, '$id must be resolved against nearest parent, not just immediate parent: non-number should fail. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, '$id must be resolved against nearest parent, not just immediate parent: non-number should fail. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, '$id must be resolved against nearest parent, not just immediate parent: non-number should fail. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testMaximum(): void
    {
        $schema = Schema::fromJson('{"maximum":3}');
        try {
            $schema->validate(json_decode('2.6'));
            $this->assertTrue(true, 'maximum validation: below the maximum is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maximum validation: below the maximum is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maximum validation: below the maximum is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('3'));
            $this->assertTrue(true, 'maximum validation: boundary point is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maximum validation: boundary point is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maximum validation: boundary point is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('3.5'));
            $this->assertTrue(false, 'maximum validation: above the maximum is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maximum validation: above the maximum is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maximum validation: above the maximum is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"x"'));
            $this->assertTrue(true, 'maximum validation: ignores non-numbers. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maximum validation: ignores non-numbers. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maximum validation: ignores non-numbers. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"maximum":300}');
        try {
            $schema->validate(json_decode('299.97'));
            $this->assertTrue(true, 'maximum validation with unsigned integer: below the maximum is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maximum validation with unsigned integer: below the maximum is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maximum validation with unsigned integer: below the maximum is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('300'));
            $this->assertTrue(true, 'maximum validation with unsigned integer: boundary point integer is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maximum validation with unsigned integer: boundary point integer is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maximum validation with unsigned integer: boundary point integer is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('300'));
            $this->assertTrue(true, 'maximum validation with unsigned integer: boundary point float is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maximum validation with unsigned integer: boundary point float is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maximum validation with unsigned integer: boundary point float is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('300.5'));
            $this->assertTrue(false, 'maximum validation with unsigned integer: above the maximum is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maximum validation with unsigned integer: above the maximum is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maximum validation with unsigned integer: above the maximum is invalid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testMinLength(): void
    {
        $schema = Schema::fromJson('{"minLength":2}');
        try {
            $schema->validate(json_decode('"foo"'));
            $this->assertTrue(true, 'minLength validation: longer is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minLength validation: longer is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minLength validation: longer is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"fo"'));
            $this->assertTrue(true, 'minLength validation: exact length is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minLength validation: exact length is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minLength validation: exact length is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"f"'));
            $this->assertTrue(false, 'minLength validation: too short is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minLength validation: too short is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minLength validation: too short is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('1'));
            $this->assertTrue(true, 'minLength validation: ignores non-strings. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'minLength validation: ignores non-strings. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minLength validation: ignores non-strings. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"\ud83d\udca9"'));
            $this->assertTrue(false, 'minLength validation: one supplementary Unicode code point is not long enough. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'minLength validation: one supplementary Unicode code point is not long enough. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'minLength validation: one supplementary Unicode code point is not long enough. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testMaxItems(): void
    {
        $schema = Schema::fromJson('{"maxItems":2}');
        try {
            $schema->validate(json_decode('[1]'));
            $this->assertTrue(true, 'maxItems validation: shorter is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxItems validation: shorter is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxItems validation: shorter is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2]'));
            $this->assertTrue(true, 'maxItems validation: exact length is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxItems validation: exact length is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxItems validation: exact length is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2,3]'));
            $this->assertTrue(false, 'maxItems validation: too long is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'maxItems validation: too long is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxItems validation: too long is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"foobar"'));
            $this->assertTrue(true, 'maxItems validation: ignores non-arrays. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'maxItems validation: ignores non-arrays. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'maxItems validation: ignores non-arrays. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testContains(): void
    {
        $schema = Schema::fromJson('{"contains":{"minimum":5}}');
        try {
            $schema->validate(json_decode('[3,4,5]'));
            $this->assertTrue(true, 'contains keyword validation: array with item matching schema (5) is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'contains keyword validation: array with item matching schema (5) is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword validation: array with item matching schema (5) is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[3,4,6]'));
            $this->assertTrue(true, 'contains keyword validation: array with item matching schema (6) is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'contains keyword validation: array with item matching schema (6) is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword validation: array with item matching schema (6) is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[3,4,5,6]'));
            $this->assertTrue(true, 'contains keyword validation: array with two items matching schema (5, 6) is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'contains keyword validation: array with two items matching schema (5, 6) is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword validation: array with two items matching schema (5, 6) is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[2,3,4]'));
            $this->assertTrue(false, 'contains keyword validation: array without items matching schema is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'contains keyword validation: array without items matching schema is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword validation: array without items matching schema is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'contains keyword validation: empty array is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'contains keyword validation: empty array is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword validation: empty array is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{}'));
            $this->assertTrue(true, 'contains keyword validation: not array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'contains keyword validation: not array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword validation: not array is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contains":{"const":5}}');
        try {
            $schema->validate(json_decode('[3,4,5]'));
            $this->assertTrue(true, 'contains keyword with const keyword: array with item 5 is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'contains keyword with const keyword: array with item 5 is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword with const keyword: array with item 5 is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[3,4,5,5]'));
            $this->assertTrue(true, 'contains keyword with const keyword: array with two items 5 is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'contains keyword with const keyword: array with two items 5 is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword with const keyword: array with two items 5 is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,2,3,4]'));
            $this->assertTrue(false, 'contains keyword with const keyword: array without item 5 is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'contains keyword with const keyword: array without item 5 is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword with const keyword: array without item 5 is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contains":true}');
        try {
            $schema->validate(json_decode('["foo"]'));
            $this->assertTrue(true, 'contains keyword with boolean schema true: any non-empty array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'contains keyword with boolean schema true: any non-empty array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword with boolean schema true: any non-empty array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'contains keyword with boolean schema true: empty array is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'contains keyword with boolean schema true: empty array is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword with boolean schema true: empty array is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contains":false}');
        try {
            $schema->validate(json_decode('["foo"]'));
            $this->assertTrue(false, 'contains keyword with boolean schema false: any non-empty array is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'contains keyword with boolean schema false: any non-empty array is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword with boolean schema false: any non-empty array is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'contains keyword with boolean schema false: empty array is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'contains keyword with boolean schema false: empty array is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword with boolean schema false: empty array is invalid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('"contains does not apply to strings"'));
            $this->assertTrue(true, 'contains keyword with boolean schema false: non-arrays are valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'contains keyword with boolean schema false: non-arrays are valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains keyword with boolean schema false: non-arrays are valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"items":{"multipleOf":2},"contains":{"multipleOf":3}}');
        try {
            $schema->validate(json_decode('[2,4,8]'));
            $this->assertTrue(false, 'items + contains: matches items, does not match contains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'items + contains: matches items, does not match contains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items + contains: matches items, does not match contains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[3,6,9]'));
            $this->assertTrue(false, 'items + contains: does not match items, matches contains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'items + contains: does not match items, matches contains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items + contains: does not match items, matches contains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[6,12]'));
            $this->assertTrue(true, 'items + contains: matches both items and contains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'items + contains: matches both items and contains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items + contains: matches both items and contains. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[1,5]'));
            $this->assertTrue(false, 'items + contains: matches neither items nor contains. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'items + contains: matches neither items nor contains. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'items + contains: matches neither items nor contains. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"contains":{"if":false,"else":true}}');
        try {
            $schema->validate(json_decode('["foo"]'));
            $this->assertTrue(true, 'contains with false if subschema: any non-empty array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'contains with false if subschema: any non-empty array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains with false if subschema: any non-empty array is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('[]'));
            $this->assertTrue(false, 'contains with false if subschema: empty array is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'contains with false if subschema: empty array is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'contains with false if subschema: empty array is invalid. Failed with: ' . $e->getMessage());
            }
        }
    }
    public function testDynamicRef(): void
    {
        $schema = Schema::fromJson('{"$id":"https:\/\/test.json-schema.org\/dynamicRef-dynamicAnchor-same-schema\/root","type":"array","items":{"$dynamicRef":"#items"},"$defs":{"foo":{"$dynamicAnchor":"items","type":"string"}}}');
        try {
            $schema->validate(json_decode('["foo","bar"]'));
            $this->assertTrue(true, 'A $dynamicRef to a $dynamicAnchor in the same schema resource should behave like a normal $ref to an $anchor: An array of strings is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'A $dynamicRef to a $dynamicAnchor in the same schema resource should behave like a normal $ref to an $anchor: An array of strings is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $dynamicRef to a $dynamicAnchor in the same schema resource should behave like a normal $ref to an $anchor: An array of strings is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo",42]'));
            $this->assertTrue(false, 'A $dynamicRef to a $dynamicAnchor in the same schema resource should behave like a normal $ref to an $anchor: An array containing non-strings is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'A $dynamicRef to a $dynamicAnchor in the same schema resource should behave like a normal $ref to an $anchor: An array containing non-strings is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $dynamicRef to a $dynamicAnchor in the same schema resource should behave like a normal $ref to an $anchor: An array containing non-strings is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"https:\/\/test.json-schema.org\/dynamicRef-anchor-same-schema\/root","type":"array","items":{"$dynamicRef":"#items"},"$defs":{"foo":{"$anchor":"items","type":"string"}}}');
        try {
            $schema->validate(json_decode('["foo","bar"]'));
            $this->assertTrue(true, 'A $dynamicRef to an $anchor in the same schema resource should behave like a normal $ref to an $anchor: An array of strings is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'A $dynamicRef to an $anchor in the same schema resource should behave like a normal $ref to an $anchor: An array of strings is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $dynamicRef to an $anchor in the same schema resource should behave like a normal $ref to an $anchor: An array of strings is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo",42]'));
            $this->assertTrue(false, 'A $dynamicRef to an $anchor in the same schema resource should behave like a normal $ref to an $anchor: An array containing non-strings is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'A $dynamicRef to an $anchor in the same schema resource should behave like a normal $ref to an $anchor: An array containing non-strings is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $dynamicRef to an $anchor in the same schema resource should behave like a normal $ref to an $anchor: An array containing non-strings is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"https:\/\/test.json-schema.org\/ref-dynamicAnchor-same-schema\/root","type":"array","items":{"$ref":"#items"},"$defs":{"foo":{"$dynamicAnchor":"items","type":"string"}}}');
        try {
            $schema->validate(json_decode('["foo","bar"]'));
            $this->assertTrue(true, 'A $ref to a $dynamicAnchor in the same schema resource should behave like a normal $ref to an $anchor: An array of strings is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'A $ref to a $dynamicAnchor in the same schema resource should behave like a normal $ref to an $anchor: An array of strings is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $ref to a $dynamicAnchor in the same schema resource should behave like a normal $ref to an $anchor: An array of strings is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo",42]'));
            $this->assertTrue(false, 'A $ref to a $dynamicAnchor in the same schema resource should behave like a normal $ref to an $anchor: An array containing non-strings is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'A $ref to a $dynamicAnchor in the same schema resource should behave like a normal $ref to an $anchor: An array containing non-strings is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $ref to a $dynamicAnchor in the same schema resource should behave like a normal $ref to an $anchor: An array containing non-strings is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"https:\/\/test.json-schema.org\/typical-dynamic-resolution\/root","$ref":"list","$defs":{"foo":{"$dynamicAnchor":"items","type":"string"},"list":{"$id":"list","type":"array","items":{"$dynamicRef":"#items"},"$defs":{"items":{"$comment":"This is only needed to satisfy the bookending requirement","$dynamicAnchor":"items"}}}}}');
        try {
            $schema->validate(json_decode('["foo","bar"]'));
            $this->assertTrue(true, 'A $dynamicRef should resolve to the first $dynamicAnchor still in scope that is encountered when the schema is evaluated: An array of strings is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'A $dynamicRef should resolve to the first $dynamicAnchor still in scope that is encountered when the schema is evaluated: An array of strings is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $dynamicRef should resolve to the first $dynamicAnchor still in scope that is encountered when the schema is evaluated: An array of strings is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo",42]'));
            $this->assertTrue(false, 'A $dynamicRef should resolve to the first $dynamicAnchor still in scope that is encountered when the schema is evaluated: An array containing non-strings is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'A $dynamicRef should resolve to the first $dynamicAnchor still in scope that is encountered when the schema is evaluated: An array containing non-strings is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $dynamicRef should resolve to the first $dynamicAnchor still in scope that is encountered when the schema is evaluated: An array containing non-strings is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"https:\/\/test.json-schema.org\/dynamic-resolution-with-intermediate-scopes\/root","$ref":"intermediate-scope","$defs":{"foo":{"$dynamicAnchor":"items","type":"string"},"intermediate-scope":{"$id":"intermediate-scope","$ref":"list"},"list":{"$id":"list","type":"array","items":{"$dynamicRef":"#items"},"$defs":{"items":{"$comment":"This is only needed to satisfy the bookending requirement","$dynamicAnchor":"items"}}}}}');
        try {
            $schema->validate(json_decode('["foo","bar"]'));
            $this->assertTrue(true, 'A $dynamicRef with intermediate scopes that don\'t include a matching $dynamicAnchor should not affect dynamic scope resolution: An array of strings is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'A $dynamicRef with intermediate scopes that don\'t include a matching $dynamicAnchor should not affect dynamic scope resolution: An array of strings is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $dynamicRef with intermediate scopes that don\'t include a matching $dynamicAnchor should not affect dynamic scope resolution: An array of strings is valid. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('["foo",42]'));
            $this->assertTrue(false, 'A $dynamicRef with intermediate scopes that don\'t include a matching $dynamicAnchor should not affect dynamic scope resolution: An array containing non-strings is invalid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'A $dynamicRef with intermediate scopes that don\'t include a matching $dynamicAnchor should not affect dynamic scope resolution: An array containing non-strings is invalid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $dynamicRef with intermediate scopes that don\'t include a matching $dynamicAnchor should not affect dynamic scope resolution: An array containing non-strings is invalid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"https:\/\/test.json-schema.org\/dynamic-resolution-ignores-anchors\/root","$ref":"list","$defs":{"foo":{"$anchor":"items","type":"string"},"list":{"$id":"list","type":"array","items":{"$dynamicRef":"#items"},"$defs":{"items":{"$comment":"This is only needed to satisfy the bookending requirement","$dynamicAnchor":"items"}}}}}');
        try {
            $schema->validate(json_decode('["foo",42]'));
            $this->assertTrue(true, 'An $anchor with the same name as a $dynamicAnchor should not be used for dynamic scope resolution: Any array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'An $anchor with the same name as a $dynamicAnchor should not be used for dynamic scope resolution: Any array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'An $anchor with the same name as a $dynamicAnchor should not be used for dynamic scope resolution: Any array is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"https:\/\/test.json-schema.org\/dynamic-resolution-without-bookend\/root","$ref":"list","$defs":{"foo":{"$dynamicAnchor":"items","type":"string"},"list":{"$id":"list","type":"array","items":{"$dynamicRef":"#items"},"$defs":{"items":{"$comment":"This is only needed to give the reference somewhere to resolve to when it behaves like $ref","$anchor":"items"}}}}}');
        try {
            $schema->validate(json_decode('["foo",42]'));
            $this->assertTrue(true, 'A $dynamicRef without a matching $dynamicAnchor in the same schema resource should behave like a normal $ref to $anchor: Any array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'A $dynamicRef without a matching $dynamicAnchor in the same schema resource should behave like a normal $ref to $anchor: Any array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $dynamicRef without a matching $dynamicAnchor in the same schema resource should behave like a normal $ref to $anchor: Any array is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"https:\/\/test.json-schema.org\/unmatched-dynamic-anchor\/root","$ref":"list","$defs":{"foo":{"$dynamicAnchor":"items","type":"string"},"list":{"$id":"list","type":"array","items":{"$dynamicRef":"#items"},"$defs":{"items":{"$comment":"This is only needed to give the reference somewhere to resolve to when it behaves like $ref","$anchor":"items","$dynamicAnchor":"foo"}}}}}');
        try {
            $schema->validate(json_decode('["foo",42]'));
            $this->assertTrue(true, 'A $dynamicRef with a non-matching $dynamicAnchor in the same schema resource should behave like a normal $ref to $anchor: Any array is valid. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'A $dynamicRef with a non-matching $dynamicAnchor in the same schema resource should behave like a normal $ref to $anchor: Any array is valid. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $dynamicRef with a non-matching $dynamicAnchor in the same schema resource should behave like a normal $ref to $anchor: Any array is valid. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"https:\/\/test.json-schema.org\/relative-dynamic-reference\/root","$dynamicAnchor":"meta","type":"object","properties":{"foo":{"const":"pass"}},"$ref":"extended","$defs":{"extended":{"$id":"extended","$dynamicAnchor":"meta","type":"object","properties":{"bar":{"$ref":"bar"}}},"bar":{"$id":"bar","type":"object","properties":{"baz":{"$dynamicRef":"extended#meta"}}}}}');
        try {
            $schema->validate(json_decode('{"foo":"pass","bar":{"baz":{"foo":"pass"}}}'));
            $this->assertTrue(true, 'A $dynamicRef that initially resolves to a schema with a matching $dynamicAnchor should resolve to the first $dynamicAnchor in the dynamic scope: The recursive part is valid against the root. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'A $dynamicRef that initially resolves to a schema with a matching $dynamicAnchor should resolve to the first $dynamicAnchor in the dynamic scope: The recursive part is valid against the root. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $dynamicRef that initially resolves to a schema with a matching $dynamicAnchor should resolve to the first $dynamicAnchor in the dynamic scope: The recursive part is valid against the root. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"foo":"pass","bar":{"baz":{"foo":"fail"}}}'));
            $this->assertTrue(false, 'A $dynamicRef that initially resolves to a schema with a matching $dynamicAnchor should resolve to the first $dynamicAnchor in the dynamic scope: The recursive part is not valid against the root. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'A $dynamicRef that initially resolves to a schema with a matching $dynamicAnchor should resolve to the first $dynamicAnchor in the dynamic scope: The recursive part is not valid against the root. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $dynamicRef that initially resolves to a schema with a matching $dynamicAnchor should resolve to the first $dynamicAnchor in the dynamic scope: The recursive part is not valid against the root. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"https:\/\/test.json-schema.org\/relative-dynamic-reference-without-bookend\/root","$dynamicAnchor":"meta","type":"object","properties":{"foo":{"const":"pass"}},"$ref":"extended","$defs":{"extended":{"$id":"extended","$anchor":"meta","type":"object","properties":{"bar":{"$ref":"bar"}}},"bar":{"$id":"bar","type":"object","properties":{"baz":{"$dynamicRef":"extended#meta"}}}}}');
        try {
            $schema->validate(json_decode('{"foo":"pass","bar":{"baz":{"foo":"fail"}}}'));
            $this->assertTrue(true, 'A $dynamicRef that initially resolves to a schema without a matching $dynamicAnchor should behave like a normal $ref to $anchor: The recursive part doesn\'t need to validate against the root. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'A $dynamicRef that initially resolves to a schema without a matching $dynamicAnchor should behave like a normal $ref to $anchor: The recursive part doesn\'t need to validate against the root. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'A $dynamicRef that initially resolves to a schema without a matching $dynamicAnchor should behave like a normal $ref to $anchor: The recursive part doesn\'t need to validate against the root. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"https:\/\/test.json-schema.org\/dynamic-ref-with-multiple-paths\/main","$defs":{"inner":{"$id":"inner","$dynamicAnchor":"foo","title":"inner","additionalProperties":{"$dynamicRef":"#foo"}}},"if":{"propertyNames":{"pattern":"^[a-m]"}},"then":{"title":"any type of node","$id":"anyLeafNode","$dynamicAnchor":"foo","$ref":"inner"},"else":{"title":"integer node","$id":"integerNode","$dynamicAnchor":"foo","type":["object","integer"],"$ref":"inner"}}');
        try {
            $schema->validate(json_decode('{"alpha":1.1}'));
            $this->assertTrue(true, 'multiple dynamic paths to the $dynamicRef keyword: recurse to anyLeafNode - floats are allowed. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'multiple dynamic paths to the $dynamicRef keyword: recurse to anyLeafNode - floats are allowed. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple dynamic paths to the $dynamicRef keyword: recurse to anyLeafNode - floats are allowed. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"november":1.1}'));
            $this->assertTrue(false, 'multiple dynamic paths to the $dynamicRef keyword: recurse to integerNode - floats are not allowed. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'multiple dynamic paths to the $dynamicRef keyword: recurse to integerNode - floats are not allowed. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'multiple dynamic paths to the $dynamicRef keyword: recurse to integerNode - floats are not allowed. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"https:\/\/test.json-schema.org\/dynamic-ref-leaving-dynamic-scope\/main","if":{"$id":"first_scope","$defs":{"thingy":{"$comment":"this is first_scope#thingy","$dynamicAnchor":"thingy","type":"number"}}},"then":{"$id":"second_scope","$ref":"start","$defs":{"thingy":{"$comment":"this is second_scope#thingy, the final destination of the $dynamicRef","$dynamicAnchor":"thingy","type":"null"}}},"$defs":{"start":{"$comment":"this is the landing spot from $ref","$id":"start","$dynamicRef":"inner_scope#thingy"},"thingy":{"$comment":"this is the first stop for the $dynamicRef","$id":"inner_scope","$dynamicAnchor":"thingy","type":"string"}}}');
        try {
            $schema->validate(json_decode('"a string"'));
            $this->assertTrue(false, 'after leaving a dynamic scope, it should not be used by a $dynamicRef: string matches /$defs/thingy, but the $dynamicRef does not stop here. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'after leaving a dynamic scope, it should not be used by a $dynamicRef: string matches /$defs/thingy, but the $dynamicRef does not stop here. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'after leaving a dynamic scope, it should not be used by a $dynamicRef: string matches /$defs/thingy, but the $dynamicRef does not stop here. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('42'));
            $this->assertTrue(false, 'after leaving a dynamic scope, it should not be used by a $dynamicRef: first_scope is not in dynamic scope for the $dynamicRef. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'after leaving a dynamic scope, it should not be used by a $dynamicRef: first_scope is not in dynamic scope for the $dynamicRef. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'after leaving a dynamic scope, it should not be used by a $dynamicRef: first_scope is not in dynamic scope for the $dynamicRef. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('null'));
            $this->assertTrue(true, 'after leaving a dynamic scope, it should not be used by a $dynamicRef: /then/$defs/thingy is the final stop for the $dynamicRef. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'after leaving a dynamic scope, it should not be used by a $dynamicRef: /then/$defs/thingy is the final stop for the $dynamicRef. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'after leaving a dynamic scope, it should not be used by a $dynamicRef: /then/$defs/thingy is the final stop for the $dynamicRef. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/localhost:1234\/strict-tree.json","$dynamicAnchor":"node","$ref":"tree.json","unevaluatedProperties":false}');
        try {
            $schema->validate(json_decode('{"children":[{"daat":1}]}'));
            $this->assertTrue(false, 'strict-tree schema, guards against misspelled properties: instance with misspelled field. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'strict-tree schema, guards against misspelled properties: instance with misspelled field. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'strict-tree schema, guards against misspelled properties: instance with misspelled field. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"children":[{"data":1}]}'));
            $this->assertTrue(true, 'strict-tree schema, guards against misspelled properties: instance with correct field. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'strict-tree schema, guards against misspelled properties: instance with correct field. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'strict-tree schema, guards against misspelled properties: instance with correct field. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/localhost:1234\/strict-extendible.json","$ref":"extendible-dynamic-ref.json","$defs":{"elements":{"$dynamicAnchor":"elements","properties":{"a":true},"required":["a"],"additionalProperties":false}}}');
        try {
            $schema->validate(json_decode('{"a":true}'));
            $this->assertTrue(false, 'tests for implementation dynamic anchor and reference link: incorrect parent schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'tests for implementation dynamic anchor and reference link: incorrect parent schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'tests for implementation dynamic anchor and reference link: incorrect parent schema. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"elements":[{"b":1}]}'));
            $this->assertTrue(false, 'tests for implementation dynamic anchor and reference link: incorrect extended schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'tests for implementation dynamic anchor and reference link: incorrect extended schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'tests for implementation dynamic anchor and reference link: incorrect extended schema. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"elements":[{"a":1}]}'));
            $this->assertTrue(true, 'tests for implementation dynamic anchor and reference link: correct extended schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'tests for implementation dynamic anchor and reference link: correct extended schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'tests for implementation dynamic anchor and reference link: correct extended schema. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/localhost:1234\/strict-extendible-allof-defs-first.json","allOf":[{"$ref":"extendible-dynamic-ref.json"},{"$defs":{"elements":{"$dynamicAnchor":"elements","properties":{"a":true},"required":["a"],"additionalProperties":false}}}]}');
        try {
            $schema->validate(json_decode('{"a":true}'));
            $this->assertTrue(false, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: incorrect parent schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: incorrect parent schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: incorrect parent schema. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"elements":[{"b":1}]}'));
            $this->assertTrue(false, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: incorrect extended schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: incorrect extended schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: incorrect extended schema. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"elements":[{"a":1}]}'));
            $this->assertTrue(true, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: correct extended schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: correct extended schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: correct extended schema. Failed with: ' . $e->getMessage());
            }
        }
        $schema = Schema::fromJson('{"$id":"http:\/\/localhost:1234\/strict-extendible-allof-ref-first.json","allOf":[{"$defs":{"elements":{"$dynamicAnchor":"elements","properties":{"a":true},"required":["a"],"additionalProperties":false}}},{"$ref":"extendible-dynamic-ref.json"}]}');
        try {
            $schema->validate(json_decode('{"a":true}'));
            $this->assertTrue(false, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: incorrect parent schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: incorrect parent schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: incorrect parent schema. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"elements":[{"b":1}]}'));
            $this->assertTrue(false, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: incorrect extended schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(true, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: incorrect extended schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: incorrect extended schema. Failed with: ' . $e->getMessage());
            }
        }
        try {
            $schema->validate(json_decode('{"elements":[{"a":1}]}'));
            $this->assertTrue(true, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: correct extended schema. Should have failed');
        } catch (Throwable $e) {
            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                $this->assertTrue(false, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: correct extended schema. Failed with: ' . $e->getMessage());
            } else {
                $this->assertTrue(false, 'Tests for implementation dynamic anchor and reference link. Reference should be independent of any possible ordering.: correct extended schema. Failed with: ' . $e->getMessage());
            }
        }
    }
}
