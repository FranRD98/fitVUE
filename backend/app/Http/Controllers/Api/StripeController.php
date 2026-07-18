<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeController extends Controller
{
    // Plan Stripe price ID -> plan_id local (1=Free, 2=Premium, 3=Pro).
    private function planForPrice(?string $priceId): ?int
    {
        return match ($priceId) {
            config('services.stripe.price_premium') => 2,
            config('services.stripe.price_pro') => 3,
            default => null,
        };
    }

    private function applyPlan(User $user, int $planId): void
    {
        $user->update([
            'plan_id' => $planId,
            'role' => $planId === 3 ? 'coach' : $user->role,
        ]);
    }

    // Llamado por el frontend tras volver de Stripe, usando el session_id del checkout.
    public function confirm(Request $request)
    {
        $data = $request->validate(['session_id' => ['required', 'string']]);

        $stripe = new StripeClient(config('services.stripe.secret'));
        $session = $stripe->checkout->sessions->retrieve($data['session_id'], ['expand' => ['line_items']]);

        if ($session->payment_status !== 'paid') {
            abort(422, 'El pago no se ha completado.');
        }

        $priceId = $session->line_items->data[0]->price->id ?? null;
        $planId = $this->planForPrice($priceId);

        if (! $planId) {
            abort(422, 'No se ha podido determinar el plan contratado.');
        }

        $user = $request->user();
        $this->applyPlan($user, $planId);

        return response()->json($user->fresh());
    }

    // Webhook oficial de Stripe: fuente de la verdad para activar el plan de forma segura.
    public function webhook(Request $request)
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('services.stripe.webhook_secret'),
            );
        } catch (\Exception $e) {
            Log::warning('Stripe webhook signature inválida: '.$e->getMessage());

            return response()->json(['error' => 'invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $userId = $session->client_reference_id;

            $stripe = new StripeClient(config('services.stripe.secret'));
            $fullSession = $stripe->checkout->sessions->retrieve($session->id, ['expand' => ['line_items']]);
            $priceId = $fullSession->line_items->data[0]->price->id ?? null;
            $planId = $this->planForPrice($priceId);

            $user = $userId ? User::find($userId) : null;

            if ($user && $planId) {
                $this->applyPlan($user, $planId);
            }
        }

        return response()->json(['received' => true]);
    }
}
