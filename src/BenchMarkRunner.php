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

        $benchMarks = [];
        foreach (SampleProvider::getSamplePaths() as $fileName) {
            foreach ($libraryFQNs as $libraryFQN) {
                echo 'Running benchmark for ' . $libraryFQN::getIdentifier() . ' on ' . $fileName . '...' . PHP_EOL;
                $benchmark = (new BenchMark())
                    ->__invoke(dirname(__DIR__) . $fileName, $libraryFQN, $this->getUserPasswordForFile($fileName), $this->getOwnerPasswordForFile($fileName));

                $benchMarks[$libraryFQN::getIdentifier()][] = [
                    'filename' => $fileName,
                    'ms' => $benchmark->msTaken,
                    'bytes' => $benchmark->bytesMemoryConsumed,
                    'pass' => $benchmark->exception === null,
                    'exception' => $benchmark->exception !== null ? substr($benchmark->exception->getMessage(), 0, 200) : null,
                ];
            }
        }

        file_put_contents(dirname(__DIR__) . '/' . self::OUTPUT_FILE, json_encode($benchMarks, JSON_PRETTY_PRINT));

        $totalData = [];
        foreach ($benchMarks as $libraryIdentifier => $data) {
            $successfullyParsedFiles = array_filter($data, fn(array $test): bool => $test['pass']);
            $totalData[$libraryIdentifier] = [
                'ms' => array_sum(array_column($successfullyParsedFiles, 'ms')) / count($successfullyParsedFiles),
                'bytes' => array_sum(array_column($successfullyParsedFiles, 'bytes')) / count($successfullyParsedFiles),
                'pass' => count($successfullyParsedFiles) / count($data) * 100,
            ];
        }

        file_put_contents(dirname(__DIR__) . '/' . self::OUTPUT_FILE_TOTAL, json_encode($totalData, JSON_PRETTY_PRINT));

        return 0;
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
