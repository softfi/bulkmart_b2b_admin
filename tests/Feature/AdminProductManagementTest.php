<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductStatusHistory;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Test Administrator',
            'email' => uniqid('admin_') . '@example.com',
            'password' => 'StrongPassword123!',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function createVendor(): array
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
            'approval_status' => 'approved',
        ]);

        return [$user, $profile];
    }

    private function createProduct(
        VendorProfile $profile,
        string $status = 'pending'
    ): Product {
        return Product::create([
            'vendor_profile_id' => $profile->id,
            'name' => 'Premium Sugar',
            'slug' => uniqid('premium-sugar-'),
            'description' => 'Wholesale quality sugar',
            'category' => 'Grocery',
            'unit' => 'kg',
            'price_per_unit' => 90,
            'available_quantity' => 100,
            'minimum_order_quantity' => 10,
            'status' => $status,
        ]);
    }

    public function test_admin_can_view_product_list(): void
    {
        $admin = $this->createAdmin();

        [, $profile] = $this->createVendor();

        $this->createProduct($profile);

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk();
    }

    public function test_admin_can_view_product_details(): void
    {
        $admin = $this->createAdmin();

        [, $profile] = $this->createVendor();

        $product = $this->createProduct($profile);

        $this->actingAs($admin)
            ->get(route('admin.products.show', $product))
            ->assertOk();
    }

    public function test_admin_can_approve_pending_product(): void
    {
        $admin = $this->createAdmin();

        [, $profile] = $this->createVendor();

        $product = $this->createProduct($profile);

        $this->actingAs($admin)
            ->patch(route('admin.products.status.update', $product), [
                'status' => 'approved',
            ])
            ->assertRedirect(route('admin.products.show', $product));
        
        $this->assertDatabaseHas('product_status_histories', [
            'product_id' => $product->id,
            'reviewed_by' => $admin->id,
            'changed_by' => $admin->id,
            'new_status' => 'approved',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('product_status_histories', [
            'product_id' => $product->id,
            'reviewed_by' => $admin->id,
            'previous_status' => 'pending',
            'new_status' => 'approved',
        ]);
    }

    public function test_rejection_requires_a_reason(): void
    {
        $admin = $this->createAdmin();

        [, $profile] = $this->createVendor();

        $product = $this->createProduct($profile);

        $this->actingAs($admin)
            ->from(route('admin.products.show', $product))
            ->patch(route('admin.products.status.update', $product), [
                'status' => 'rejected',
            ])
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseCount('product_status_histories', 0);
    }

    public function test_suspension_requires_a_reason(): void
    {
        $admin = $this->createAdmin();

        [, $profile] = $this->createVendor();

        $product = $this->createProduct($profile, 'approved');

        $this->actingAs($admin)
            ->patch(route('admin.products.status.update', $product), [
                'status' => 'suspended',
            ])
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_rejection_records_reason_and_history(): void
    {
        $admin = $this->createAdmin();

        [, $profile] = $this->createVendor();

        $product = $this->createProduct($profile);

        $reason = 'Product pricing needs verification.';

        $this->actingAs($admin)
            ->patch(route('admin.products.status.update', $product), [
                'status' => 'rejected',
                'reason' => $reason,
            ])
            ->assertRedirect(route('admin.products.show', $product));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'rejected',
            'admin_note' => $reason,
            'reviewed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('product_status_histories', [
            'product_id' => $product->id,
            'reviewed_by' => $admin->id,
            'previous_status' => 'pending',
            'new_status' => 'rejected',
            'reason' => $reason,
        ]);
    }

    public function test_admin_cannot_select_the_existing_status(): void
    {
        $admin = $this->createAdmin();

        [, $profile] = $this->createVendor();

        $product = $this->createProduct($profile, 'approved');

        $this->actingAs($admin)
            ->patch(route('admin.products.status.update', $product), [
                'status' => 'approved',
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('product_status_histories', 0);
    }


    public function test_vendor_cannot_access_admin_product_management(): void
    {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $profile = VendorProfile::create([
            'user_id' => $vendor->id,
            'business_name' => 'Test Supplier',
            'phone' => '9876543210',
            'business_address' => 'Lucknow',
            'approval_status' => 'approved',
        ]);

        $product = $this->createProduct($profile);

        $this->actingAs($vendor)
            ->get(route('admin.products.index'))
            ->assertForbidden();

        $this->actingAs($vendor)
            ->get(route('admin.products.show', $product))
            ->assertForbidden();
    }


    public function test_admin_image_preview_returns_404_when_product_has_no_image(): void
    {
        $admin = $this->createAdmin();

        [, $profile] = $this->createVendor();

        $product = $this->createProduct($profile);

        $this->actingAs($admin)
            ->get(route('admin.products.image', $product))
            ->assertNotFound();
    }
}
