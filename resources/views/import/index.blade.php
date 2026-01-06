<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product CSV Import</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
        }

        .container {
            width: 400px;
            margin: 80px auto;
            background: #fff;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 14px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        input[type="file"] {
            width: 100%;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Product CSV Import</h2>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ url('/import') }}">
        @csrf

        <label>Select CSV File</label>
        <input type="file" name="csv" accept=".csv" required>

        <br><br>

        <button type="submit">Upload & Start Import</button>
    </form>
        <br>

<a href="{{ url('/import-history') }}" style="text-decoration:none;">
    <button type="button" style="
        width:100%;
        padding:10px;
        background:#6c757d;
        color:white;
        border:none;
        border-radius:4px;
        cursor:pointer;">
        View Import History
    </button>
</a>

</div>

</body>
</html>
