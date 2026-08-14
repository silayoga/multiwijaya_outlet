<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Listing;
use App\Models\Order;
use App\Models\PricingPlan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SubscriptionCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function makeVerifiedUser(): User
    {
        return User::factory()->create([
            'phone_number' => '6281234567890',
            'phone_verified_at' => now(),
        ]);
    }

    private function makeSoftwarePlan(string $billingCycle = 'monthly', float $price = 50000): array
    {
        $category = Category::create([
            'name' => 'Software & Services', 'slug' => 'software-services-'.uniqid(),
            'type_hint' => 'software_service',
        ]);

        $listing = Listing::create([
            'category_id' => $category->id,
            'name' => 'Restoflow Pro',
            'slug' => 'restoflow-pro-'.uniqid(),
            'listing_type' => 'software_service',
            'status' => 'active',
        ]);

        $plan = PricingPlan::create([
            'listing_id' => $listing->id,
            'plan_name' => 'Pro',
            'billing_cycle' => $billingCycle,
            'price' => $price,
            'currency' => 'IDR',
            'is_default' => true,
        ]);

        return [$listing, $plan];
    }

    private function makeHardwarePlan(float $price = 90000): array
    {
        $category = Category::create([
            'name' => 'Networking', 'slug' => 'networking-'.uniqid(),
            'type_hint' => 'hardware',
        ]);

        $listing = Listing::create([
            'category_id' => $category->id,
            'name' => 'Mikrotik hAP',
            'slug' => 'mikrotik-hap-'.uniqid(),
            'listing_type' => 'physical_product',
            'status' => 'active',
        ]);

        $plan = PricingPlan::create([
            'listing_id' => $listing->id,
            'plan_name' => 'Retail',
            'billing_cycle' => 'one_time',
            'price' => $price,
            'currency' => 'IDR',
            'is_default' => true,
        ]);

        return [$listing, $plan];
    }

    private function addToCart(User $user, Listing $listing, PricingPlan $plan): CartItem
    {
        $cart = Cart::firstOrCreate(['user_id' => $user->id, 'status' => 'active']);

        return $cart->items()->create([
            'listing_id' => $listing->id,
            'pricing_plan_id' => $plan->id,
            'listing_name_snapshot' => $listing->name,
            'plan_name_snapshot' => $plan->plan_name,
            'unit_price_snapshot' => $plan->price,
            'billing_cycle_snapshot' => $plan->billing_cycle,
            'quantity' => 1,
        ]);
    }

    public function test_pure_subscription_cart_creates_trialing_subscription_with_no_immediate_charge(): void
    {
        Http::fake([
            '*/v1/subscriptions' => Http::response(['id' => 'sub-fake-123', 'status' => 'active'], 200),
        ]);

        $user = $this->makeVerifiedUser();
        [$listing, $plan] = $this->makeSoftwarePlan('monthly', 50000);
        $this->addToCart($user, $listing, $plan);

        $response = $this->actingAs($user)->post('/subscribe/checkout', ['token_id' => 'fake-card-token']);

        $response->assertRedirect(route('subscriptions.index'));

        $subscription = Subscription::first();
        $this->assertNotNull($subscription);
        $this->assertSame('trialing', $subscription->status);
        $this->assertSame('sub-fake-123', $subscription->gateway_subscription_id);
        $this->assertSame('fake-card-token', $subscription->saved_token_id);
        $this->assertTrue($subscription->trial_ends_at->isBetween(now()->addDays(6), now()->addDays(8)));

        // No one-time Order/Payment created for a subscription-only checkout.
        $this->assertSame(0, Order::count());

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/v1/subscriptions')
                && $request['payment_type'] === 'credit_card'
                && $request['token'] === 'fake-card-token'
                && $request['schedule']['interval'] === 1
                && $request['schedule']['interval_unit'] === 'month';
        });
    }

    public function test_registration_failure_rolls_back_local_subscription_row(): void
    {
        Http::fake([
            '*/v1/subscriptions' => Http::response(['status_message' => 'Invalid token'], 400),
        ]);

        $user = $this->makeVerifiedUser();
        [$listing, $plan] = $this->makeSoftwarePlan();
        $this->addToCart($user, $listing, $plan);

        $response = $this->actingAs($user)->post('/subscribe/checkout', ['token_id' => 'bad-token']);

        $response->assertRedirect(route('subscriptions.checkout'));
        $this->assertSame(0, Subscription::count());
    }

    public function test_webhook_success_activates_trialing_subscription_and_sets_period(): void
    {
        $subscription = $this->createSubscription('trialing');

        $payload = [
            'order_id' => 'sub-order-1',
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'transaction_status' => 'settlement',
            'subscription_id' => $subscription->gateway_subscription_id,
        ];
        $payload['signature_key'] = $this->signPayload($payload);

        $response = $this->postJson('/webhooks/midtrans-subscription', $payload);

        $response->assertOk();
        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertNotNull($subscription->current_period_start);
        $this->assertNotNull($subscription->current_period_end);
        $this->assertTrue($subscription->current_period_end->greaterThan(now()));
    }

    public function test_webhook_failure_marks_subscription_past_due_without_crashing(): void
    {
        $subscription = $this->createSubscription('active');

        $payload = [
            'order_id' => 'sub-order-2',
            'status_code' => '202',
            'gross_amount' => '50000.00',
            'transaction_status' => 'deny',
            'subscription_id' => $subscription->gateway_subscription_id,
        ];
        $payload['signature_key'] = $this->signPayload($payload);

        $response = $this->postJson('/webhooks/midtrans-subscription', $payload);

        $response->assertOk();
        $this->assertSame('past_due', $subscription->fresh()->status);
    }

    public function test_webhook_with_forged_signature_is_rejected_with_no_state_change(): void
    {
        $subscription = $this->createSubscription('trialing');

        $payload = [
            'order_id' => 'sub-order-3',
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'transaction_status' => 'settlement',
            'subscription_id' => $subscription->gateway_subscription_id,
            'signature_key' => 'not-a-real-signature',
        ];

        $response = $this->postJson('/webhooks/midtrans-subscription', $payload);

        $response->assertForbidden();
        $this->assertSame('trialing', $subscription->fresh()->status);
    }

    public function test_webhook_with_missing_signature_is_rejected_with_no_state_change(): void
    {
        $subscription = $this->createSubscription('trialing');

        $payload = [
            'order_id' => 'sub-order-4',
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'transaction_status' => 'settlement',
            'subscription_id' => $subscription->gateway_subscription_id,
        ];

        $response = $this->postJson('/webhooks/midtrans-subscription', $payload);

        // No signature_key field at all fails the required-fields check (400)
        // before signature comparison is even reached — same ordering as
        // MidtransGateway's existing hardware webhook. Either way, nothing
        // changes state, which is the actual security property under test.
        $response->assertStatus(400);
        $this->assertSame('trialing', $subscription->fresh()->status);
    }

    public function test_hardware_only_checkout_is_completely_unaffected(): void
    {
        Http::fake([
            '*/snap/v1/transactions' => Http::response(['redirect_url' => 'https://app.sandbox.midtrans.com/snap/v1/fake-redirect'], 200),
        ]);

        $user = $this->makeVerifiedUser();
        [$listing, $plan] = $this->makeHardwarePlan(90000);
        $this->addToCart($user, $listing, $plan);

        $response = $this->actingAs($user)->post('/checkout/place-order');

        $response->assertRedirect('https://app.sandbox.midtrans.com/snap/v1/fake-redirect');
        $this->assertSame(1, Order::count());
        $this->assertSame(0, Subscription::count());

        Http::assertSent(fn ($request) => str_contains($request->url(), '/snap/v1/transactions'));
    }

    public function test_direct_checkout_link_preselects_plan_by_slug(): void
    {
        $category = Category::create([
            'name' => 'Software & Services', 'slug' => 'software-services-'.uniqid(),
            'type_hint' => 'software_service',
        ]);

        $listing = Listing::create([
            'category_id' => $category->id,
            'name' => 'Restoflow',
            'slug' => 'restoflow-'.uniqid(),
            'listing_type' => 'software_service',
            'status' => 'active',
        ]);

        $plans = [
            'restoflow-basic' => 250000,
            'restoflow-pro' => 300000,
            'restoflow-enterprise' => 500000,
            'restoflow-enterprise-plus' => 850000,
        ];

        foreach ($plans as $slug => $price) {
            PricingPlan::create([
                'listing_id' => $listing->id,
                'plan_name' => $slug,
                'slug' => $slug,
                'billing_cycle' => 'monthly',
                'price' => $price,
                'currency' => 'IDR',
                'is_default' => $slug === 'restoflow-pro',
            ]);
        }

        $user = $this->makeVerifiedUser();

        foreach ($plans as $slug => $price) {
            $response = $this->actingAs($user)->get('/subscribe/checkout?plan='.$slug);

            $response->assertOk();
            $response->assertSeeText('Rp '.number_format($price, 0, ',', '.').'/mo');

            $this->assertSame(1, Cart::where('user_id', $user->id)->where('status', 'active')->first()->items()->count());
        }
    }

    public function test_direct_checkout_link_with_unknown_plan_slug_404s(): void
    {
        $user = $this->makeVerifiedUser();

        $response = $this->actingAs($user)->get('/subscribe/checkout?plan=does-not-exist');

        $response->assertNotFound();
    }

    public function test_mixed_hardware_and_subscription_cart_is_still_blocked(): void
    {
        $user = $this->makeVerifiedUser();
        [$hardwareListing, $hardwarePlan] = $this->makeHardwarePlan();
        [$softwareListing, $softwarePlan] = $this->makeSoftwarePlan();
        $this->addToCart($user, $hardwareListing, $hardwarePlan);
        $this->addToCart($user, $softwareListing, $softwarePlan);

        $response = $this->actingAs($user)->post('/checkout/place-order');

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
        $this->assertSame(0, Order::count());
        $this->assertSame(0, Subscription::count());
    }

    private function createSubscription(string $status): Subscription
    {
        $user = $this->makeVerifiedUser();
        [$listing, $plan] = $this->makeSoftwarePlan('monthly', 50000);

        return Subscription::create([
            'user_id' => $user->id,
            'listing_id' => $listing->id,
            'pricing_plan_id' => $plan->id,
            'status' => $status,
            'trial_ends_at' => now()->addDays(7),
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(7),
            'gateway_subscription_id' => 'sub-fake-'.uniqid(),
            'saved_token_id' => 'fake-token',
        ]);
    }

    private function signPayload(array $payload): string
    {
        return hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('services.midtrans.server_key')
        );
    }
}
