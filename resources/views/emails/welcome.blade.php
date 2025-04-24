{{-- resources/views/emails/welcome.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <title>Welcome!</title>
</head>

<body>
    <h1>Welcome to the MOTAC HRMS, {{ $user->full_name ?? 'New User' }}!</h1> {{-- Access the $user data passed from the Mailable --}}

    <p>We are excited to have you join the system.</p>

    <p>You can log in using your registered credentials.</p>

    <p>If you have any questions, please contact the HR department or IT support.</p>

    <p>Thank you,</p>
    <p>The MOTAC HRMS Team</p>
</body>

</html>
