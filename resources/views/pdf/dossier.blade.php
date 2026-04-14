<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>{{ $dossier->typedossier->libelle ?? '' }}</title>
    <style>
        h1 {
            /**/
            text-align: center;
            font-weight: bold;
        }

        body {


            /*font-family: "Amiri" !important;
            font-family: 'kfgqpcuthmantahanaskh', sans-serif !important;*/
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

        /* Style for the inline data */
        .inline-data {
            display: grid;
            grid-template-columns: repeat(4, 2fr);
            gap: 10px;
            margin-top: 20px;
        }

        .inline-data p {
            margin: 0;
        }

        .row {
            width: 100%;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .column {
            float: right;
            width: 32%;
            margin-left: 2%;
        }

        .column p {
            margin: 0;
        }
    </style>


</head>

<body>
    <table class="header-table" style="width: 100%; border: none; margin-bottom: 30px;">
        <tr>
            <td
                style="width: 33%; border: none; text-align: center; vertical-align: middle; font-size: 13pt; line-height: 1.4; font-weight: normal; font-family:xbriyaz;">
                <p style="margin: 0; padding-right: 15px;">المملكة المغربية</p>
                <p style="margin: 0; padding-right: 15px;">رئاسة النيابة العامة</p>
                <p style="margin: 0; padding-right: 15px;">{{ $dossier->LibelleTribunalUtilisateur->libelle }}</p>
                <p style="margin: 0; padding-right: 15px; text-decoration: underline;">النيابة العامة</p>
                <p style="margin: 0; padding-right: 15px; margin-top: 10px; font-weight: bold;">
                    {{ $dossier->typedossier->libelle }} عدد:
                    @if ($dossier->numero_dapg)
                        {{ $dossier->numero_dapg }}
                    @else
                        {{ $dossier->numero }}
                    @endif
                </p>
                @if ($dossier->numeromp)
                    <p style="margin: 0; padding-right: 15px;">
                        رقم النيابة العامة: {{ $dossier->numeromp }}
                    </p>
                @endif
            </td>
            <td style="width: 33%; border: none; text-align: center; vertical-align: middle;">
                <img src="{{ public_path('images/royaume_du_maroc.svg') }}" width="110px">
            </td>
            <td
                style="width: 33%; border: none; text-align: center; vertical-align: middle; font-size: 11pt; line-height: 1.6; font-family: xbriyaz;">

                <div style="font-size: 12pt; border-top: 2px solid black; width: 60px; text-align: left;">

                </div>
            </td>




        </tr>
    </table>



    <h1 style="font-family: xbriyaz"> ملتمس النيابة العامة</h1>

    <div
        style="margin-top: 30px; line-height: 1.8; font-family: xbriyaz; text-align: justify; direction: rtl;font-size:18px">

        <p>
            إن الوكيل العام للملك لدى {{ $dossier->user_tribunal_libelle }}
            بناء على طلب @if ($dossier->typedossier->id == 1)
                العفو
            @elseif($dossier->typedossier->id == 2)
                {{-- Remplacez 2 par l'ID correspondant à l'autre type --}}
                الافراج المقيد بشروط
            @else
                {{ $dossier->typedossier->libelle }}
            @endif
            المقدم لفائدة {{ $dossier->detenu->nom ?? '' }} {{ $dossier->detenu->prenom ?? '' }}،
            المدان من طرف

            @if ($dossier->affaires->isNotEmpty())
                {{ $dossier->affaires->first()->tribunal->libelle ?? 'المحكمة المختصة' }}
            @else
                ....................
            @endif
            ،
            بموجب المقرر القضائي في القضية عدد

            @if ($dossier->affaires->isNotEmpty())
                {{ $dossier->affaires->first()->numeroaffaire ?? '..........' }}
            @else
                ....................
            @endif

            ،
            بتاريخ

            @if ($dossier->affaires->isNotEmpty() && $dossier->affaires->first()->datejujement)
                {{ \Carbon\Carbon::parse($dossier->affaires->first()->datejujement)->format('Y/m/d') }}
            @else
                ....................
            @endif

            .
        </p>

        <p>
            والذي يلتمس من خلاله
            @if ($dossier->typedossier->id == 1)
                الانعام عليه بالعفو الملكي السامي
            @elseif($dossier->typedossier->id == 2)
                تمتيعه بالافراج المقيد بشروط
            @endif
            من العقوبة المحكوم بها عليه وفق المشار إليه أعلاه.
        </p>

        <p style="margin-top: 15px;">
            فإن هذه النيابة العامة تلتمس:
            {{ $dossier->avis->libelle ?? '........................................' }}
        </p>

        <p>
            تعليل القرار:
            {{ $dossier->observations_parquet ?? '................................................................................' }}
        </p>

        <p style="margin-top: 20px;">
            وللجنة @if ($dossier->typedossier->id == 1)
                العفو
            @elseif($dossier->typedossier->id == 2)
                الافراج المقيد بشروط
            @endif واسع النظر
        </p>

        <div style="margin-top: 40px; width: 100%;">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="border: none; text-align: right; width: 50%;font-size:18px"><strong>
                            التاريخ:</strong> {{ now()->format('Y/m/d') }}
                    </td>
                    <td style="border: none; text-align: left; width: 50%;font-size:18px">
                        <strong> إسم النائب:</strong> {{ $dossier->userParquetObjet->name ?? '....................' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>










</body>

</html>
