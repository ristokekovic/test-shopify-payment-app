<?php

namespace Packlink\Middleware\Commands;

use Illuminate\Console\Command;
use Logeecom\Infrastructure\ServiceRegister;
use Packlink\Middleware\Service\Required\MaintenanceModeService;

/**
 * Class StopMaintenanceMode
 *
 * @package Packlink\Middleware\Commands
 */
class StopMaintenanceMode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packlink:maintenance:stop';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stops maintenance mode in the app.';

    /**
     * Executes the console command.
     */
    public function handle(): void
    {
        $maintenanceModeService = ServiceRegister::getService(MaintenanceModeService::CLASS_NAME);

        if ($maintenanceModeService->setStatus(false)) {
            $this->info('Maintenance mode stopped!');
        }
    }
}
