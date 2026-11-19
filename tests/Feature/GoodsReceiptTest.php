<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryBatch;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorPurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoodsReceiptTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function createPurchase(
        User $admin,
        float $orderedQuantity = 20,
        float $reservedQuantity = 20
    ): array {
        $vendor = User::factory()->create([
            'role' => 'vendor',
            'is_active' => true,
        ]);

        $profile = VendorProfile::create([
            'user_id' => $vendor->id,
            'business_name' => 'Receipt Test Supplier',
            'phone' => '9876543210',
            'business_address' => 'Lucknow',
            'approval_status' => 'approved',
        ]);

        $product = Product::create([
            'vendor_profile_id' => $profile->id,
            'name' => 'Receipt Test Sugar',
            'slug' => 'receipt-test-sugar-' . uniqid(),
            'description' => 'Wholesale sugar',
            'category' => 'Grocery',
            'unit' => 'kg',
            'price_per_unit' => 90,
            'available_quantity' => 100,
            'minimum_order_quantity' => 1,
            'status' => 'approved',
        ]);

        // reserved_quantity is deliberately set explicitly because it
        // is maintained by the procurement workflow.
        $product->forceFill([
            'reserved_quantity' => $reservedQuantity,
        ])->save();

        $purchase = VendorPurchase::create([
            'purchase_number' => 'VP-RECEIPT-' . uniqid(),
            'vendor_profile_id' => $profile->id,
            'product_id' => $product->id,
            'created_by' => $admin->id,
            'quantity' => $orderedQuantity,
            'unit_price' => 90,
            'subtotal' => round($orderedQuantity * 90, 2),
            'status' => 'ordered',
            'payment_status' => 'unpaid',
            'purchase_date' => now()->toDateString(),
        ]);

        return [$vendor, $profile, $product, $purchase];
    }

    public function test_admin_can_record_a_partial_goods_receipt(): void
    {
        $admin = $this->createAdmin();

        [, , $product, $purchase] = $this->createPurchase($admin);

        $response = $this->actingAs($admin)->post(
            route('admin.purchases.receive.store', $purchase),
            [
                'quantity_received' => 5,
                'received_at' => now()->format('Y-m-d\TH:i'),
                'notes' => 'First partial delivery.',
            ]
        );

        $response->assertRedirect(
            route('admin.purchases.show', $purchase)
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vendor_purchases', [
            'id' => $purchase->id,
            'status' => 'partially_received',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'reserved_quantity' => '15.000',
        ]);

        $this->assertDatabaseHas('goods_receipt_items', [
            'product_id' => $product->id,
            'quantity_received' => '5.000',
            'unit_cost' => '90.00',
            'subtotal' => '450.00',
        ]);

        $this->assertDatabaseHas('inventory_batches', [
            'product_id' => $product->id,
            'vendor_purchase_id' => $purchase->id,
            'quantity_received' => '5.000',
            'quantity_remaining' => '5.000',
            'unit_cost' => '90.00',
        ]);

        $this->assertDatabaseCount('goods_receipts', 1);
        $this->assertDatabaseCount('goods_receipt_items', 1);
        $this->assertDatabaseCount('inventory_batches', 1);
    }

    public function test_admin_can_complete_a_purchase_with_a_goods_receipt(): void
    {
        $admin = $this->createAdmin();

        [, , $product, $purchase] = $this->createPurchase($admin);

        $response = $this->actingAs($admin)->post(
            route('admin.purchases.receive.store', $purchase),
            [
                'quantity_received' => 20,
                'received_at' => now()->format('Y-m-d\TH:i'),
                'notes' => 'Complete delivery.',
            ]
        );

        $response->assertRedirect(
            route('admin.purchases.show', $purchase)
        );

        $this->assertDatabaseHas('vendor_purchases', [
            'id' => $purchase->id,
            'status' => 'received',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'reserved_quantity' => '0.000',
        ]);

        $this->assertDatabaseHas('inventory_batches', [
            'product_id' => $product->id,
            'vendor_purchase_id' => $purchase->id,
            'quantity_received' => '20.000',
            'quantity_remaining' => '20.000',
        ]);

        $this->assertDatabaseCount('goods_receipts', 1);
        $this->assertDatabaseCount('inventory_batches', 1);
    }

    public function test_admin_cannot_receive_more_than_the_outstanding_quantity(): void
    {
        $admin = $this->createAdmin();

        [, , $product, $purchase] = $this->createPurchase($admin);

        $response = $this->actingAs($admin)->post(
            route('admin.purchases.receive.store', $purchase),
            [
                'quantity_received' => 21,
                'received_at' => now()->format('Y-m-d\TH:i'),
            ]
        );

        $response->assertSessionHasErrors('quantity_received');

        $this->assertDatabaseHas('vendor_purchases', [
            'id' => $purchase->id,
            'status' => 'ordered',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'reserved_quantity' => '20.000',
        ]);

        $this->assertDatabaseCount('goods_receipts', 0);
        $this->assertDatabaseCount('goods_receipt_items', 0);
        $this->assertDatabaseCount('inventory_batches', 0);
    }

    public function test_admin_cannot_receive_more_than_reserved_vendor_stock(): void
    {
        $admin = $this->createAdmin();

        [, , $product, $purchase] = $this->createPurchase(
            $admin,
            20,
            5
        );

        $response = $this->actingAs($admin)->post(
            route('admin.purchases.receive.store', $purchase),
            [
                'quantity_received' => 10,
                'received_at' => now()->format('Y-m-d\TH:i'),
            ]
        );

        $response->assertSessionHasErrors('quantity_received');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'reserved_quantity' => '5.000',
        ]);

        $this->assertDatabaseHas('vendor_purchases', [
            'id' => $purchase->id,
            'status' => 'ordered',
        ]);

        $this->assertDatabaseCount('goods_receipts', 0);
        $this->assertDatabaseCount('inventory_batches', 0);
    }

    public function test_vendor_cannot_record_goods_receipts(): void
    {
        $admin = $this->createAdmin();

        [$vendor, , $product, $purchase] = $this->createPurchase($admin);

        $response = $this->actingAs($vendor)->post(
            route('admin.purchases.receive.store', $purchase),
            [
                'quantity_received' => 5,
                'received_at' => now()->format('Y-m-d\TH:i'),
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'reserved_quantity' => '20.000',
        ]);

        $this->assertDatabaseCount('goods_receipts', 0);
        $this->assertDatabaseCount('inventory_batches', 0);
    }

    public function test_receipt_records_the_admin_and_receipt_history(): void
    {
        $admin = $this->createAdmin();

        [, , $product, $purchase] = $this->createPurchase($admin);

        $this->actingAs($admin)->post(
            route('admin.purchases.receive.store', $purchase),
            [
                'quantity_received' => 7.5,
                'received_at' => now()->format('Y-m-d\TH:i'),
                'notes' => 'Delivery checked and accepted.',
            ]
        )->assertRedirect();

        $receipt = GoodsReceipt::where(
            'vendor_purchase_id',
            $purchase->id
        )->firstOrFail();

        $this->assertSame($admin->id, $receipt->received_by);
        $this->assertSame('Delivery checked and accepted.', $receipt->notes);

        $this->assertDatabaseHas('goods_receipt_items', [
            'goods_receipt_id' => $receipt->id,
            'product_id' => $product->id,
            'quantity_received' => '7.500',
        ]);

        $this->assertDatabaseHas('vendor_purchase_status_histories', [
            'vendor_purchase_id' => $purchase->id,
            'changed_by' => $admin->id,
            'previous_status' => 'ordered',
            'new_status' => 'partially_received',
        ]);

        $batch = InventoryBatch::where(
            'vendor_purchase_id',
            $purchase->id
        )->firstOrFail();

        $this->assertSame(
            '7.500',
            $batch->quantity_received
        );
    }
}
