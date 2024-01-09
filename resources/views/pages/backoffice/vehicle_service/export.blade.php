<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
</head>
<style>
    table {
        widows: 100%;
        width: 100%;
        border-collapse: collapse;
    }

    table thead th {
        background-color: #46ce5f;
        color: #000;
        padding: 5px;
        text-align: center;

    }
</style>
{{-- <?php echo json_encode($data); die; ?> --}}

<body>
    <table border="1">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Keterangan</th>
                <th>NOPOL</th>
                <th>Jumlah Pengeluaran</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                <tr>
                    <td>{{ $item->date }}</td>  
                    <td>{{ $item->vehicleServiceDetail->spending_category->spending_category ?? '-' }}</td>
                    <td>{{ $item->vehicleServiceDetail->description ?? '-' }}</td>
                    <td>{{ $item->vehicle->license_plate ?? '-' }}</td>
                    <td>{{ toThousand($item->vehicleServiceDetail->amount_of_expenditure ?? '0') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
