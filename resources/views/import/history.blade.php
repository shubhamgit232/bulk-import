<!DOCTYPE html>
<html>
<head>
    <title>Import History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3>CSV Import History</h3>

    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Total</th>
                <th>Imported</th>
                <th>Updated</th>
                <th>Invalid</th>
                <th>Duplicates</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($imports as $import)
                <tr>
                    <td>{{ $import->id }}</td>
                    <td>{{ $import->total }}</td>
                    <td class="text-success">{{ $import->imported }}</td>
                    <td class="text-primary">{{ $import->updated }}</td>
                    <td class="text-warning">{{ $import->invalid }}</td>
                    <td class="text-danger">{{ $import->duplicates }}</td>
                    <td>{{ $import->created_at->format('d-m-Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No imports yet</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <a href="/import" class="btn btn-secondary">Back to Import</a>
</div>

</body>
</html>
