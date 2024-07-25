<?php

declare(strict_types=1);

namespace Giann\Schematics;

use Giann\Schematics\Exception\InvalidSchemaException;
use Giann\Trunk\Trunk;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Parser;
use PhpParser\ParserFactory;

class GeneratorHelper
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = (new ParserFactory)->createForNewestSupportedVersion();
    }

    public function trueExpr(): Expr
    {
        return new ConstFetch(new Name('true'));
    }

    public function falseExpr(): Expr
    {
        return new ConstFetch(new Name('false'));
    }

    public function boolExpr(bool $value): Expr
    {
        return $value ? $this->trueExpr() : $this->falseExpr();
    }

    /**
     * @param mixed $value
     * @throws InvalidSchemaException
     * @return Expr
     */
    public function phpValueToExpr(mixed $value): Expr
    {
        $parsed = $this->parser->parse(
            '<?php '
                . var_export($value, true)
                . ';'
        ) ?? [];

        if (count($parsed) == 1 && $parsed[0] instanceof Expression) {
            /** @var Expression */
            $exprStmt = $parsed[0];
            return $exprStmt->expr;
        }

        throw new InvalidSchemaException();
    }

    public function anyToCamelCase(string $name): string
    {
        return ucfirst(
            str_replace(
                ' ',
                '',
                ucwords(
                    str_replace(
                        ['-', '_'],
                        ' ',
                        $name
                    )
                )
            )
        );
    }

    public function getAt(Trunk $schema, string $path): Trunk
    {
        assert(str_starts_with($path, '#/'));

        $current = $schema;
        foreach (array_slice(explode('/', $path), 1) as $fragment) {
            $current = $current[$fragment];
        }

        return $current;
    }
}
