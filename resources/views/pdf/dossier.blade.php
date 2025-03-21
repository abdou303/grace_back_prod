<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>تقرير الملف</title>
    <style>
        @font-face {
            font-family: 'Amiri';

            src: url("{{ storage_path('fonts/Amiri-Regular.ttf') }}") format('truetype');

        }

        body {
            font-family: 'Amiri', sans-serif;
            direction: rtl;
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: right;
        }
    </style>
</head>

<body>
    <h1>تقرير الملف #{{ $dossier->id }}</h1>
    <p>رقم الملف: {{ $dossier->numero }}</p>
    <p>السجن: {{ $dossier->prison->libelle }}</p>
    <p>السجين: {{ $dossier->detenu->nom }} {{ $dossier->detenu->prenom }}</p>
    <p>تاريخ التسجيل: {{ $dossier->date_enregistrement }}</p>

    <h2>القضايا</h2>
    <table>
        <tr>
            <th>رقم القضية</th>
            <th>تاريخ الحكم</th>
        </tr>
        @foreach ($dossier->affaires as $affaire)
            <tr>
                <td>{{ $affaire->numeroaffaire }}</td>
                <td>{{ $affaire->datejujement }}</td>
            </tr>
        @endforeach
    </table>
</body>

</html>
