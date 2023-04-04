<?php
declare(strict_types=1);

namespace Tests\Hal\Metric\System\Packages\Composer;

use Hal\Component\File\ReaderInterface;
use Hal\Metric\System\Packages\Composer\Packagist;
use Phake;
use PHPUnit\Framework\TestCase;
use stdClass;

final class PackagistTest extends TestCase
{
    private stdClass $defaultResponse;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->defaultResponse = (object)[
            'name' => '',
            'latest' => null,
            'license' => [],
            'homepage' => null,
            'time' => null,
            'zip' => null,
            'type' => 'unknown',
            'description' => null,
            'github_stars' => 0,
            'github_watchers' => 0,
            'github_forks' => 0,
            'github_open_issues' => 0,
            'download_total' => 0,
            'download_monthly' => 0,
            'download_daily' => 0,
            'favorites' => 0,
        ];
    }

    /**
     * @return void
     */
    public function testFetchWrongNamePackage(): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);
        $response = (new Packagist($fileReader))->get('foo');
        self::assertEqualsCanonicalizing($this->defaultResponse, $response);
        Phake::verifyNoInteraction($fileReader);
    }

    /**
     * @return void
     */
    public function testFetchNonExistingPackage(): void
    {
        $fileReader = Phake::mock(ReaderInterface::class);
        Phake::when($fileReader)->__call('httpReadJson', [Phake::anyParameters()])->thenReturn((object)[]);

        $response = (new Packagist($fileReader))->get('foo/bar');
        self::assertEqualsCanonicalizing($this->defaultResponse, $response);
        Phake::verify($fileReader)->__call('httpReadJson', ['https://packagist.org/packages/foo/bar.json']);
        Phake::verifyNoOtherInteractions($fileReader);
    }

    /**
     * @return void
     */
    public function testFetchExistentPackage(): void
    {
        $expectedResponse = (object)[
            'package' => (object)[
                'type' => 'known',
                'description' => 'This is a description',
                'github_stars' => 78,
                'github_watchers' => 147,
                'github_forks' => 987,
                'github_open_issues' => 1,
                'downloads' => (object)[
                    'total' => 15421478,
                    'monthly' => 245078,
                    'daily' => 9547,
                ],
                'favers' => 149,
                'versions' => (object)[
                    'v1.0.0-alpha' => (object)[ // Not only digits or dots => ignore this version.
                        'license' => (object)['Copyright v1.0.0'],
                        'homepage' => 'https://this.is.a.test.100',
                        'time' => '100ms',
                        'dist' => (object)[
                            'url' => '@archiveZip.100',
                        ]
                    ],
                    'v2.0.0' => (object)[ // Version ok, but not the latest.
                        'license' => (object)['Copyright v2.0.0'],
                        'homepage' => 'https://this.is.a.test.200',
                        'time' => '200ms',
                        'dist' => (object)[
                            'url' => '@archiveZip.200',
                        ]
                    ],
                    'v2.1.0' => (object)[ // Version ok, latest. We expect those values to be fetched.
                        'license' => (object)['Copyright v2.1.0'],
                        'homepage' => 'https://this.is.a.test.210',
                        'time' => '210ms',
                        'dist' => (object)[
                            'url' => '@archiveZip.210',
                        ]
                    ],
                    'v2.0.1' => (object)[ // v2.1.0 already set up.
                        'license' => (object)['Copyright v2.0.1'],
                        'homepage' => 'https://this.is.a.test.201',
                        'time' => '201ms',
                        'dist' => (object)[
                            'url' => '@archiveZip.201',
                        ]
                    ],
                ]
            ],
        ];
        $fileReader = Phake::mock(ReaderInterface::class);
        Phake::when($fileReader)->__call('httpReadJson', [Phake::anyParameters()])->thenReturn($expectedResponse);

        $response = (new Packagist($fileReader))->get('foo/bar');
        self::assertSame('foo/bar', $response->name);
        self::assertSame('2.1.0', $response->latest);
        self::assertSame(['Copyright v2.1.0'], $response->license);
        self::assertSame('https://this.is.a.test.210', $response->homepage);
        self::assertSame('210ms', $response->time);
        self::assertSame('@archiveZip.210', $response->zip);
        self::assertSame('known', $response->type);
        self::assertSame('This is a description', $response->description);
        self::assertSame(78, $response->github_stars);
        self::assertSame(147, $response->github_watchers);
        self::assertSame(987, $response->github_forks);
        self::assertSame(1, $response->github_open_issues);
        self::assertSame(15421478, $response->download_total);
        self::assertSame(245078, $response->download_monthly);
        self::assertSame(9547, $response->download_daily);
        self::assertSame(149, $response->favorites);

        Phake::verify($fileReader)->__call('httpReadJson', ['https://packagist.org/packages/foo/bar.json']);
        Phake::verifyNoOtherInteractions($fileReader);
    }
}
