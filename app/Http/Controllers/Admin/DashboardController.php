<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VendorProfile;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalVendors = VendorProfile::count();

        $pendingVendors = VendorProfile::where(
            'approval_status',
            'pending'
        )->count();

        $underReviewVendors = VendorProfile::where(
            'approval_status',
            'under_review'
        )->count();

        $approvedVendors = VendorProfile::where(
            'approval_status',
            'approved'
        )->count();

        $rejectedVendors = VendorProfile::where(
            'approval_status',
            'rejected'
        )->count();

        $suspendedVendors = VendorProfile::where(
            'approval_status',
            'suspended'
        )->count();

        $activeVendorAccounts = VendorProfile::whereHas('user', function ($query) {
            $query->where('is_active', true);
        })->count();

        $inactiveVendorAccounts = VendorProfile::whereHas('user', function ($query) {
            $query->where('is_active', false);
        })->count();

        $recentVendors = VendorProfile::with('user')
            ->latest()
            ->take(6)
            ->get();

        // Vendor registration trend for the last six months.
        $monthlyRegistrations = [];
        $firstMonth = now()->startOfMonth()->subMonths(5);

        for ($i = 0; $i < 6; $i++) {
            $month = $firstMonth->copy()->addMonths($i);

            $count = VendorProfile::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $monthlyRegistrations[] = [
                'label' => $month->format('M'),
                'count' => $count,
            ];
        }

        $maxMonthlyRegistrations = max(
            array_column($monthlyRegistrations, 'count') ?: [0]
        );

        return view('admin.dashboard', compact(
            'totalVendors',
            'pendingVendors',
            'underReviewVendors',
            'approvedVendors',
            'rejectedVendors',
            'suspendedVendors',
            'activeVendorAccounts',
            'inactiveVendorAccounts',
            'recentVendors',
            'monthlyRegistrations',
            'maxMonthlyRegistrations'
        ));
    }
}
