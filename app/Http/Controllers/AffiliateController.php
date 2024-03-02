<?php

namespace App\Http\Controllers;

use App\Exceptions\AffiliateInvitationException;
use App\Http\Requests\InviteAffiliatesRequest;
use App\Services\AffiliateInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected AffiliateInvitationService $affiliate)
    {
        //
    }

    /**
     * Renders the home page...
     * 
     * @return View
     */
    public function index(): View
    {
        return view('pages.affiliates.home', [
            'guestList' => session()->has('guestList') ? session()->get('guestList') : null,
            'invitationException' => session()->has('invitationException') ? session()->get('invitationException') : null
        ]);
    }

    /**
     * Invites eligible affiliates...
     * 
     * @param InviteAffiliatesRequest $request
     * 
     * @return void
     */
    public function invite(InviteAffiliatesRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $invitedAffiliates = $this->affiliate->invite($validated['affiliates']);
        } catch (AffiliateInvitationException $exception) {
            return redirect()->route('home')->with([
                'invitationException' => 'An error occurred while inviting the affiliates: ' . $exception->getMessage()
            ]);
        }

        return redirect()->route('home')->with([
            'guestList' => $invitedAffiliates
        ]);
    }
}
