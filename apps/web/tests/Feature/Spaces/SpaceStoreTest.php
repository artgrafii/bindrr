<?php

use Dotenv\Repository\Adapter\ArrayAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Illuminate\Support\Env;
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

it('treats blank aws credentials as null so the role provider is used', function () {
    $blank = filesystemConfigWith([
        'AWS_ACCESS_KEY_ID' => '',
        'AWS_SECRET_ACCESS_KEY' => '',
        'AWS_SESSION_TOKEN' => '',
    ]);

    expect($blank['disks']['s3']['key'])->toBeNull()
        ->and($blank['disks']['s3']['secret'])->toBeNull()
        ->and($blank['disks']['s3']['token'])->toBeNull()
        ->and($blank['disks']['s3']['region'])->toBe('ap-northeast-2')
        ->and($blank['disks']['s3']['bucket'])->toBe('artgrafii-bindrr-156777722327');

    $static = filesystemConfigWith([
        'AWS_ACCESS_KEY_ID' => 'AKIAEXAMPLE',
        'AWS_SECRET_ACCESS_KEY' => 'secret-value',
        'AWS_SESSION_TOKEN' => 'session-token',
    ]);

    expect($static['disks']['s3']['key'])->toBe('AKIAEXAMPLE')
        ->and($static['disks']['s3']['secret'])->toBe('secret-value')
        ->and($static['disks']['s3']['token'])->toBe('session-token');
});

function filesystemConfigWith(array $variables): array
{
    $repository = RepositoryBuilder::createWithNoAdapters()
        ->addAdapter(ArrayAdapter::class)
        ->make();

    foreach ([
        'AWS_DEFAULT_REGION' => 'ap-northeast-2',
        'AWS_BUCKET' => 'artgrafii-bindrr-156777722327',
        'AWS_USE_PATH_STYLE_ENDPOINT' => 'false',
        'APP_URL' => 'http://localhost',
        ...$variables,
    ] as $key => $value) {
        $repository->set($key, $value);
    }

    $property = new ReflectionProperty(Env::class, 'repository');
    $previous = $property->getValue();
    $property->setValue(null, $repository);

    try {
        return require config_path('filesystems.php');
    } finally {
        $property->setValue(null, $previous);
    }
}
