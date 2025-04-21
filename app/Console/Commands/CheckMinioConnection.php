<?php

namespace App\Console\Commands;

use App\Services\MinioService;
use Illuminate\Console\Command;

class CheckMinioConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-minio-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(MinioService  $minioService)
    {
        $this->info('Checking Minio connection...');
        $minioService->checkConnection();
        $this->info('Minio connection is OK!');
    }
}
