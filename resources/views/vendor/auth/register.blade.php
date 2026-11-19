<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Registration | B2C</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: #101114;
            color: white;
            font-family: Arial, sans-serif;
            padding: 40px 15px;
        }

        .register-card {
            max-width: 700px;
            margin: auto;
            padding: 35px;
            background: #1b1c21;
            border: 1px solid #303139;
            border-radius: 16px;
        }

        .form-control {
            background: #272830;
            border: 1px solid #41424c;
            color: white;
            padding: 12px;
        }

        .form-control:focus {
            background: #272830;
            color: white;
            border-color: #8b5cf6;
            box-shadow: none;
        }

        .form-control::placeholder {
            color: #999;
        }

        .form-label {
            color: #ddd;
        }

        .btn-register {
            background: #8b5cf6;
            color: white;
            padding: 12px;
            border: none;
            font-weight: 600;
        }

        .btn-register:hover {
            background: #7544df;
            color: white;
        }

        input[type="file"] {
            color: #ccc;
        }

        .help-text {
            color: #999;
            font-size: 13px;
        }
    </style>
</head>

<body>

<div class="register-card">

    <h3 class="text-center mb-2">Become a Vendor</h3>

    <p class="text-center text-secondary mb-4">
        Register your business on our wholesale platform.
    </p>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vendor.register.submit') }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf

        <h5 class="mb-3">Account Details</h5>

        <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" name="name" class="form-control"
                   value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Phone Number *</label>
            <input type="tel" name="phone" class="form-control"
                   value="{{ old('phone') }}" required>
        </div>

        <hr class="border-secondary my-4">

        <h5 class="mb-3">Business Details</h5>

        <div class="mb-3">
            <label class="form-label">Business Name *</label>
            <input type="text" name="business_name" class="form-control"
                   value="{{ old('business_name') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Business Address *</label>
            <textarea name="business_address" class="form-control"
                      rows="3" required>{{ old('business_address') }}</textarea>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">City *</label>
                <input type="text" name="city" class="form-control"
                       value="{{ old('city') }}" required>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">State *</label>
                <input type="text" name="state" class="form-control"
                       value="{{ old('state') }}" required>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Pincode *</label>
                <input type="text" name="pincode" class="form-control"
                       value="{{ old('pincode') }}" maxlength="10" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">GST Number (Optional)</label>
            <input type="text" name="gst_number" class="form-control"
                   value="{{ old('gst_number') }}">
        </div>

        <hr class="border-secondary my-4">

        <h5 class="mb-2">Verification Documents</h5>

        <p class="help-text mb-3">
            Accepted formats: PDF, JPG, JPEG, PNG. Maximum size: 5 MB per file.
        </p>

        <div class="mb-3">
            <label class="form-label">Identity Proof *</label>
            <input type="file" name="identity_proof"
                   class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Address Proof *</label>
            <input type="file" name="address_proof"
                   class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Business Proof *</label>
            <input type="file" name="business_proof"
                   class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>

        <div class="mb-3">
            <label class="form-label">GST Certificate (Required if GST number is provided)</label>
            <input type="file" name="gst_certificate"
                   class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <hr class="border-secondary my-4">

        <h5 class="mb-3">Security</h5>

        <div class="mb-3">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control"
                   minlength="8" required>
        </div>

        <div class="mb-4">
            <label class="form-label">Confirm Password *</label>
            <input type="password" name="password_confirmation"
                   class="form-control" minlength="8" required>
        </div>

        <button type="submit" class="btn btn-register w-100">
            Create Vendor Account
        </button>

    </form>

    <p class="text-center text-secondary mt-4 mb-0">
        Already registered?
        <a href="{{ route('vendor.login') }}" class="text-white">
            Login here
        </a>
    </p>

</div>

</body>
</html>