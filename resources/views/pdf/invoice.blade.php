<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <!-- <meta http-equiv="X-UA-Compatible" content="IE=edge" /> -->
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Document</title>
    </head>
    <body>
        <style type="text/css">
            /* @page {
                margin: 100px 15px;
            } */
            .container {
                width: 100%;
                /* position: fixed; */
            }
            @page {
                margin: 40px 15px 40px 15px;
            }
            .tg {
                page-break-before: auto;
                page-break-inside: avoid;
                border-collapse: collapse;
                border-spacing: 0;
                width: 100%;
                table-layout: fixed;
            }
            .tg td {
                page-break-inside: avoid;
                border-color: black;
                border-style: solid;
                border-width: 1px;
                /* font-family: Arial, sans-serif; */
                font-family: "Times New Roman", Times, serif;
                font-size: 14px;
                overflow: hidden;
                /* padding: 10px 5px; */
                word-break: normal;
            }
            .tg td p {
                margin: 3px;
            }
            .rotate {
                page-break-inside: avoid;
                transform: rotate(-90deg);
                position: absolute;
                left: -30px;
                top: -10px;
                -webkit-transform: rotate(-90deg);
            }

            .rotate.remitente {
                left: -25px;
                top: -10px;
            }

            .rotate.destinatario {
                left: -35px;
                top: -10px;
            }

            .rotate.datos {
                left: -7px;
                top: -10px;
            }

            .cabecera {
                width: 4%;
                page-break-inside: avoid;
            }

            .remitente-contenido {
                width: 10%;
            }

            .barcode {
                padding-top: 1px !important;
                text-align: center;
            }

            .barcode img {
                width: 200px;
            }
            .nobreak {
                page-break-inside: avoid;
                border: green solid 2px;
                position: relative;
            }
        </style>

        <main>
            <div class="container">
                @for ($i = 0; $i < 15; $i++)

                <table class="tg">
                    <tbody>
                        <tr>
                            <td class="cabecera" rowspan="2">
                                <div
                                    style="
                                        position: relative;
                                        overflow: visible;
                                    "
                                >
                                    <span class="rotate remitente">
                                        <b>REMITENTE</b>
                                    </span>
                                </div>
                            </td>
                            <td
                                colspan="4"
                                rowspan="2"
                                style="vertical-align: top"
                            >
                                <p>NOMBRE: Lumingo</p>
                                <p>CIUDAD: LIMA</p>
                                <p>FECHA: 2021-03-22</p>
                                <p><b>Nº de Guía: 210320000001401</b></p>
                                <p>
                                    DIRECCION: Avenida Juan De Arona 755,
                                    Edificio Wework, Piso 3, San Isidro, Lima.
                                </p>
                            </td>
                            <td class="cabecera" rowspan="3">
                                <div
                                    style="
                                        position: relative;
                                        overflow: visible;
                                    "
                                >
                                    <span class="rotate destinatario"
                                        ><b>DESTINATARIO</b></span
                                    >
                                </div>
                            </td>
                            <td
                                colspan="5"
                                rowspan="3"
                                style="vertical-align: top"
                            >
                                <p>NOMBRE: Lumingo</p>
                                <p>CIUDAD: LIMA</p>
                                <p>FECHA: 2021-03-22</p>
                                <p>Nº de Guía: 210320000001401</p>
                                <p>Nº de Guía: 210320000001401</p>
                                <p>Nº de Guía: 210320000001401</p>
                                <p>
                                    DIRECCION: Avenida Juan De Arona 755,
                                    Edificio Wework, Piso 3, San Isidro, Lima.
                                </p>
                            </td>
                        </tr>
                        <tr></tr>
                        <tr>
                            <td
                                colspan="5"
                                rowspan="2"
                                class="barcode"
                                style="padding: 10px 0 0 0"
                            >
                                @php $generator = new
                                Picqer\Barcode\BarcodeGeneratorPNG(); echo '<img
                                    style="margin-top: 8px"
                                    src="data:image/png;base64,' . base64_encode($generator->getBarcode('T001\'00816543', $generator::TYPE_CODE_128, 2, 70)) . '"
                                />'; @endphp
                                <p
                                    style="
                                        font-size: 16px;
                                        font-weight: bold;
                                        margin: 0;
                                        padding: 0;
                                    "
                                >
                                    T001'00816543
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td class="cabecera" rowspan="3">
                                <div
                                    style="
                                        position: relative;
                                        overflow: visible;
                                    "
                                >
                                    <span class="rotate remitente"
                                        ><b>CONTENIDO</b></span
                                    >
                                </div>
                            </td>
                            <td
                                colspan="5"
                                rowspan="3"
                                style="vertical-align: top"
                            >
                                <p>NOMBRE: Lumingo</p>
                                <p>CIUDAD: LIMA</p>
                                <p>FECHA: 2021-03-22</p>
                                <p>Nº de Guía: 210320000001401</p>
                                <p>Nº de Guía: 210320000001401</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="cabecera" rowspan="2">
                                <div
                                    style="
                                        position: relative;
                                        overflow: visible;
                                    "
                                >
                                    <span class="rotate datos">
                                        <b>DATOS</b>
                                    </span>
                                </div>
                            </td>
                            <td
                                colspan="2"
                                style="text-align: center"
                                rowspan="2"
                            >
                                <b>NRO. PIEZAS: 0</b>
                            </td>
                            <td
                                colspan="2"
                                style="text-align: center"
                                rowspan="2"
                            >
                                <b>PESO SECO: 0</b>
                            </td>
                        </tr>
                        <tr></tr>
                    </tbody>
                </table>
                <br />
                @endfor {{--
                <div class="nobreak">
                    <table
                        style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            width: 200px;
                        "
                    >
                        <thead>
                            <tr>
                                <th colspan="3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td
                                    colspan="2"
                                    rowspan="3"
                                    style="border: black solid 1px; width: 50px"
                                >
                                    Lorem, ipsum dolor.
                                </td>
                                <td style="border: black solid 1px">lorem1</td>
                            </tr>
                            <tr>
                                <td style="border: black solid 1px">lorem2</td>
                            </tr>
                            <tr>
                                <td style="border: black solid 1px">lorem3</td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                --}}
            </div>
        </main>
    </body>
</html>
