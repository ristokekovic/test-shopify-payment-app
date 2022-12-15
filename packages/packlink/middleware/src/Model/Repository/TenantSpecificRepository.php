<?php

namespace Packlink\Middleware\Model\Repository;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Logeecom\Infrastructure\Configuration\Configuration;
use Logeecom\Infrastructure\ServiceRegister;
use Packlink\Middleware\Service\BusinessLogic\ConfigurationService;

/**
 * Class TenantSpecificRepository
 *
 * @package Packlink\Middleware\Model\Repository
 */
abstract class TenantSpecificRepository extends BaseRepository
{
    /**
     * Tenant specific entity table prefix.
     */
    public const TABLE_PREFIX = 'tenant_';
    /**
     * @var ConfigurationService
     */
    private $configService;

    /**
     * Creates tenant specific entity table.
     */
    public function createTable(): void
    {
        if ($this->getConfigService()->getTenant() === null) {
            return;
        }

        if (!Schema::hasTable($this->getTableName())) {
            Schema::create(
                $this->getTableName(),
                function (Blueprint $table) {
                    $table->increments('id');
                    $table->string('type');
                    $table->text('data');

                    for ($i = 1; $i <= $this->getIndexCount(); $i++) {
                        $table->string('index_' . $i)->nullable();
                        $table->index('index_' . $i);
                    }
                }
            );
        }
    }

    /**
     * Drops tenant specific entity table.
     */
    public function dropTable(): void
    {
        if ($this->getConfigService()->getCurrentSystemId() === null) {
            return;
        }

        Schema::dropIfExists($this->getTableName());
    }

    /**
     * Returns the name of database table that this repository should query.
     *
     * @return string
     */
    protected function getTableName(): string
    {
        $tenant = $this->getConfigService()->getTenant();

        if ($tenant !== null) {
            return self::TABLE_PREFIX . $tenant->getId();
        }

        return parent::getTableName();
    }

    /**
     * Returns the maximum number of indexes in the specific integration.
     *
     * @return int
     */
    abstract protected function getIndexCount(): int;

    /**
     * Returns an instance of configuration service.
     *
     * @return \Packlink\Middleware\Service\BusinessLogic\ConfigurationService
     *
     * @throws \InvalidArgumentException
     */
    private function getConfigService(): ConfigurationService
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::class);
        }

        return $this->configService;
    }
}
