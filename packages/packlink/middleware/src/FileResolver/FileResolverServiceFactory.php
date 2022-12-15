<?php

namespace Packlink\Middleware\FileResolver;

use Packlink\BusinessLogic\FileResolver\FileResolverService;

/**
 * Interface FileResolverServiceFactory
 *
 * @package Packlink\Middleware\FileResolver
 */
interface FileResolverServiceFactory
{
    /**
     * Gets FileResolverService.
     *
     * @return FileResolverService
     */
    public function getFileResolverService(): FileResolverService;
}