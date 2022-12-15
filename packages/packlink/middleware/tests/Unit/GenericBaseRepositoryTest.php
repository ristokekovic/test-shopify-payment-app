<?php

namespace Packlink\Middleware\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Logeecom\Tests\Infrastructure\ORM\AbstractGenericStudentRepositoryTest;
use Packlink\Middleware\Tests\CreatesApplication;
use Packlink\Middleware\Tests\Unit\Repository\TestRepository;

class GenericBaseRepositoryTest extends AbstractGenericStudentRepositoryTest
{
    use DatabaseMigrations, CreatesApplication;

    /**
     * @return string
     */
    public function getStudentEntityRepositoryClass(): string
    {
        return TestRepository::class;
    }

    /**
     * Cleans up all storage services used by repositories
     */
    public function cleanUpStorage(): void
    {
        TestRepository::dropTestEntityTable();
    }

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->createApplication();

        parent::setUp();

        TestRepository::createTestEntityTable();
    }
}
