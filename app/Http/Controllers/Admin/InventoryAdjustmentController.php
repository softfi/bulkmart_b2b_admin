<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\InventoryBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryAdjustmentController extends Controller
{
    /**
     * Record a stock adjustment against an inventory batch.
     */
    public function store(Request $request, InventoryBatch $batch)
    {
        $validated = $request->validate([
            'adjustment_type' => [
                'required',
                'in:increase,decrease',
            ],
            'quantity' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,3',
                'max:999999999',
            ],
            'reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        DB::transaction(function () use ($validated, $request, $batch) {
            // Lock the batch to prevent concurrent adjustments from
            // calculating their changes using the same old quantity.
            $lockedBatch = InventoryBatch::whereKey($batch->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Store calculations as thousandths to avoid floating-point
            // arithmetic errors with decimal inventory quantities.
            $before = (int) round(
                (float) $lockedBatch->quantity_remaining * 1000
            );

            $quantity = (int) round(
                (float) $validated['quantity'] * 1000
            );

            // DECIMAL(12,3) supports up to 999,999,999.999.
            $maximumQuantity = 999999999999;

            if ($before < 0 || $before > $maximumQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'The existing batch quantity is invalid. Please investigate this batch before adjusting it.',
                ]);
            }

            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Adjustment quantity must be greater than zero.',
                ]);
            }

            if ($validated['adjustment_type'] === 'decrease') {
                if ($quantity > $before) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Adjustment quantity cannot exceed the batch remaining stock.',
                    ]);
                }

                $after = $before - $quantity;
            } else {
                if ($quantity > ($maximumQuantity - $before)) {
                    throw ValidationException::withMessages([
                        'quantity' => 'The adjusted stock exceeds the maximum supported quantity.',
                    ]);
                }

                $after = $before + $quantity;
            }

            $beforeDecimal = number_format($before / 1000, 3, '.', '');
            $afterDecimal = number_format($after / 1000, 3, '.', '');

            // Update the batch and create its audit record in the same
            // transaction. Either both operations succeed or neither does.
            $lockedBatch->update([
                'quantity_remaining' => $afterDecimal,
            ]);

            InventoryAdjustment::create([
                'inventory_batch_id' => $lockedBatch->id,
                'adjusted_by' => $request->user()->id,
                'adjustment_type' => $validated['adjustment_type'],
                'quantity' => number_format($quantity / 1000, 3, '.', ''),
                'quantity_before' => $beforeDecimal,
                'quantity_after' => $afterDecimal,
                'reason' => trim($validated['reason']),
            ]);
        });

        return redirect()
            ->route('admin.inventory.show', $batch->product_id)
            ->with('success', 'Inventory adjustment recorded successfully.');
    }
}
