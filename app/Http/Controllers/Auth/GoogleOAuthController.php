<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GmailOAuthService;
use Illuminate\Http\Request;

/**
 * Handles the Google OAuth2 flow used to authorize the application's Gmail account for sending emails.
 * This is an admin-only utility: the redirect/callback pair exchanges an auth code for a refresh token
 * that is then stored in config to power the GmailOAuthService.
 */
class GoogleOAuthController extends Controller
{
    /**
     * Redirect to Google OAuth2 consent screen.
     * Only accessible by admin users.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function redirect()
    {
        try {
            $redirectUri = url('/oauth2/callback');
            $authUrl = GmailOAuthService::getAuthorizationUrl($redirectUri);

            if (empty(parse_url($authUrl, PHP_URL_QUERY))) {
                return response('Error: Google client_id not configured. Check config/services.php and .env', 500);
            }

            return redirect($authUrl);
        } catch (\Throwable $e) {
            return response('Error: ' . $e->getMessage() . '<br>File: ' . $e->getFile() . ':' . $e->getLine(), 500);
        }
    }

    /**
     * Handle the OAuth2 callback from Google.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\View\View
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return response('OAuth2 Error: ' . $request->input('error') . '<br>Description: ' . $request->input('error_description', 'none'), 400);
        }

        $code = $request->input('code');
        if (!$code) {
            return response('No authorization code received.', 400);
        }

        $redirectUri = url('/oauth2/callback');
        $tokens = GmailOAuthService::exchangeCode($code, $redirectUri);

        if (!$tokens) {
            return response('Failed to exchange authorization code for tokens. Check Laravel logs for details.', 500);
        }

        $refreshToken = $tokens['refresh_token'] ?? null;

        return response()->view('auth.oauth-success', [
            'refreshToken' => $refreshToken,
            'accessToken' => $tokens['access_token'] ?? null,
            'expiresIn' => $tokens['expires_in'] ?? null,
        ]);
    }
}
