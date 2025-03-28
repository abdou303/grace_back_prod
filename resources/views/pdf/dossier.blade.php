<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>{{ $dossier->typedossier->libelle }}</title>
    <style>
        /*  @font-face {
            
            font-family: 'Amiri';

            src: url("{{ storage_path('fonts/DroidKufi-Regular.ttf') }}") format('truetype');

        }*/

        h1 {
            /**/
            text-align: center;
            font-weight: bold;
        }

        body {


            /*font-family: "Amiri" !important;*/
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

    <h1 style="font-family: xbriyaz"> {{ $dossier->typedossier->libelle }} عدد: {{ $dossier->numero }}</h1>
    <p>تاريخ التسجيل: {{ $dossier->created_at }}</p>
    <p>المصدر: {{ $dossier->user_tribunal_libelle }}</p>
    <p>مقدم الطلب: {{ $dossier->sourcedemande->libelle }}</p>

    <p>رقم النيابة العامة: {{ $dossier->numeromp }}</p>

    <h3>معلومات حول المتابع:</h3>
    <p>الاسم الكامل: {{ $dossier->detenu->nom }} {{ $dossier->detenu->prenom }}</p>
    <p>رقم البطاقة الوطنية للتعريف: {{ $dossier->detenu->cin }} </p>
    <p>اسم الاب: {{ $dossier->detenu->nompere }} </p>
    <p>اسم الام: {{ $dossier->detenu->nommere }} </p>
    <p>تاريخ الازدياد: {{ $dossier->detenu->datenaissance }} </p>
    <p>الجنسية: {{ $dossier->detenu->nationalite->libelle }} </p>





    <h2>القضايا</h2>
    <table>
        <tr>
            <th>رقم القضية</th>
            <th>تاريخ الحكم</th>
            <th>المحكمة</th>
            <th>المنطوق</th>

        </tr>
        @foreach ($dossier->affaires as $affaire)
            <tr>
                <td>{{ $affaire->annee }}/{{ $affaire->code }}/{{ $affaire->numero }}</td>
                <td>{{ $affaire->datejujement }}</td>
                <td>{{ $affaire->tribunal->libelle }}</td>
                <td>{{ $affaire->conenujugement }}</td>

            </tr>
        @endforeach
    </table>
</body>

</html>
