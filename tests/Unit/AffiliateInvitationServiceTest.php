<?php

namespace Tests\Unit;

use App\Exceptions\AffiliateInvitationException;
use App\Services\AffiliateInvitationService;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;
use stdClass;

class AffiliateInvitationServiceTest extends TestCase
{
    /**
     * @return void
     * 
     * @covers App\Services\AffiliateInvitationService::invite
     */
    public function test_invite_throws_an_exception_due_to_unsuccessful_upload(): void
    {
        $file = new UploadedFile(
            '/tmp',
            'temporary.txt',
            'application/json',
            1,
            false
        );

        $this->expectException(AffiliateInvitationException::class);

        $this->expectExceptionMessage('The file couldn\'t be processed as it wasn\'t properly uploaded.');

        (new AffiliateInvitationService)->invite($file);
    }

    /**
     * @return void
     * 
     * @covers App\Services\AffiliateInvitationService::invite
     */
    public function test_invite_throws_an_exception_due_to_malformed_input(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'affiliates.txt',
            '{"latitude": "52.986375", "affiliate_id": 12, "name": "Yosef Giles", "longitude": "-6.043701"}\n\n\n'
        );

        $file->mimeType('application/json');

        $this->expectException(AffiliateInvitationException::class);

        $this->expectExceptionMessage('Couldn\'t find any affiliate information as the submitted file is either empty or malformed.');

        (new AffiliateInvitationService)->invite($file);
    }

    /**
     * @param string $content
     * @param array $expected
     * 
     * @return void
     * 
     * @covers App\Services\AffiliateInvitationService::invite
     *
     * @dataProvider generateInviteTestCases
     */
    public function test_invite(string $content, array $expected): void
    {
        $file = UploadedFile::fake()->createWithContent('affiliates.txt', $content);

        $file->mimeType('application/json');

        $this->assertEquals(
            (new AffiliateInvitationService)->invite($file),
            $expected
        );
    }

    /**
     * @param string $content
     * @param array $expected
     * 
     * @return void
     * 
     * @covers App\Services\AffiliateInvitationService::parseAffiliateList
     *
     * @dataProvider generateParseAffiliateListTestCases
     */
    public function test_parse_affiliate_list(string $content, array $expected): void
    {
        $file = UploadedFile::fake()->createWithContent('affiliates.txt', $content);

        $file->mimeType('application/json');

        $service = new AffiliateInvitationService();

        $this->assertEquals(
            $this->invokeMethod($service, 'parseAffiliateList', [$file->getRealPath()]),
            $expected
        );
    }

    /**
     * @param stdClass $guests
     * @param array $expected
     * 
     * @return void
     * 
     * @covers App\Services\AffiliateInvitationService::getEligibleGuests
     *
     * @dataProvider generateGetEligibleGuestsTestCases
     */
    public function test_get_eligible_guests(array $guests, array $expected): void
    {
        $service = new AffiliateInvitationService();

        $this->assertEquals(
            $this->invokeMethod($service, 'getEligibleGuests', [$guests]),
            $expected
        );
    }

    /**
     * @param stdClass $parameters
     * @param bool $expected
     * 
     * @return void
     * 
     * @covers App\Services\AffiliateInvitationService::isEligible
     *
     * @dataProvider generateIsEligibleTestCases
     */
    public function test_is_eligible(stdClass $guest, bool $expected): void
    {
        $service = new AffiliateInvitationService();

        $this->assertEquals(
            $this->invokeMethod($service, 'isEligible', [$guest]),
            $expected
        );
    }

    /**
     * Provides test cases for the test_invite test...
     * 
     * @return void
     */
    private function generateInviteTestCases(): array
    {
        $eligibleGuest = new \stdClass();

        $eligibleGuest->affiliate_id = 12;
        $eligibleGuest->name = 'Yosef Giles';
        $eligibleGuest->latitude = 52.986375;
        $eligibleGuest->longitude = -6.043701;

        return [
            [
                '{"latitude": "52.986375", "affiliate_id": 12, "name": "Yosef Giles", "longitude": "-6.043701"}
                {"latitude": "51.92893", "affiliate_id": 1, "name": "Lance Keith", "longitude": "-10.27699"}',
                [
                    $eligibleGuest
                ]
            ],
            [
                '{"latitude": "51.92893", "affiliate_id": 1, "name": "Lance Keith", "longitude": "-10.27699"}
                {"latitude": "51.8856167", "affiliate_id": 2, "name": "Mohamed Bradshaw", "longitude": "-10.4240951"}',
                []
            ]
        ];
    }

    /**
     * Provides test cases for the test_parse_affiliate_list test...
     * 
     * @return void
     */
    private function generateParseAffiliateListTestCases(): array
    {
        $eligibleGuest = new \stdClass();

        $eligibleGuest->affiliate_id = 12;
        $eligibleGuest->name = 'Yosef Giles';
        $eligibleGuest->latitude = 52.986375;
        $eligibleGuest->longitude = -6.043701;

        $ineligibleGuest = new \stdClass();

        $ineligibleGuest->affiliate_id = 1;
        $ineligibleGuest->name = 'Lance Keith';
        $ineligibleGuest->latitude = 51.92893;
        $ineligibleGuest->longitude = -10.27699;

        return [
            [
                '{"latitude": "52.986375", "affiliate_id": 12, "name": "Yosef Giles", "longitude": "-6.043701"}
                {"latitude": "51.92893", "affiliate_id": 1, "name": "Lance Keith", "longitude": "-10.27699"}',
                [
                    12 => $eligibleGuest,
                    1 => $ineligibleGuest
                ]
            ],
            [
                '',
                []
            ]
        ];
    }

    /**
     * Provides test cases for the test_is_eligible test...
     * 
     * @return void
     */
    private function generateGetEligibleGuestsTestCases(): array
    {
        $eligibleGuest = new \stdClass();

        $eligibleGuest->latitude = 52.986375;
        $eligibleGuest->longitude = -6.043701;

        $ineligibleGuest = new \stdClass();

        $ineligibleGuest->latitude = 51.92893;
        $ineligibleGuest->longitude = -10.27699;

        return [
            [
                [
                    $eligibleGuest,
                    $ineligibleGuest
                ],
                [
                    $eligibleGuest
                ]
            ],
            [
                [
                    $ineligibleGuest
                ],
                []
            ],
            [
                [],
                []
            ]
        ];
    }

    /**
     * Provides test cases for the test_is_eligible test...
     * 
     * @return void
     */
    private function generateIsEligibleTestCases(): array
    {
        $eligibleGuest = new \stdClass();

        $eligibleGuest->latitude = 52.986375;
        $eligibleGuest->longitude = -6.043701;

        $ineligibleGuest = new \stdClass();

        $ineligibleGuest->latitude = 51.92893;
        $ineligibleGuest->longitude = -10.27699;

        return [
            [
                $eligibleGuest,
                true
            ],
            [
                $ineligibleGuest,
                false
            ]
        ];
    }

    /**
     * Leverages the Reflection API to call publicly inaccessible methods...
     * 
     * @param mixed $object
     * @param string $method
     * @param array $parameters
     * 
     * @return mixed
     */
    private function invokeMethod(mixed $object, string $method, array $parameters): mixed
    {
        $reflection = new \ReflectionClass(get_class($object));

        $method = $reflection->getMethod($method);

        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
