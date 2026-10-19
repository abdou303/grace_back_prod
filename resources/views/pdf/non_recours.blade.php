{{-- الشهادة الضبطية : vue dédiée (copie du ملتمس النيابة العامة, à adapter). Données : $dossier, $affaire, $choix, $requette --}}
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>{{ $dossier->typedossier->libelle ?? '' }}</title>
    <style>
        /* Forcer la police sur TOUS les éléments */
        * {
            font-family: 'benaya-mohannad', sans-serif !important;
        }

        body {
            direction: rtl;
            text-align: right;
            font-family: 'benaya-mohannad', sans-serif !important;
        }

        h1 {
            text-align: center;
            font-weight: bold;
            font-size: 24pt;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            text-align: right;
            padding: 5px;
        }

        /* Style spécifique pour ton en-tête (pour éviter qu'il n'écrase la police) */
        .header-table td p {
            font-family: 'benaya-mohannad', sans-serif !important;
            line-height: 1.5;
        }
    </style>


</head>

<body>
    <table class="header-table" style="width: 100%; border: none; margin-bottom: 30px;">
        <tr>
            <td
                style="width: 33%; border: none; text-align: center; vertical-align: middle; font-size: 13pt; line-height: 1.4; font-weight: normal;">
                <p style="margin: 0; padding-right: 15px;">المملكة المغربية</p>
                <p style="margin: 0; padding-right: 15px;">وزارة العدل</p>
                <p style="margin: 0; padding-right: 15px;">{{ $dossier->LibelleTribunalUtilisateur->libelle }}</p>
                <!-- <p style="margin: 0; padding-right: 15px; text-decoration: underline;">النيابة العامة</p> -->

            </td>
            <td style="width: 33%; border: none; text-align: center; vertical-align: middle;">
                <img src="{{ public_path('images/royaume_du_maroc.svg') }}" width="110px">
            </td>
            <td
                style="width: 33%; border: none; text-align: center; vertical-align: middle; font-size: 11pt; line-height: 1.6; ">

                <div style="font-size: 12pt; border-top: 2px solid black; width: 60px; text-align: left;">

                </div>
            </td>




        </tr>
    </table>
    <br>




    <h1>شهادة ضبطية</h1>
    <br>
    <br>
    <div style="margin-top: 30px; line-height: 1.8;  text-align: justify; direction: rtl;font-size:20px">

        <br>
        إن رئيس كتابة الضبط ب{{ $dossier->LibelleTribunalUtilisateur->libelle ?? '' }}، يشهد بأنه بعد الاطلاع على
        ملف القضية عدد {{ $affaire->numeroaffaire ?? '..........' }}، الصادر فيها قرار بتاريخ @if ($affaire->datejujement)
            {{ \Carbon\Carbon::parse($affaire->datejujement)->format('Y/m/d') }}
        @else
            ....................
        @endif
        .
        <br>
        تبث إليه أن القرار الصادر في الملف المذكور:
        <strong>{{ $choix->libelle ?? '........................................' }}</strong>،
        <strong>{{ $affaire->observation_non_recours ?? '................................................................................' }}</strong>
        .
        <br>
        وبمضمنه سلمت هذه الشهادة لاستعمالها طبق القانون لا سيما من أجل الادلاء بها في

        @if ($dossier->typedossier->id == 1)
            ملف العفو
        @elseif($dossier->typedossier->id == 2)
            {{-- Remplacez 2 par l'ID correspondant à l'autre type --}}
            ملف الافراج المقيد بشروط
        @else
            {{ $dossier->typedossier->libelle }}
        @endif
        عدد:
        @if ($dossier->numero_dapg)
            {{ $dossier->numero_dapg }}
        @else
            {{ $dossier->numero }}
        @endif
        .
        </p>


        <br>
        <br>
        <br>
        <div style="margin-top: 40px; width: 100%;">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="border: none; text-align: right; width: 50%;font-size:20px"><strong>
                            التاريخ:</strong><br>
                        {{-- Date de validation du greffe (TR) enregistrée avec le choix "نعم" --}}
                        @php
                            $dateTr =
                                $affaire->date_choix_non_recours ??
                                ($requette->date_etat_greffe ?? $dossier->date_etat_greffe);
                        @endphp
                        {{ $dateTr ? \Carbon\Carbon::parse($dateTr)->format('Y/m/d') : '....................' }}
                    </td>
                    <td style="border: none; text-align: left; width: 50%;font-size:20px">
                        <strong> محرر الشهادة:</strong><br>
                        {{-- Utilisateur du greffe qui a choisi "نعم" --}}
                        {{ $userChoix->name ?? '....................' }}

                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>

</html>
