<?php declare(strict_types=1);

namespace PrinsFrank\PDFParserBenchmarks;

use PrinsFrank\PDFParserBenchmarks\BenchMark\Library\LibraryProvider;
use PrinsFrank\PDFParserBenchmarks\BenchMark\BenchMark;
use PrinsFrank\PDFParserBenchmarks\BenchMark\Samples\SampleProvider;

class BenchMarkRunner {
    private const OUTPUT_FILE = 'benchmarks.json';
    private const OUTPUT_FILE_TOTAL = 'benchmarks_total.json';

    public function __invoke(): int {
        $libraryFQNs = LibraryProvider::FQNs();

        $benchMarks = $benchMarksByFile = [];
        foreach (SampleProvider::getSamplePaths() as $fileName) {
            foreach ($libraryFQNs as $libraryFQN) {
                echo 'Running benchmark for ' . $libraryFQN::getIdentifier() . ' on ' . $fileName . '...' . PHP_EOL;
                $benchmark = (new BenchMark())
                    ->__invoke(dirname(__DIR__) . $fileName, $libraryFQN, $this->getUserPasswordForFile($fileName), $this->getOwnerPasswordForFile($fileName));

                $benchmarkData = [
                    'filename' => $fileName,
                    'ms' => $benchmark->msTaken,
                    'bytes' => $benchmark->bytesMemoryConsumed,
                    'pass' => $benchmark->exception === null,
                    'exception' => $benchmark->exception !== null ? substr($benchmark->exception->getMessage(), 0, 200) : null,
                ];
                $benchMarks[$libraryFQN::getIdentifier()][] = $benchmarkData;
                $benchMarksByFile[$fileName][$libraryFQN::getIdentifier()] = $benchmarkData;
            }
        }

        file_put_contents(dirname(__DIR__) . '/public/' . self::OUTPUT_FILE, json_encode($benchMarksByFile, JSON_PRETTY_PRINT));

        $totalData = ['environment' => ['cpu_count' => shell_exec('nproc') ?? 'unknown', 'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status() !== false, 'memory_limit' => ini_get('memory_limit')]];
        foreach ($benchMarks as $libraryIdentifier => $data) {
            $successfullyParsedFiles = array_filter($data, fn(array $test): bool => $test['pass']);

            $msMeans = $bytesMean = [];
            foreach ($successfullyParsedFiles as $successfullyParsedFile) {
                $msMeans[] = $this->getMedian($successfullyParsedFile['ms']);
                $bytesMean[] = $this->getMedian($successfullyParsedFile['bytes']);
            }

            $totalData[$libraryIdentifier] = [
                'ms' => [
                    'p5' => $this->getPercentile($msMeans, 5),
                    'median' => array_sum($msMeans) / count($msMeans),
                    'p95' => $this->getPercentile($msMeans, 95),
                ],
                'bytes' => [
                    'p5' => $this->getPercentile($bytesMean, 5),
                    'median' => array_sum($bytesMean) / count($msMeans),
                    'p95' => $this->getPercentile($bytesMean, 95),
                ],
                'pass' => count($successfullyParsedFiles) / count($data) * 100,
            ];
        }

        file_put_contents(dirname(__DIR__) . '/public/' . self::OUTPUT_FILE_TOTAL, json_encode($totalData, JSON_PRETTY_PRINT));

        return 0;
    }

    /** @param list<float> $values */
    private function getMedian(array $values): float {
        sort($values);
        $nrOfItems = count($values);
        if ($nrOfItems % 2 === 0) {
            return ($values[floor($nrOfItems / 2)] + $values[ceil($nrOfItems / 2)]) / 2;
        }

        return $values[$nrOfItems / 2];
    }

    /** @param list<float> $values */
    private function getPercentile(array $values, int $percentile): float {
        sort($values);
        if (($nrOfItems = count($values)) === 0) {
            return 0.0;
        }

        return $values[(int) round(($percentile / 100) * ($nrOfItems - 1))];
    }

    private function getUserPasswordForFile(string $fileName): ?string {
        return match ($fileName) {
            '/vendor/prinsfrank/pdfparser/tests/Samples/files/gdocs-hello-world-simple-password/file.pdf' => 'user',
            '/vendor/prinsfrank/pdfparser/tests/Samples/files/libreoffice-hello-world-open-password-hello/file.pdf' => 'hello',
            default => null,
        };
    }

    private function getOwnerPasswordForFile(string $fileName): ?string {
        return match ($fileName) {
            '/vendor/prinsfrank/pdfparser/tests/Samples/files/gdocs-hello-world-simple-password/file.pdf' => 'owner',
            '/vendor/prinsfrank/pdfparser/tests/Samples/files/libreoffice-hello-world-open-password-hello/file.pdf' => 'hello',
            default => null,
        };
    }
}
