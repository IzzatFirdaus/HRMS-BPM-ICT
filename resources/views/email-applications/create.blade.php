@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>New Email/User ID Request</h2>

        <form action="{{ route('email-applications.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="purpose" class="form-label">Purpose</label>
                <input type="text" name="purpose" class="form-control" required>
            </div>

            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="certification_accepted" id="cert1" required>
                <label class="form-check-label" for="cert1">
                    I confirm the above information is accurate.
                </label>
            </div>

            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="cert2" required>
                <label class="form-check-label" for="cert2">
                    I have read and understood the policy.
                </label>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="cert3" required>
                <label class="form-check-label" for="cert3">
                    I agree to abide by MOTAC usage terms.
                </label>
            </div>

            <button type="submit" class="btn btn-success">Submit Application</button>
        </form>
    </div>
@endsection
