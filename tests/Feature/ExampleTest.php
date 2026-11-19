<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\VendorDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
//     dump([
//     'environment' => app()->environment(),
//     'database_connection' => config('database.default'),
//     'database_name' => config('database.connections.sqlite.database'),
// ]);
    public function test_vendor_can_register_successfully(): void
    {

        Storage::fake('local');
        $response = $this->post(route('vendor.register.submit'), [
            'name' => 'Test Vendor',
            'email' => 'vendor@example.com',
            'phone' => '9876543210',
            'business_name' => 'Test Wholesale Store',
            'business_address' => '123 Market Road',
            'city' => 'Lucknow',
            'state' => 'Uttar Pradesh',
            'pincode' => '226001',
            'gst_number' => '',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',

            'identity_proof' => \Illuminate\Http\UploadedFile::fake()
                ->create('identity.pdf', 100, 'application/pdf'),

            'address_proof' => \Illuminate\Http\UploadedFile::fake()
                ->create('address.pdf', 100, 'application/pdf'),

            'business_proof' => \Illuminate\Http\UploadedFile::fake()
                ->create('business.pdf', 100, 'application/pdf'),
        ]);

        $user = User::where('email', 'vendor@example.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals('vendor', $user->role);

        $profile = VendorProfile::where('user_id', $user->id)->first();

        $this->assertNotNull($profile);
        $this->assertEquals('pending', $profile->approval_status);
        $this->assertEquals('Lucknow', $profile->city);

        $this->assertDatabaseCount('vendor_documents', 3);
        $documents = VendorDocument::where(
        'vendor_profile_id',
            $profile->id
        )->get();

        $this->assertCount(3, $documents);

        foreach ($documents as $document) {
            Storage::disk('local')->assertExists($document->file_path);
        }

        $response->assertRedirect(route('vendor.dashboard'));
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::create([
            'name' => 'Existing Vendor',
            'email' => 'vendor@example.com',
            'password' => 'StrongPass123!',
            'role' => 'vendor',
            'is_active' => 1,
        ]);

        $response = $this->post(route('vendor.register.submit'), [
            'name' => 'Another Vendor',
            'email' => 'vendor@example.com',
            'phone' => '9876543210',
            'business_name' => 'Another Store',
            'business_address' => '123 Market Road',
            'city' => 'Lucknow',
            'state' => 'Uttar Pradesh',
            'pincode' => '226001',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_invalid_document_type_is_rejected(): void
    {
        Storage::fake('local');

        $response = $this->post(route('vendor.register.submit'), [
            'name' => 'Invalid Vendor',
            'email' => 'invalid@example.com',
            'phone' => '9876543210',
            'business_name' => 'Invalid Store',
            'business_address' => '123 Market Road',
            'city' => 'Lucknow',
            'state' => 'Uttar Pradesh',
            'pincode' => '226001',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',

            'identity_proof' => UploadedFile::fake()
                ->create('identity.exe', 100, 'application/octet-stream'),

            'address_proof' => UploadedFile::fake()
                ->create('address.pdf', 100, 'application/pdf'),

            'business_proof' => UploadedFile::fake()
                ->create('business.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('identity_proof');

        $this->assertDatabaseMissing('users', [
            'email' => 'invalid@example.com',
        ]);

        $this->assertDatabaseCount('vendor_documents', 0);

        Storage::disk('local')->assertDirectoryEmpty('vendor-documents');
    }

    public function test_password_confirmation_mismatch_is_rejected(): void
    {
        $response = $this->post(route('vendor.register.submit'), [
            'name' => 'Password Test Vendor',
            'email' => 'passwordtest@example.com',
            'phone' => '9876543210',
            'business_name' => 'Password Test Store',
            'business_address' => '123 Market Road',
            'city' => 'Lucknow',
            'state' => 'Uttar Pradesh',
            'pincode' => '226001',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'DifferentPass123!',

            'identity_proof' => UploadedFile::fake()
                ->create('identity.pdf', 100, 'application/pdf'),

            'address_proof' => UploadedFile::fake()
                ->create('address.pdf', 100, 'application/pdf'),

            'business_proof' => UploadedFile::fake()
                ->create('business.pdf', 100, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', [
            'email' => 'passwordtest@example.com',
        ]);
    }

    public function test_document_larger_than_5mb_is_rejected(): void
{
    Storage::fake('local');

    $response = $this->post(route('vendor.register.submit'), [
        'name' => 'Large File Vendor',
        'email' => 'largefile@example.com',
        'phone' => '9876543210',
        'business_name' => 'Large File Store',
        'business_address' => '123 Market Road',
        'city' => 'Lucknow',
        'state' => 'Uttar Pradesh',
        'pincode' => '226001',
        'password' => 'StrongPass123!',
        'password_confirmation' => 'StrongPass123!',

        'identity_proof' => UploadedFile::fake()
            ->create('identity.pdf', 6000, 'application/pdf'),

        'address_proof' => UploadedFile::fake()
            ->create('address.pdf', 100, 'application/pdf'),

        'business_proof' => UploadedFile::fake()
            ->create('business.pdf', 100, 'application/pdf'),
    ]);

    $response->assertSessionHasErrors('identity_proof');

    $this->assertDatabaseMissing('users', [
        'email' => 'largefile@example.com',
    ]);

    $this->assertDatabaseCount('vendor_documents', 0);

    Storage::disk('local')->assertDirectoryEmpty('vendor-documents');
}

public function test_inactive_vendor_cannot_access_dashboard(): void
{
    $user = User::create([
        'name' => 'Inactive Vendor',
        'email' => 'inactive@example.com',
        'password' => 'StrongPass123!',
        'role' => 'vendor',
        'is_active' => 0,
    ]);

    $this->actingAs($user)
        ->get(route('vendor.dashboard'))
        ->assertRedirect(route('vendor.login'));

    $this->assertGuest();
}
}