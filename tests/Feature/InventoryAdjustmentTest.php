<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryAdjustment;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorPurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function createVendor(): array
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
            'reserved_quantity' => 0,
            'status' => 'approved',
        ]);

        return [$vendor, $profile, $product];
    }

    private function createInventoryBatch(User $admin): array
    {
        [, $profile, $product] = $this->createVendor();

        $purchase = VendorPurchase::create([
            'purchase_number' => 'VP-TEST-' . uniqid(),
            'vendor_profile_id' => $profile->id,
            'product_id' => $product->id,
            'created_by' => $admin->id,
            'quantity' => 20,
            'unit_price' => 90,
            'subtotal' => 1800,
            'status' => 'received',
            'payment_status' => 'unpaid',
            'purchase_date' => now()->toDateString(),
        ]);

        $receipt = GoodsReceipt::create([
            'receipt_number' => 'GR-TEST-' . uniqid(),
            'vendor_purchase_id' => $purchase->id,
            'received_by' => $admin->id,
            'received_at' => now(),
            'notes' => 'Test goods receipt',
        ]);

        $receiptItem = GoodsReceiptItem::create([
            'goods_receipt_id' => $receipt->id,
            'product_id' => $product->id,
            'quantity_received' => 20,
            'unit_cost' => 90,
            'subtotal' => 1800,
        ]);

        $batch = InventoryBatch::create([
            'goods_receipt_item_id' => $receiptItem->id,
            'product_id' => $product->id,
            'vendor_profile_id' => $profile->id,
            'vendor_purchase_id' => $purchase->id,
            'quantity_received' => 20,
            'quantity_remaining' => 20,
            'unit_cost' => 90,
            'received_at' => now(),
        ]);

        return [$product, $batch];
    }

    public function test_admin_can_increase_inventory_batch_stock(): void
    {
        $admin = $this->createAdmin();
        [$product, $batch] = $this->createInventoryBatch($admin);

        $response = $this->actingAs($admin)->post(
            route('admin.inventory.adjustments.store', $batch),
            [
                'adjustment_type' => 'increase',
                'quantity' => 5,
                'reason' => 'Additional stock found during physical counting.',
            ]
        );

        $response->assertRedirect(route('admin.inventory.show', $product->id));

        $this->assertDatabaseHas('inventory_batches', [
            'id' => $batch->id,
            'quantity_received' => '20.000',
            'quantity_remaining' => '25.000',
        ]);

        $this->assertDatabaseHas('inventory_adjustments', [
            'inventory_batch_id' => $batch->id,
            'adjusted_by' => $admin->id,
            'adjustment_type' => 'increase',
            'quantity' => '5.000',
            'quantity_before' => '20.000',
            'quantity_after' => '25.000',
        ]);
    }

    public function test_admin_can_decrease_inventory_batch_stock(): void
    {
        $admin = $this->createAdmin();
        [$product, $batch] = $this->createInventoryBatch($admin);

        $response = $this->actingAs($admin)->post(
            route('admin.inventory.adjustments.store', $batch),
            [
                'adjustment_type' => 'decrease',
                'quantity' => 7,
                'reason' => 'Damaged stock discovered during inspection.',
            ]
        );

        $response->assertRedirect(route('admin.inventory.show', $product->id));

        $this->assertDatabaseHas('inventory_batches', [
            'id' => $batch->id,
            'quantity_received' => '20.000',
            'quantity_remaining' => '13.000',
        ]);

        $this->assertDatabaseHas('inventory_adjustments', [
            'inventory_batch_id' => $batch->id,
            'adjusted_by' => $admin->id,
            'adjustment_type' => 'decrease',
            'quantity' => '7.000',
            'quantity_before' => '20.000',
            'quantity_after' => '13.000',
        ]);
    }

    public function test_admin_cannot_decrease_stock_below_zero(): void
    {
        $admin = $this->createAdmin();
        [, $batch] = $this->createInventoryBatch($admin);

        $response = $this->actingAs($admin)->post(
            route('admin.inventory.adjustments.store', $batch),
            [
                'adjustment_type' => 'decrease',
                'quantity' => 25,
                'reason' => 'Invalid stock reduction.',
            ]
        );

        $response->assertSessionHasErrors('quantity');

        $this->assertDatabaseHas('inventory_batches', [
            'id' => $batch->id,
            'quantity_received' => '20.000',
            'quantity_remaining' => '20.000',
        ]);

        $this->assertDatabaseCount('inventory_adjustments', 0);
    }

    public function test_zero_adjustment_quantity_is_rejected(): void
    {
        $admin = $this->createAdmin();
        [, $batch] = $this->createInventoryBatch($admin);

        $response = $this->actingAs($admin)->post(
            route('admin.inventory.adjustments.store', $batch),
            [
                'adjustment_type' => 'increase',
                'quantity' => 0,
                'reason' => 'Invalid zero adjustment.',
            ]
        );

        $response->assertSessionHasErrors('quantity');

        $this->assertDatabaseHas('inventory_batches', [
            'id' => $batch->id,
            'quantity_remaining' => '20.000',
        ]);

        $this->assertDatabaseCount('inventory_adjustments', 0);
    }

    public function test_adjustment_records_complete_audit_information(): void
    {
        $admin = $this->createAdmin();
        [, $batch] = $this->createInventoryBatch($admin);

        $reason = 'Physical stock count correction.';

        $this->actingAs($admin)->post(
            route('admin.inventory.adjustments.store', $batch),
            [
                'adjustment_type' => 'increase',
                'quantity' => 3.5,
                'reason' => $reason,
            ]
        )->assertRedirect();

        $adjustment = InventoryAdjustment::where(
            'inventory_batch_id',
            $batch->id
        )->firstOrFail();

        $this->assertSame($admin->id, $adjustment->adjusted_by);
        $this->assertSame('increase', $adjustment->adjustment_type);
        $this->assertEquals(3.5, (float) $adjustment->quantity);
        $this->assertEquals(20, (float) $adjustment->quantity_before);
        $this->assertEquals(23.5, (float) $adjustment->quantity_after);
        $this->assertSame($reason, $adjustment->reason);
    }

    public function test_adjustment_preserves_three_decimal_places(): void
    {
        $admin = $this->createAdmin();
        [$product, $batch] = $this->createInventoryBatch($admin);

        $response = $this->actingAs($admin)->post(
            route('admin.inventory.adjustments.store', $batch),
            [
                'adjustment_type' => 'increase',
                'quantity' => '0.125',
                'reason' => 'Precision test for fractional inventory.',
            ]
        );

        $response->assertRedirect(route('admin.inventory.show', $product->id));

        $this->assertDatabaseHas('inventory_batches', [
            'id' => $batch->id,
            'quantity_remaining' => '20.125',
        ]);

        $this->assertDatabaseHas('inventory_adjustments', [
            'inventory_batch_id' => $batch->id,
            'adjusted_by' => $admin->id,
            'adjustment_type' => 'increase',
            'quantity' => '0.125',
            'quantity_before' => '20.000',
            'quantity_after' => '20.125',
        ]);
    }

    public function test_vendor_cannot_adjust_inventory(): void
    {
        $admin = $this->createAdmin();
        [$vendor] = $this->createVendor();
        [, $batch] = $this->createInventoryBatch($admin);

        $response = $this->actingAs($vendor)->post(
            route('admin.inventory.adjustments.store', $batch),
            [
                'adjustment_type' => 'increase',
                'quantity' => 5,
                'reason' => 'Unauthorized adjustment attempt.',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('inventory_batches', [
            'id' => $batch->id,
            'quantity_remaining' => '20.000',
        ]);

        $this->assertDatabaseCount('inventory_adjustments', 0);
    }
}
