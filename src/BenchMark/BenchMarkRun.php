<?php declare(strict_types=1);

namespace PrinsFrank\PDFParserBenchmarks\BenchMark;

use Throwable;

readonly class BenchMarkRun {
    /**
     * @param list<float>|null $msTaken
     * @param list<float>|null $bytesMemoryConsumed
     */
    public function __construct(
        public ?Throwable $exception,
        public ?array $msTaken,
        public ?array $bytesMemoryConsumed,
    ) {}
}
