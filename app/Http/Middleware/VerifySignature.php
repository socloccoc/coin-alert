<?php

namespace App\Http\Middleware;

use Closure;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Exception\InvalidSignatureException;
// use Yamakadi\LineWebhooks\Exceptions\WebhookFailed;

class VerifySignature
{
    protected $except = [
        '/line/webhook.php',
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     * @throws \Yamakadi\LineWebhooks\Exceptions\WebhookFailed
     */
    public function handle($request, Closure $next)
    {
        $signature = $request->header(HTTPHeader::LINE_SIGNATURE);

        if (!$signature) {
            // throw WebhookFailed::missingSignature();
        }

        if (!$this->isValid($signature, $request->getContent())) {
            // throw WebhookFailed::invalidSignature($signature);
        }

        return $next($request);
    }

    /**
     * Check whether the request signature is valid
     *
     * @param string $signature
     * @param string $payload
     *
     * @return bool
     * @throws \Yamakadi\LineWebhooks\Exceptions\WebhookFailed
     */
    protected function isValid(string $signature, string $payload): bool
    {
        $secret = '158617eacbd3b00059ba151d8e0f5818';

        if (empty($secret)) {
            // throw WebhookFailed::channelSecretNotSet();
        }

        try {
            return LINEBot\SignatureValidator::validateSignature($payload, $secret, $signature);
        } catch (InvalidSignatureException $exception) {
            return false;
        }
    }
}
