<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class VendorController extends Controller
{
    public function showRegister()
    {
        return view('vendor.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
             'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],


            'business_name' => ['required', 'string', 'max:255'],
            'business_address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:10'],

            'gst_number' => [
                'nullable',
                'string',
                'max:20',
                'unique:vendor_profiles,gst_number',
            ],

            'identity_proof' => [
                'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120',
            ],
            'address_proof' => [
                'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120',
            ],
            'business_proof' => [
                'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120',
            ],
            'gst_certificate' => [
                'nullable',
                'required_with:gst_number',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],

            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => 'This email address is already registered. Please use another email.',
            'gst_number.unique' => 'This GST number is already registered.',
        ]);

        $documentTypes = [
            'identity_proof' => 'identity',
            'address_proof' => 'address',
            'business_proof' => 'business',
            'gst_certificate' => 'gst',
        ];

        $storedPaths = [];

        try {
            // Store uploaded files outside the public web directory.
            foreach ($documentTypes as $field => $type) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $path = $file->store('vendor-documents', 'local');

                    if (!$path) {
                        throw new \RuntimeException('Document upload failed.');
                    }

                    $storedPaths[] = $path;
                    $validated['_documents'][$field] = [
                        'type' => $type,
                        'path' => $path,
                        'filename' => $file->getClientOriginalName(),
                        'mime' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ];
                }
            }

            $user = DB::transaction(function () use ($validated) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => $validated['password'],
                    'role' => 'vendor',
                    'is_active' => 1,
                ]);

                $profile = VendorProfile::create([
                    'user_id' => $user->id,
                    'business_name' => $validated['business_name'],
                    'phone' => $validated['phone'],
                    'business_address' => $validated['business_address'],
                    'city' => $validated['city'],
                    'state' => $validated['state'],
                    'pincode' => $validated['pincode'],
                    'gst_number' => $validated['gst_number'] ?? null,
                    'approval_status' => 'pending',
                ]);

                foreach ($validated['_documents'] ?? [] as $document) {
                    $profile->documents()->create([
                        'document_type' => $document['type'],
                        'file_path' => $document['path'],
                        'original_filename' => $document['filename'],
                        'mime_type' => $document['mime'],
                        'file_size' => $document['size'],
                        'review_status' => 'pending',
                    ]);
                }

                return $user;
            });

        } catch (\Throwable $exception) {

    // Clean up uploaded documents if registration fails.
    foreach ($storedPaths as $path) {
        Storage::disk('local')->delete($path);
    }

    // Handle duplicate database entries without exposing SQL errors.
    if (
        $exception instanceof QueryException &&
        (int) ($exception->errorInfo[1] ?? 0) === 1062
    ) {
        $databaseError = $exception->getMessage();

        if (str_contains($databaseError, 'users_email')) {
            return back()
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                ]))
                ->withErrors([
                    'email' => 'This email address is already registered. Please use another email.',
                ])
                ->with('error', 'This email address is already registered.');
        }

        if (str_contains($databaseError, 'vendor_profiles_gst_number')) {
            return back()
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                ]))
                ->withErrors([
                    'gst_number' => 'This GST number is already registered.',
                ])
                ->with('error', 'This GST number is already registered.');
        }

        return back()
            ->withInput($request->except([
                'password',
                'password_confirmation',
            ]))
            ->with('error', 'Some registration information is already in use. Please check your details.');
    }

    throw $exception;
}

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('vendor.dashboard')
            ->with(
                'success',
                'Your vendor account has been created. It is awaiting admin verification.'
            );
    }

    public function showLogin()
    {
        return view('vendor.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['role'] = 'vendor';
        $credentials['is_active'] = 1;

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('vendor.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'Invalid vendor credentials or your account is inactive.',
        ]);
    }

    public function dashboard()
{
    $vendor = auth()->user()->load('vendorProfile');

    $productStats = [
        'total' => 0,
        'pending' => 0,
        'approved' => 0,
    ];

    if ($vendor->vendorProfile?->approval_status === 'approved') {
        $products = $vendor->vendorProfile->products();

        $productStats = [
            'total' => (clone $products)->count(),
            'pending' => (clone $products)->where('status', 'pending')->count(),
            'approved' => (clone $products)->where('status', 'approved')->count(),
        ];
    }

    return view('vendor.dashboard', compact('vendor', 'productStats'));
}

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('vendor.login')
            ->with('success', 'You have been logged out successfully.');
    }
}