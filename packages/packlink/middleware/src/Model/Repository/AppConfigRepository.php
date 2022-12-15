<?php

namespace Packlink\Middleware\Model\Repository;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Packlink\Middleware\Model\Repository\Required\AppConfigRepository as AppConfigRepositoryInterface;

/**
 * Class AppConfigRepository
 *
 * @package Packlink\Middleware\Model\Repository
 */
class AppConfigRepository implements AppConfigRepositoryInterface
{
    /**
     * Database table name.
     */
    protected const APP_CONFIG_TABLE = 'app_config';

    /**
     * Sets config value.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return bool Result of the operation.
     */
    public function set(string $key, $value): bool
    {
        if (!Schema::hasTable(static::APP_CONFIG_TABLE)) {
            return false;
        }

        return DB::table(static::APP_CONFIG_TABLE)->updateOrInsert(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Retrieves config value.
     *
     * @param string $key
     *
     * @return mixed | null
     */
    public function get(string $key)
    {
        if (!Schema::hasTable(static::APP_CONFIG_TABLE)) {
            return null;
        }

        return DB::table(static::APP_CONFIG_TABLE)
            ->where('key', '=', $key)
            ->value('value');
    }
}
