<?php

namespace App\Services;

use App\Exceptions\AffiliateInvitationException;
use Illuminate\Http\UploadedFile;

class AffiliateInvitationService
{
    /**
     * Invites the eligible affiliates...
     * 
     * @param UploadedFile $affiliates
     * 
     * @return array
     */
    public function invite(UploadedFile $affiliates): array
    {
        if (!$affiliates->isValid()) {
            throw new AffiliateInvitationException(
                'The file couldn\'t be processed as it wasn\'t properly uploaded.'
            );
        }

        $guests = $this->parseAffiliateList($affiliates->path());

        if (empty($guests)) {
            throw new AffiliateInvitationException(
                'Couldn\'t find any affiliate information as the submitted file is either empty or malformed.'
            );
        }

        return $this->getEligibleGuests($guests);

        // ...but, wait! If we have eligible affiliates, don't we wanna make this official? Create an 'event', perhaps?

        // Naturally, somebody's got to 'listen'. I mean, we do have to 'send invites', or 'buy food and beverages', right? ;-)
    }

    /**
     * Parses the list of submitted affiliates...
     * 
     * @param string $path
     * 
     * @return array
     */
    private function parseAffiliateList(string $path): array
    {
        $guests = [];

        if (($file = fopen($path, 'r')) !== false) {
            while (($extractedRow = fgets($file)) !== false) {
                if ($guest = json_decode(trim($extractedRow))) {
                    $guests[$guest->affiliate_id] = $guest;
                }
            }

            fclose($file);
        }

        return $guests;
    }

    /**
     * Returns eligible affiliates from the guest list...
     * 
     * @param array $guests
     * 
     * @return array
     */
    private function getEligibleGuests(array $guests): array
    {
        $eligibleGuests = [];

        foreach ($guests as $guest) {
            if ($this->isEligible($guest)) {
                $eligibleGuests[] = $guest;
            }
        }

        return $eligibleGuests;
    }

    /**
     * Determines if the guest is eligible to be invited...
     * 
     * @param \stdClass $guest
     * 
     * @return bool
     */
    private function isEligible(\stdClass $guest): bool
    {
        // Dublin's coordinates...
        $referencePoint = [
            'latitude' => 53.3340285,
            'longitude' => -6.2535495
        ];

        $radianValues = [
            'p1Latitude' => deg2rad($referencePoint['latitude']),
            'p1Longitude' => deg2rad($referencePoint['longitude']),
            'p2Latitude' => deg2rad($guest->latitude),
            'p2Longitude' => deg2rad($guest->longitude)
        ];

        $earthRadius = 6371000;

        $deltaOfLatitudes = $radianValues['p2Latitude'] - $radianValues['p1Latitude'];
        $deltaOfLongitudes = $radianValues['p2Longitude'] - $radianValues['p1Longitude'];

        // Using the haversine formula as we're disregarding the existence of antipodal pairs of coordinates...
        $angle = 2 * asin(sqrt(pow(sin($deltaOfLatitudes / 2), 2) + cos($radianValues['p1Latitude'])
            * cos($radianValues['p2Latitude']) * pow(sin($deltaOfLongitudes / 2), 2)));

        return 100000 > $angle * $earthRadius;
    }
}
