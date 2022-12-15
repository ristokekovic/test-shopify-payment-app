<?php

namespace Packlink\Middleware\Commands;

use Illuminate\Console\Command;
use Logeecom\Infrastructure\ServiceRegister;
use Packlink\Middleware\Service\Required\MaintenanceModeService;

/**
 * Class StartMaintenanceModeCommand
 *
 * @package Packlink\Middleware\Commands
 */
class StartMaintenanceMode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packlink:maintenance:start';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts maintenance mode in the app.';

    /**
     * Executes the console command.
     */
    public function handle(): void
    {
        $maintenanceModeService = ServiceRegister::getService(MaintenanceModeService::CLASS_NAME);

        if ($maintenanceModeService->setStatus(true)) {
            $this->info('Maintenance mode started!');
        }
    }
}
