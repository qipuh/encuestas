<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushController extends Controller
{
    public function vapidKey()
    {
        return response()->json(['public_key' => config('app.vapid_public_key')]);
    }

    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint'       => 'required|string',
            'keys.auth'      => 'required|string',
            'keys.p256dh'    => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            ['user_id' => auth()->id(), 'endpoint' => $data['endpoint']],
            ['public_key' => $data['keys']['p256dh'], 'auth_token' => $data['keys']['auth']]
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request)
    {
        $data = $request->validate(['endpoint' => 'required|string']);

        PushSubscription::where('user_id', auth()->id())
            ->where('endpoint', $data['endpoint'])
            ->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Enviar push a uno o varios usuarios.
     * Llamado internamente desde otros controladores.
     */
    public static function sendToUsers(array $userIds, string $title, string $body, string $url = '/app/comentarios'): void
    {
        $subs = PushSubscription::whereIn('user_id', $userIds)->get();
        if ($subs->isEmpty()) return;

        $auth = [
            'VAPID' => [
                'subject'    => config('app.vapid_subject'),
                'publicKey'  => config('app.vapid_public_key'),
                'privateKey' => config('app.vapid_private_key'),
            ],
        ];

        $webPush = new WebPush($auth);

        foreach ($subs as $sub) {
            $subscription = Subscription::create([
                'endpoint'        => $sub->endpoint,
                'keys'            => [
                    'p256dh' => $sub->public_key,
                    'auth'   => $sub->auth_token,
                ],
            ]);

            $webPush->queueNotification(
                $subscription,
                json_encode(compact('title', 'body', 'url'))
            );
        }

        foreach ($webPush->flush() as $report) {
            // Eliminar suscripciones inválidas/expiradas
            if (!$report->isSuccess() && in_array($report->getResponse()?->getStatusCode(), [404, 410])) {
                PushSubscription::where('endpoint', $report->getEndpoint())->delete();
            }
        }
    }
}
