@php
$map = [
    'Authorized'   => 'badge-authorized',
    'Auth Pending' => 'badge-auth-pending',
    'Do Not Use'   => 'badge-do-not-use',
    'Active'       => 'badge-authorized',
    'Suspended'    => 'badge-pending',
    'Custom'       => 'badge text-bg-secondary',
    'Official'     => 'badge text-bg-primary',
    'Template'     => 'badge text-bg-info',
];
$cls = $map[$status] ?? 'badge text-bg-secondary';
@endphp
<span class="badge {{ $cls }}">{{ $status }}</span>
