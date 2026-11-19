<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorPurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVendorPurchaseTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function createApprovedVendor(): array
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $profile = VendorProfile::create([
            'user_id' => $vendor->id,
            'business_name' => 'Approved Supplier',
            'phone' => '9876543210',
            'business_address' => 'Lucknow',
            'approval_status' => 'approved',
        ]);

        $product = Product::create([
            'vendor_profile_id' => $profile->id,
            'name' => 'Sugar',
            'slug' => 'sugar-' . uniqid(),
            'description' => 'Wholesale sugar',
            'category' => 'Grocery',
            'unit' => 'kg',
            'price_per_unit' => 90,
            'available_quantity' => 100,
            'minimum_order_quantity' => 1,
            'status' => 'approved',
        ]);

        return [$vendor, $profile, $product];
    }

    public function test_admin_can_create_vendor_purchase(): void
    {
        $admin = $this->createAdmin();
        [, $profile, $product] = $this->createApprovedVendor();

        $response = $this->actingAs($admin)->post(route('admin.purchases.store'), [
            'product_id' => $product->id,
            'quantity' => 100,
            'unit_price' => 90,
            'purchase_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('vendor_purchases', [
            'vendor_profile_id' => $profile->id,
            'product_id' => $product->id,
            'created_by' => $admin->id,
            'quantity' => '100.000',
            'unit_price' => '90.00',
            'subtotal' => '9000.00',
            'status' => 'ordered',
        ]);
    }

    public function test_admin_cannot_purchase_unapproved_product(): void
    {
        $admin = $this->createAdmin();
        [, , $product] = $this->createApprovedVendor();

        $product->update(['status' => 'pending']);

        $response = $this->actingAs($admin)->post(route('admin.purchases.store'), [
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 90,
            'purchase_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('vendor_purchases', 0);
    }

    public function test_admin_cannot_purchase_from_unapproved_vendor(): void
    {
        $admin = $this->createAdmin();
        [, $profile, $product] = $this->createApprovedVendor();

        $profile->update(['approval_status' => 'pending']);

        $response = $this->actingAs($admin)->post(route('admin.purchases.store'), [
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 90,
            'purchase_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('vendor_purchases', 0);
    }

    public function test_purchase_quantity_cannot_exceed_vendor_available_quantity(): void
    {
        $admin = $this->createAdmin();
        [, , $product] = $this->createApprovedVendor();

        $response = $this->actingAs($admin)->post(route('admin.purchases.store'), [
            'product_id' => $product->id,
            'quantity' => 101,
            'unit_price' => 90,
            'purchase_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('vendor_purchases', 0);
    }

    public function test_purchase_subtotal_is_calculated_by_server(): void
    {
        $admin = $this->createAdmin();
        [, , $product] = $this->createApprovedVendor();

        $response = $this->actingAs($admin)->post(route('admin.purchases.store'), [
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 90,
            'subtotal' => 1,
            'purchase_date' => now()->toDateString(),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('vendor_purchases', [
            'product_id' => $product->id,
            'subtotal' => '450.00',
        ]);
    }

    public function test_vendor_cannot_access_admin_procurement(): void
    {
        [$vendor] = $this->createApprovedVendor();

        $this->actingAs($vendor)
            ->get(route('admin.purchases.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_use_invalid_purchase_status(): void
    {
        $admin = $this->createAdmin();
        [, $profile, $product] = $this->createApprovedVendor();

        $purchase = VendorPurchase::create([
            'purchase_number' => 'VP-TEST-000001',
            'vendor_profile_id' => $profile->id,
            'product_id' => $product->id,
            'created_by' => $admin->id,
            'quantity' => 10,
            'unit_price' => 90,
            'subtotal' => 900,
            'status' => 'ordered',
            'payment_status' => 'unpaid',
            'purchase_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->patch(
            route('admin.purchases.status.update', $purchase),
            ['status' => 'completed', 'payment_status' => 'unpaid']
        );

        $response->assertSessionHasErrors('status');
    }
}
