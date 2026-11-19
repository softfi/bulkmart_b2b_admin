<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorProductTest extends TestCase
{
    use RefreshDatabase;

    private function createVendor(string $approvalStatus = 'approved'): array
    {
        $user = User::create([
            'name' => 'Test Vendor',
            'email' => uniqid('vendor_') . '@example.com',
            'password' => 'StrongPassword123!',
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $profile = VendorProfile::create([
            'user_id' => $user->id,
            'business_name' => 'Test Wholesale Business',
            'phone' => '9876543210',
            'business_address' => 'Test Business Address',
            'approval_status' => $approvalStatus,
        ]);

        return [$user, $profile];
    }

    private function validProductData(): array
    {
        return [
            'name' => 'Premium Sugar',
            'description' => 'Wholesale quality sugar',
            'category' => 'Grocery',
            'unit' => 'kg',
            'price_per_unit' => 90,
            'available_quantity' => 100,
            'minimum_order_quantity' => 10,
        ];
    }

    public function test_approved_vendor_can_open_product_listing(): void
    {
        [$user] = $this->createVendor();

        $this->actingAs($user)
            ->get(route('vendor.products.index'))
            ->assertOk();
    }

    public function test_pending_vendor_cannot_manage_products(): void
    {
        [$user] = $this->createVendor('pending');

        $this->actingAs($user)
            ->get(route('vendor.products.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('vendor.products.create'))
            ->assertForbidden();
    }

    public function test_approved_vendor_can_create_product_with_pending_status(): void
    {
        [$user] = $this->createVendor();

        $this->actingAs($user)
            ->post(route('vendor.products.store'), $this->validProductData())
            ->assertRedirect(route('vendor.products.index'));

        $this->assertDatabaseHas('products', [
            'vendor_profile_id' => $user->vendorProfile->id,
            'name' => 'Premium Sugar',
            'status' => 'pending',
            'price_per_unit' => 90,
            'available_quantity' => 100,
            'minimum_order_quantity' => 10,
        ]);
    }

    public function test_minimum_order_quantity_cannot_exceed_available_quantity(): void
    {
        [$user] = $this->createVendor();

        $data = $this->validProductData();
        $data['minimum_order_quantity'] = 150;

        $this->actingAs($user)
            ->from(route('vendor.products.create'))
            ->post(route('vendor.products.store'), $data)
            ->assertSessionHasErrors('minimum_order_quantity');

        $this->assertDatabaseCount('products', 0);
    }

    public function test_vendor_cannot_update_or_delete_another_vendors_product(): void
    {
        [$owner, $ownerProfile] = $this->createVendor();
        [$otherVendor] = $this->createVendor();

        $product = Product::create([
            'vendor_profile_id' => $ownerProfile->id,
            'name' => 'Owner Sugar',
            'slug' => 'owner-sugar',
            'description' => 'Owner product',
            'category' => 'Grocery',
            'unit' => 'kg',
            'price_per_unit' => 90,
            'available_quantity' => 100,
            'minimum_order_quantity' => 10,
            'status' => 'pending',
        ]);

        $this->actingAs($otherVendor)
            ->put(route('vendor.products.update', $product), $this->validProductData())
            ->assertNotFound();

        $this->actingAs($otherVendor)
            ->delete(route('vendor.products.destroy', $product))
            ->assertNotFound();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Owner Sugar',
        ]);
    }

    
public function test_editing_product_resets_approval_and_records_vendor_resubmission(): void
{
    [$user, $profile] = $this->createVendor();

    $admin = User::create([
        'name' => 'Test Admin',
        'email' => 'resubmission-admin@example.com',
        'password' => 'StrongPassword123!',
        'role' => 'admin',
        'is_active' => true,
    ]);

    $product = Product::create([
        'vendor_profile_id' => $profile->id,
        'name' => 'Old Sugar',
        'slug' => 'old-sugar',
        'category' => 'Grocery',
        'unit' => 'kg',
        'price_per_unit' => 80,
        'available_quantity' => 100,
        'minimum_order_quantity' => 10,
        'status' => 'approved',
        'admin_note' => 'Previously approved',
        'reviewed_by' => $admin->id,
        'reviewed_at' => now(),
    ]);

    $this->actingAs($user)
        ->put(route('vendor.products.update', $product), [
            'name' => 'Updated Sugar',
            'category' => 'Grocery',
            'unit' => 'kg',
            'price_per_unit' => 95,
            'available_quantity' => 120,
            'minimum_order_quantity' => 10,
        ])
        ->assertRedirect(route('vendor.products.index'));

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Sugar',
        'status' => 'pending',
        'admin_note' => null,
        'reviewed_by' => null,
        'reviewed_at' => null,
    ]);

    $this->assertDatabaseHas('product_status_histories', [
        'product_id' => $product->id,
        'reviewed_by' => null,
        'changed_by' => $user->id,
        'previous_status' => 'approved',
        'new_status' => 'pending',
    ]);
}

public function test_deleting_product_preserves_product_and_history_as_soft_deleted(): void
{
    [$user, $profile] = $this->createVendor();

    $product = Product::create([
        'vendor_profile_id' => $profile->id,
        'name' => 'Archived Sugar',
        'slug' => 'archived-sugar',
        'category' => 'Grocery',
        'unit' => 'kg',
        'price_per_unit' => 90,
        'available_quantity' => 100,
        'minimum_order_quantity' => 10,
        'status' => 'pending',
    ]);

    $history = $product->statusHistories()->create([
        'changed_by' => $user->id,
        'previous_status' => 'pending',
        'new_status' => 'pending',
        'reason' => 'Vendor resubmitted product.',
    ]);

    $this->actingAs($user)
        ->delete(route('vendor.products.destroy', $product))
        ->assertRedirect(route('vendor.products.index'));

    $this->assertSoftDeleted('products', [
        'id' => $product->id,
    ]);

    $this->assertDatabaseHas('product_status_histories', [
        'id' => $history->id,
        'product_id' => $product->id,
    ]);
}

public function test_vendor_cannot_access_another_vendors_soft_deleted_product(): void
{
    [$owner, $ownerProfile] = $this->createVendor();
    [$otherVendor] = $this->createVendor();

    $product = Product::create([
        'vendor_profile_id' => $ownerProfile->id,
        'name' => 'Deleted Sugar',
        'slug' => 'deleted-sugar',
        'category' => 'Grocery',
        'unit' => 'kg',
        'price_per_unit' => 90,
        'available_quantity' => 100,
        'minimum_order_quantity' => 10,
        'status' => 'pending',
    ]);

    $product->delete();

    $this->actingAs($otherVendor)
        ->get(route('vendor.products.edit', $product->id))
        ->assertNotFound();
}

}
