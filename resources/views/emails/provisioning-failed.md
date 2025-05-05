{{-- resources/views/emails/provisioning-failed.md --}}
@component('mail::message')

# Perhatian: Peruntukan E-mel Gagal

Terdapat ralat semasa memproses permohonan e-mel/ID pengguna.

**Butiran Permohonan:**

- **ID Permohonan:** {{ $application->id }}
- **Pemohon:** {{ $application->user->full_name ?? $application->user->name ?? 'N/A' }} (ID Pengguna: {{ $application->user->id ?? 'N/A' }})
- **Taraf Perkhidmatan:** {{ $application->service_status_translated ?? $application->service_status ?? 'N/A' }}
- **Tujuan Permohonan:** {{ $application->purpose ?? 'Tiada dinyatakan' }}

**Cadangan E-mel/ID:** {{ $application->proposed_email ?? 'Tiada' }}
**E-mel Kumpulan:** {{ $application->group_email ?? 'Tiada' }}

**Butiran Ralat Peruntukan:**
{{ $errorMessage }}
Sila semak log aplikasi untuk maklumat lanjut mengenai ralat ini dan ambil tindakan pembetulan yang sewajarnya dalam sistem peruntukan e-mel luaran MOTAC. Setelah masalah diselesaikan, sila kemas kini status permohonan dalam Sistem HRMS MOTAC secara manual atau cuba proses semula peruntukan jika fungsi tersebut tersedia.

@component('mail::button', ['url' => route('email-applications.show', $application)])
Lihat Permohonan Dalam Sistem
@endcomponent

Terima kasih,

Sistem HRMS MOTAC
@endcomponent
