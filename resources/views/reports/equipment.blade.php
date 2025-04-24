@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Equipment Loan Reports</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Status</th>
                    <th>Items</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($applications as $app)
                    <tr>
                        <td>{{ $app->user->full_name }}</td>
                        <td>{{ $app->status }}</td>
                        <td>
                            <ul>
                                @foreach ($app->items as $item)
                                    <li>{{ $item->equipment_type }} x {{ $item->quantity_requested }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
