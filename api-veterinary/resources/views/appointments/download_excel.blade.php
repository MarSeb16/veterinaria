<table border="1" cellpadding="5" cellspacing="0"
    style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;">
    <thead style="background-color: #f2f2f2;">
        <tr>
            <th>#</th>
            <th>Mascota</th>
            <th>Especie</th>
            <th>Veterinario</th>
            <th>Fecha de la cita</th>
            <th>Estado de la cita</th>
            <th>Estado de pago</th>
            <th>Horario</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($appointments as $key => $appointment)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $appointment->pet->name }}</td>
                <td>{{ $appointment->pet->specie }}</td>
                <td>{{ $appointment->veterinarie->name . ' ' . $appointment->veterinarie->surname }}</td>
                <td>{{ \Carbon\Carbon::parse($appointment->date_appointment)->format('Y/m/d') }}</td>
                <td>
                    @php
                        $states = [1 => 'Pendiente', 2 => 'Cancelado', 3 => 'Atendido'];
                    @endphp
                    {{ $states[$appointment->state] ?? 'Desconocido' }}
                </td>
                <td>
                    @php
                        $payments = [1 => 'Pendiente', 2 => 'Parcial', 3 => 'Completo'];
                    @endphp
                    {{ $payments[$appointment->state_pay] ?? 'Desconocido' }}
                </td>
                <td>
                    @foreach ($appointment->schedules as $schedule)
                        {{ \Carbon\Carbon::parse($schedule->schedule_hour->hour_start)->format('h:i A') }} -
                        {{ \Carbon\Carbon::parse($schedule->schedule_hour->hour_end)->format('h:i A') }}<br>
                    @endforeach
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
