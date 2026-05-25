<?php declare(strict_types=1);

namespace PrinsFrank\PDFParserBenchmarks\BenchMark\Library;

use Smalot\PdfParser\Parser;

class Smalot implements Library {
    public static function getIdentifier(): string {
        return 'smalot/pdfparser';
    }

    public function getText(string $filePath, ?string $userPassword, ?string $ownerPassword): string {
        return (new Parser())
            ->parseFile($filePath)
            ->getText();
    }
}
