<?php declare(strict_types=1);

namespace PrinsFrank\PDFParserBenchmarks\BenchMark;

use PrinsFrank\PDFParserBenchmarks\BenchMark\Library\Library;
use Throwable;

class BenchMark {
    private const NR_OF_WARMUP_RUNS = 10;
    private const NR_OF_RUNS = 20;

    /** @param class-string<Library> $libraryFQN */
    public function __invoke(string $filePath, string $libraryFQN, ?string $userPassword, ?string $ownerPassword): BenchMarkRun {
        try {
            (new $libraryFQN())->getText($filePath, $userPassword, $ownerPassword);
        } catch (Throwable $e) {
            return new BenchMarkRun($e, null, null);
        }

        for ($i = 0; $i < self::NR_OF_WARMUP_RUNS; $i++) {
            try {
                (new $libraryFQN())->getText($filePath, $userPassword, $ownerPassword);
            } catch (Throwable) {
            }
        }

        $bytesMemoryConsumedList = $msTakenList = [];
        for ($i = 0; $i < self::NR_OF_RUNS; $i++) {
            gc_collect_cycles();
            gc_mem_caches();

            $memoryStart = memory_get_usage(false);
            $timeStart = hrtime(true);
            $result = (new $libraryFQN())
                ->getText($filePath, $userPassword, $ownerPassword);
            $memoryEnd = memory_get_usage(false);
            $timeEnd = hrtime(true);

            $bytesMemoryConsumedList[] = $memoryEnd - $memoryStart;
            $msTakenList[] = ($timeEnd - $timeStart) / 1_000_000;

            unset($result);
        }

        return new BenchMarkRun(null, $msTakenList, $bytesMemoryConsumedList);
    }
}
