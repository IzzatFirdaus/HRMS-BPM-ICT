@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>New ICT Equipment Loan Application</h2>

        <form action="{{ route('loan-applications.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="purpose" class="form-label">Loan Purpose</label>
                <input type="text" name="purpose" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="location" class="form-label">Loan Location</label>
                <input type="text" name="location" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="loan_start_date" class="form-label">Start Date</label>
                <input type="date" name="loan_start_date" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="loan_end_date" class="form-label">End Date</label>
                <input type="date" name="loan_end_date" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Submit Loan Request</button>
        </form>
    </div>
@endsection
