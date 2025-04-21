<?php

namespace App\Services;

use Aws\S3\S3Client;
use Exception;
use Illuminate\Support\Facades\Log;

class MinioService
{
    private S3Client $s3Client;

    public function __construct()
    {
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => env('MINIO_REGION'),
            'endpoint' => env('MINIO_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => env('MINIO_ACCESS_KEY'),
                'secret' => env('MINIO_SECRET_KEY'),
            ],
        ]);
    }

    /**
     * Check MinIO connection by listing buckets.
     *
     * @return bool
     */
    public function checkConnection(): bool
    {
        try {
            $this->s3Client->listBuckets();
            return true; // Connection is successful
        } catch (Exception $e) {
            // Log the error for debugging
            Log::error('MinIO connection failed: ' . $e->getMessage());
            return false; // Connection failed
        }
    }

    /**
     * @throws Exception
     */
    public function setupBuckets(): void
    {
        // Create private bucket
        $this->createBucket(env('MINIO_PRIVATE_BUCKET'));

        // Create public bucket and set public policy
        $this->createBucket(env('MINIO_PUBLIC_BUCKET'));
        $this->setPublicPolicy(env('MINIO_PUBLIC_BUCKET'));
    }

    /**
     * @param $bucketName
     * @return void
     * @throws Exception
     */
    private function createBucket($bucketName): void
    {
        try {
            if (!$this->s3Client->doesBucketExist($bucketName)) {
                $this->s3Client->createBucket([
                    'Bucket' => $bucketName
                ]);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $bucketName
     * @return void
     */
    private function setPublicPolicy($bucketName): void
    {
        $policy = json_encode([
            'Version' => '2012-10-17',
            'Statement' => [
                [
                    'Sid' => 'PublicReadGetObject',
                    'Effect' => 'Allow',
                    'Principal' => '*',
                    'Action' => ['s3:GetObject'],
                    'Resource' => [
                        "arn:aws:s3:::{$bucketName}/*"
                    ]
                ],
            ]
        ]);

        $this->s3Client->putBucketPolicy([
            'Bucket' => $bucketName,
            'Policy' => $policy
        ]);
    }
}
