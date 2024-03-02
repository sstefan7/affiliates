<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AffiliateInvitationTest extends TestCase
{
    /**
     * Able to navigate to the home page...
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Returns validation errors...
     */
    public function test_returns_validation_errors(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'affiliates.txt',
            'This is just a plain text...'
        );

        $file->mimeType('text/plain');

        $response = $this->post('/invite', [
            'affiliates' => $file
        ]);

        $response->assertInvalid(['affiliates']);

        $response->assertStatus(302);

        $response->assertRedirect('/');

        $this->followRedirects($response)->assertSeeText('isn\'t of valid JSON format', true);
    }

    /**
     * Throws the AffiliateInvitationException due to a malformed input...
     */
    public function test_throws_an_exception_due_to_malformed_input(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'affiliates.txt',
            '{"latitude": "52.986375", "affiliate_id": 12, "name": "Yosef Giles", "longitude": "-6.043701"}\n\n\n'
        );

        $file->mimeType('application/json');

        $response = $this->post('/invite', [
            'affiliates' => $file
        ]);

        $response->assertValid(['affiliates']);

        $response->assertStatus(302);

        $response->assertRedirect('/');

        $this->followRedirects($response)->assertSeeText('empty or malformed', false);
    }

    /**
     * Returns invited affiliates...
     */
    public function test_returns_eligible_affiliates(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'affiliates.txt',
            '{"latitude": "52.986375", "affiliate_id": 12, "name": "Yosef Giles", "longitude": "-6.043701"}
            {"latitude": "51.92893", "affiliate_id": 1, "name": "Lance Keith", "longitude": "-10.27699"}'
        );

        $file->mimeType('application/json');

        $response = $this->post('/invite', [
            'affiliates' => $file
        ]);

        $response->assertValid(['affiliates']);

        $response->assertSessionHas('guestList');

        $response->assertStatus(302);

        $response->assertRedirect('/');

        $this->followRedirects($response)->assertSee('Yosef Giles');

        $this->followRedirects($response)->assertDontSee('Lance Keith');
    }

    /**
     * There were no eligible affiliates to invite...
     */
    public function test_no_eligible_affiliates_were_invited(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'affiliates.txt',
            '{"latitude": "51.92893", "affiliate_id": 1, "name": "Lance Keith", "longitude": "-10.27699"}
            {"latitude": "51.8856167", "affiliate_id": 2, "name": "Mohamed Bradshaw", "longitude": "-10.4240951"}'
        );

        $file->mimeType('application/json');

        $response = $this->post('/invite', [
            'affiliates' => $file
        ]);

        $response->assertValid();

        $response->assertSessionHas('guestList', []);

        $response->assertStatus(302);

        $response->assertRedirect('/');

        $this->followRedirects($response)->assertSeeText('couldn\'t find any eligible affiliates', false);

        $this->followRedirects($response)->assertDontSee('Lance Keith');

        $this->followRedirects($response)->assertDontSee('Mohamed Bradshaw');
    }
}
