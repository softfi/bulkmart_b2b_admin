
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Sign In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            background: #0b1422;
            color: #edf2f8;
            font-family: Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            padding: 32px;
            background: #111c2d;
            border: 1px solid #34445b;
            border-radius: 14px;
            box-shadow: 0 18px 50px rgba(0,0,0,.25);
        }

        .login-card h1 {
            color: #f5f7fa;
            font-size: 25px;
            font-weight: 800;
        }

        .login-card p,
        .login-card small {
            color: #aab7c8;
        }

        .login-card label {
            color: #d5deea;
            font-weight: 650;
        }

        .login-card .form-control {
            min-height: 46px;
            background: #0b1422;
            border: 1px solid #40516a;
            color: #fff;
        }

        .login-card .form-control:focus {
            border-color: #d6b574;
            box-shadow: 0 0 0 .2rem rgba(214,181,116,.12);
        }

        .login-card .btn {
            min-height: 45px;
            background: #d6b574;
            border-color: #d6b574;
            color: #101827;
            font-weight: 800;
        }

        .login-card .btn:hover {
            background: #e5c98f;
        }

        .brand {
            margin-bottom: 28px;
            color: #e5c98f;
            font-size: 13px;
            font-weight: 850;
            letter-spacing: 1.5px;
        }

        a {
            color: #e5c98f;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand">WHOLESALE.</div>

    <h1>Customer Sign In</h1>
    <p class="mb-4">Sign in securely using a one-time verification code sent to your email.</p>

    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('customer.otp.send') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                value="{{ old('email', session('otp_email')) }}"
                maxlength="255"
                autocomplete="email"
                required
            >
        </div>

        <button type="submit" class="btn w-100">
            Send Verification Code
        </button>
    </form>

    @if(session('otp_sent'))
        <hr class="border-secondary my-4">

        <form method="POST" action="{{ route('customer.otp.verify') }}">
            @csrf

            <input
                type="hidden"
                name="email"
                value="{{ session('otp_email') }}"
            >

            <div class="mb-3">
                <label for="code" class="form-label">6-digit verification code</label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    class="form-control"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    autocomplete="one-time-code"
                    required
                >
            </div>

            <small class="d-block mb-3">
                The code expires after 10 minutes. You can request another code after 60 seconds.
            </small>

            <button type="submit" class="btn w-100">
                Verify and Sign In
            </button>
        </form>
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('storefront.index') }}" class="text-decoration-none">
            Back to shop
        </a>
    </div>
</div>
</body>
</html>
