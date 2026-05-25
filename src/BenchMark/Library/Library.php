<?php declare(strict_types=1);

namespace PrinsFrank\PDFParserBenchmarks\BenchMark\Library;

use Exception;

interface Library {
    public static function getIdentifier(): string;

    /** @throws Exception */
    public function getText(string $filePath, ?string $userPassword, ?string $ownerPassword): string;
}
