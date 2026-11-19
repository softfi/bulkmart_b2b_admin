<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorDocument;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VendorManagementController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => [
                'nullable',
                Rule::in([
                    'pending',
                    'under_review',
                    'approved',
                    'rejected',
                    'suspended',
                ]),
            ],
        ]);

        $query = VendorProfile::with(['user', 'documents']);

        if (!empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('business_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('gst_number', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($validated['status'])) {
            $query->where('approval_status', $validated['status']);
        }

        $vendors = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.vendors.index', compact('vendors'));
    }

    public function show(VendorProfile $vendor)
    {
        $vendor->load([
            'user',
            'documents.reviewer',
            'statusHistories.reviewer',
        ]);

        return view('admin.vendors.show', compact('vendor'));
    }

    public function updateStatus(Request $request, VendorProfile $vendor)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'under_review',
                    'approved',
                    'rejected',
                    'suspended',
                ]),
            ],
            'reason' => [
                'nullable',
                'string',
                'max:2000',
                'required_if:status,rejected,suspended',
            ],
        ]);

        $adminId = $request->user()->id;

        DB::transaction(function () use ($vendor, $validated, $adminId) {
            // Lock the vendor row to avoid simultaneous conflicting reviews.
            $lockedVendor = VendorProfile::whereKey($vendor->id)
                ->lockForUpdate()
                ->firstOrFail();

            $previousStatus = $lockedVendor->approval_status;
            $newStatus = $validated['status'];

            if ($previousStatus === $newStatus) {
                throw ValidationException::withMessages([
                    'status' => 'The vendor already has this status.',
                ]);
            }

            $isApproved = $newStatus === 'approved';

            $lockedVendor->approval_status = $newStatus;

            // Keep the rejection reason only for rejected applications.
            $lockedVendor->rejection_reason = $newStatus === 'rejected'
                ? $validated['reason']
                : null;

            $lockedVendor->save();

            // A vendor can access vendor features only when approved and active.
            $lockedVendor->user()->update([
                'is_active' => $isApproved,
            ]);

            $lockedVendor->statusHistories()->create([
                'reviewed_by' => $adminId,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'reason' => $validated['reason'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.vendors.show', $vendor)
            ->with('success', 'Vendor status updated successfully.');
    }

    public function downloadDocument(
        VendorProfile $vendor,
        VendorDocument $document
    ) {
        abort_unless(
            $document->vendor_profile_id === $vendor->id,
            404
        );

        abort_unless(
            Storage::disk('local')->exists($document->file_path),
            404
        );

        return Storage::disk('local')->download(
            $document->file_path,
            basename($document->original_filename)
        );
    }
}
