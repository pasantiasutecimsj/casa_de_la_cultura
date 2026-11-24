<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Escolaridad - {{ $alumno->nombre }} {{ $alumno->apellido }}</title>
    <style>
        @page { margin: 32px 38px; }
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { color: #1d4ed8; font-size: 22px; margin: 0 0 4px; }
        h2 { border-bottom: 1px solid #cbd5e1; color: #1e3a8a; font-size: 14px; margin: 24px 0 10px; padding-bottom: 5px; }
        .muted { color: #64748b; }
        .data { width: 100%; }
        .data td { padding: 3px 0; width: 50%; }
        .label { color: #64748b; }
        table { border-collapse: collapse; width: 100%; }
        th { background: #dbeafe; color: #1e3a8a; text-align: left; }
        th, td { border: 1px solid #cbd5e1; padding: 7px; }
        .empty { color: #64748b; padding: 10px 0; }
        .footer { color: #64748b; font-size: 9px; margin-top: 28px; }
    </style>
</head>
<body>
    <h1>Escolaridad</h1>
    <div class="muted">Casa de la Cultura</div>

    <h2>Datos del alumno</h2>
    <table class="data">
        <tr><td><span class="label">Nombre:</span> {{ $alumno->nombre }} {{ $alumno->apellido }}</td><td><span class="label">Cédula:</span> {{ $alumno->ci }}</td></tr>
        <tr><td><span class="label">Fecha de nacimiento:</span> {{ optional($alumno->edad)->format('d/m/Y') }}</td><td><span class="label">Fecha de ingreso:</span> {{ optional($alumno->fecha_ingreso)->format('d/m/Y') }}</td></tr>
        <tr><td><span class="label">Correo:</span> {{ $alumno->correo }}</td><td><span class="label">Teléfono:</span> {{ $alumno->telefono }}</td></tr>
    </table>

    <h2>Calificaciones</h2>
    @if ($alumno->calificaciones->isNotEmpty())
        <table>
            <thead><tr><th>Materia</th><th>Fecha de inscripción</th><th>Fecha de examen</th><th>Nota</th></tr></thead>
            <tbody>
                @foreach ($alumno->calificaciones as $calificacion)
                    <tr>
                        <td>{{ $calificacion->materia?->nombre ?? 'Materia eliminada' }}</td>
                        <td>{{ optional($calificacion->fecha_inscripcion)->format('d/m/Y') }}</td>
                        <td>{{ optional($calificacion->fecha_examen)->format('d/m/Y') ?: '—' }}</td>
                        <td>{{ $calificacion->nota ?? 'Sin calificar' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">El alumno no tiene calificaciones registradas.</div>
    @endif

    <div class="footer">Documento generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
