<?php

declare(strict_types=1);

use Giann\Schematics\InvalidSchemaValueException;
use Giann\Schematics\NotYetImplementedException;
use Giann\Schematics\Schema;
use PHPUnit\Framework\TestCase;

/**
 * Run https://github.com/json-schema-org/JSON-Schema-Test-Suite test suite
 */
final class OrgTest extends TestCase
{
    // List of not yet implemented stuff things
    private static array $ignore = [
        // tries to hit localhost to refer to a local file, we should run a local server to serve it
        'vocabulary.json',
    ];

    public function testOrg(): void
    {
        $generalTestCount = 0;
        $generalFailedCount = 0;
        $failed = [];
        $dir = new DirectoryIterator(__DIR__ . '/org/tests/draft2020-12');
        /** @var SplFileInfo $fileinfo */
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isFile() && $fileinfo->getExtension() === 'json') {
                if (in_array($fileinfo->getFilename(), self::$ignore)) {
                    continue;
                }

                $testCount = 0;
                $failedCount = 0;

                $tests = json_decode(file_get_contents($fileinfo->getPathName()));

                foreach ($tests as $test) {
                    $cases = $test->tests;
                    $groupDesc = $test->description;

                    try {
                        $schema = Schema::fromJson($test->schema);
                    } catch (Throwable $e) {
                        $failedCount++;
                        $generalFailedCount++;

                        $failed[$fileinfo->getFilename()] ??= [];
                        $failed[$fileinfo->getFilename()][] = $fileinfo->getFilename() . ' | ' . $groupDesc . ': could not instanciate schematics from its content';

                        echo PHP_EOL . $fileinfo->getFilename() . ' | ' . $groupDesc . ': could not instanciate schematics from its content';

                        // throw $e;
                    }

                    foreach ($cases as $case) {
                        $testCount++;
                        $generalTestCount++;
                        try {
                            $message = $fileinfo->getFilename() . ' | ' . $groupDesc . ': ' . $case->description;

                            $schema->validate($case->data);

                            if (!$case->valid) {
                                $failedCount++;
                                $generalFailedCount++;

                                $failed[$fileinfo->getFilename()] ??= [];
                                $failed[$fileinfo->getFilename()][] = $message;

                                echo PHP_EOL . $message . '. Should have failed.';
                            }
                        } catch (Throwable $e) {
                            if ($e instanceof InvalidSchemaValueException || $e instanceof NotYetImplementedException) {
                                if ($case->valid) {
                                    $failedCount++;
                                    $generalFailedCount++;

                                    $failed[$fileinfo->getFilename()] ??= [];
                                    $failed[$fileinfo->getFilename()][] = $message . '. Failed with: ' . $e->getMessage();

                                    echo PHP_EOL . $message . '.' . PHP_EOL . '    Failed with: ' . $e->getMessage();
                                }
                            } else {
                                $failedCount++;
                                $generalFailedCount++;

                                $failed[$fileinfo->getFilename()] ??= [];
                                $failed[$fileinfo->getFilename()][] = "Something went wrong in " . $message . " -> " . $e->getMessage();

                                echo PHP_EOL . $message . '.' . PHP_EOL . '    Failed with: ' . $e->getMessage();
                            }
                        }
                    }
                }

                echo PHP_EOL . $fileinfo->getFilename() . ': passed ' . ($testCount - $failedCount) . '/' . $testCount . ' tests' . PHP_EOL;
            }
        }

        echo PHP_EOL . PHP_EOL . 'Passed in total ' . ($generalTestCount - $generalFailedCount) . '/' . $generalTestCount . ' tests' . PHP_EOL;

        $this->assertEmpty($failed);
    }
}
