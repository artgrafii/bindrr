<?php

uses(Tests\TestCase::class);

test('boost proof marker exists', function (): void {
    $this->assertFileExists(storage_path('app/boost_proof.txt'));
});
