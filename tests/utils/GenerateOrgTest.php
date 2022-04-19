<?php

declare(strict_types=1);

echo '<?php

declare(strict_types=1);

use Giann\Schematics\InvalidSchemaValueException;
use Giann\Schematics\NotYetImplementedException;
use Giann\Schematics\Schema;
use PHPUnit\Framework\TestCase;

final class OrgTest extends TestCase
{
';

$dir = new DirectoryIterator(__DIR__ . '/../org/tests/draft2020-12');

/** @var SplFileInfo $fileinfo */
foreach ($dir as $fileinfo) {
    if ($fileinfo->isFile() && $fileinfo->getExtension() === 'json') {

        echo '    public function test' . ucfirst(str_replace('-', '_', $fileinfo->getBasename('.json'))) . '(): void {' . PHP_EOL;

        $tests = json_decode(file_get_contents($fileinfo->getPathName()));

        foreach ($tests as $test) {
            $cases = $test->tests;
            $groupDesc = $test->description;

            echo '        $schema = Schema::fromJson(\'' . str_replace("'", "\\'", json_encode($test->schema)) . '\');' . PHP_EOL;

            foreach ($cases as $case) {
                $message = $groupDesc . ': ' . $case->description;

                echo '        try {' . PHP_EOL;
                echo '            $schema->validate(json_decode(\'' . str_replace("'", "\\'", json_encode($case->data)) . '\'));' . PHP_EOL;
                echo '            $this->assertTrue(' . ($case->valid ? 'true' : 'false') . ', \'' . str_replace("'", "\\'", $message) . '. Should have failed\');' . PHP_EOL;
                echo '        } catch (Throwable $e) {' . PHP_EOL;
                echo '            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {' . PHP_EOL;
                echo '                $this->assertTrue(' . (!$case->valid ? 'true' : 'false') . ', \'' . str_replace("'", "\\'", $message) . '. Failed with: \' . $e->getMessage());' . PHP_EOL;
                echo '            } else {' . PHP_EOL;
                echo '                $this->assertTrue(false, \'' . str_replace("'", "\\'", $message) . '. Failed with: \' . $e->getMessage());' . PHP_EOL;
                echo '            }' . PHP_EOL;
                echo '        }' . PHP_EOL;
            }
        }

        echo '    }' . PHP_EOL;
    }
}

echo '}
';
