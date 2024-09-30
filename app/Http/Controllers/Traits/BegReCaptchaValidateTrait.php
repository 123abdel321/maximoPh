<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Http;
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;

trait BegReCaptchaValidateTrait
{
	public function validateReCaptcha($captcha_token)
	{
		$response = Http::asForm()->post(config('services.recaptcha.url'), [
			'secret' => config('services.recaptcha.secret'),
            'response' => $captcha_token,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        ]);

		$recaptchaResponse = (object)$response->json();

		return $recaptchaResponse;
	}
}