<?php declare(strict_types=1);

namespace PrinsFrank\PDFParserBenchmarks\BenchMark\Library;

class LibraryProvider {
    /** @return list<class-string<Library>> */
    public static function FQNs(): array {
        return [
            Smalot::class,
            PrinsFrank::class,
        ];
    }
}
