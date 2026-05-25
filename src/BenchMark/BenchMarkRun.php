<?php declare(strict_types=1);

namespace PrinsFrank\PDFParserBenchmarks\BenchMark;

use Throwable;

readonly class BenchMarkRun {
    public function __construct(
        public ?Throwable $exception,
        public ?float     $msTaken,
        public ?float     $bytesMemoryConsumed,
    ) {}
}
