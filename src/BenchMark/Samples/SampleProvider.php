<?php declare(strict_types=1);

namespace PrinsFrank\PDFParserBenchmarks\BenchMark\Samples;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class SampleProvider {
    private const SAMPLE_PATHS = [
        '/vendor/prinsfrank/pdfparser/tests/Samples/files',
        '/vendor/smalot/pdfparser/samples',
    ];

    /** @return list<string> */
    public static function getSamplePaths(): iterable {
        foreach (self::SAMPLE_PATHS as $samplePath) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__, 3) . $samplePath)) as $fileInfo) {
                if ($fileInfo->getExtension() !== 'pdf' || $fileInfo->getFilename() === 'corrupted.pdf' || $fileInfo->getFilename() === 'Pages-tree-refs.pdf') {
                    continue;
                }

                yield str_replace(dirname(__DIR__, 3), '', $fileInfo->getRealPath());
            }
        }
    }
}
