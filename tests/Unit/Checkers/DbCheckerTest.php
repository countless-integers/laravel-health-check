<?php

declare(strict_types=1);

namespace Tests\Unit\Checkers;

use CountlessIntegers\LaravelHealthCheck\Checkers\DbChecker;
use Tests\AppTestCase;
use Illuminate\Support\Facades\DB;

class DbCheckerTest extends AppTestCase
{
    /**
     * @test
     *
     * The sorcery here is about capturing a randomly assigned values
     * and "passing" them to following exceptions. Assertions are not
     * synchronous here, so we need to call the `checkHealth` method
     * before actually checking argument values.
     */
    public function itWillSucceedIfAKeyCanBeSetAndRetrieved(): void
    {
        $checker = new DbChecker([
            'query' => 'SHOW TABLES;'
        ]);

        DB::shouldReceive('select')
            ->with('SHOW TABLES;')
            ->andReturn([
                'table',
            ]);

        $report = $checker->checkHealth();

        $this->assertTrue($report->isHealthy());
        $this->assertEmpty($report->getDetails());
    }
}
