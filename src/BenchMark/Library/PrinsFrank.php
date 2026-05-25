<?php declare(strict_types=1);

namespace PrinsFrank\PDFParserBenchmarks\BenchMark\Library;

use PrinsFrank\PdfParser\Document\Security\StandardSecurity;
use PrinsFrank\PdfParser\PdfParser;

class PrinsFrank implements Library {
    public static function getIdentifier(): string {
        return 'prinsfrank/pdfparser';
    }

    public function getText(string $filePath, ?string $userPassword, ?string $ownerPassword): string {
        return (new PdfParser())
            ->parseFile($filePath, security: new StandardSecurity($userPassword, $ownerPassword))
            ->getText();
    }
}
