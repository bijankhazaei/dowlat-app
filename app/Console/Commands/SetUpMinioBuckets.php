<?php

namespace App\Console\Commands;

use App\Services\MinioService;
use Illuminate\Console\Command;

class SetUpMinioBuckets extends Command
{
    /**
     * @var string
     */
    protected $signature = 'minio:setup';

    /**
     * @var string
     */
    protected $description = 'Setup MinIO buckets and their policies';

    /**
     * Execute the console command.
     */
    public function handle(MinioService $minioService): void
    {
        $this->info('Setting up MinIO buckets and their policies...');
        try {
            $minioService->setupBuckets();
            $this->info('MinIO buckets and policies set up successfully.');
        } catch (\Exception $e) {
            $this->error('Error setting up MinIO buckets and policies: ' . $e->getMessage());
        }
    }
}
