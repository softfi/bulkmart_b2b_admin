
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | B2C</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #101114;
            font-family: Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 35px;
            background: #1b1c21;
            border: 1px solid #303139;
            border-radius: 16px;
            color: white;
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

        .btn-login {
            background: #8b5cf6;
            color: white;
            padding: 12px;
            border: none;
            font-weight: 600;
        }

        .btn-login:hover {
            background: #7544df;
            color: white;
        }
    </style>
</head>

<body>

<div class="login-card">

    <h3 class="text-center mb-2">B2C Admin</h3>

    <p class="text-center text-secondary mb-4">
        Sign in to access your dashboard
    </p>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('admin.login.submit') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">Email address</label>

            <input
                type="email"
                name="email"
                class="form-control"
                placeholder="admin@example.com"
                value="{{ old('email') }}"
                required
            >
        </div>

        <div class="mb-4">
            <label class="form-label">Password</label>

            <input
                type="password"
                name="password"
                class="form-control"
                placeholder="Enter your password"
                required
            >
        </div>

        <button type="submit" class="btn btn-login w-100">
            Login to Dashboard
        </button>

    </form>

</div>

</body>
</html>
