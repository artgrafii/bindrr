<?php

use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;

it('stores spaces on the s3 disk for the dogfood bucket', function () {
    expect(config('bindrr.disk'))->toBe('s3')
        ->and(config('filesystems.default'))->toBe('s3')
        ->and(config('filesystems.disks.s3.driver'))->toBe('s3')
        ->and(config('filesystems.disks.s3.region'))->toBe('ap-northeast-2')
        ->and(config('filesystems.disks.s3.bucket'))->toBe('artgrafii-bindrr-156777722327')
        ->and(config('filesystems.disks.s3.throw'))->toBeTrue()
        ->and(config('filesystems.disks.s3.use_path_style_endpoint'))->toBeFalse();

    expect(Storage::disk('s3')->getAdapter())->toBeInstanceOf(AwsS3V3Adapter::class);
});
