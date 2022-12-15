<?php

namespace Packlink\Middleware\Commands;

use Illuminate\Console\Command;

/**
 * Class Migrate
 *
 * @package Packlink\Middleware\Commands
 */
class Migrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'packlink:migrate';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wraps the existing Laravel artisan migrate command and adds maintenance mode switching.';

    /**
     * Executes the console command.
     */
    public function handle(): int
    {
        $this->call('packlink:maintenance:stop');

        $this->info('Starting migration...');
        $exitCode = $this->call('migrate');

        $this->call('packlink:maintenance:stop');

        return $exitCode;
    }
}
