@extends("emails.layouts.layout")

@section("content")
<table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
    <tr>
        <td align="center" valign="top" style="padding:0;margin:0;width:520px">
            <table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-width:10px;border-style:solid;border-color:transparent;background-color:#ffffff;border-radius:32px">
                <tr>
                    <td align="center" style="padding:0;margin:0;padding-top:15px;padding-bottom:10px">
                        <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#FF0090;font-size:24px">
                            <strong>{{ $data['title'] }}</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0 20px;margin:0;padding-bottom:20px;">
                        <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:19px;color:#0a1b33;font-size:16px">
                            Hola <strong>Equipo Partymax</strong>,<br>
                            ¡Buenas noticias! Un usuario quiere ponerse en contacto con ustedes y ha dejado el siguiente mensaje: <br>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="start" style="padding:0 20px;margin:0;padding-bottom:20px;">
                        <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:19px;color:#0a1b33;font-size:16px">
                            {!! $data['text'] !!}
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;margin:0;padding-bottom:20px;padding-left:15px;padding-right:15px">
                        <p style="margin:10px 0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:19px;color:#0a1b33;font-size:16px">
                            Puedes responder directamente haciendo clic en el correo del usuario o usando este enlace:
                        </p
                        <span  style="border-style:solid;border-color:#2CB543;background:#ff0090;border-width:0px 0px 2px 0px;display:block;border-radius:32px;width:auto;border-bottom-width:0px">
                            <a href="mailto:{{ $data['email'] }}?subject=Respuesta a tu mensaje de contacto" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;color:#FFFFFF;font-size:16px;padding:10px 20px 10px 20px;display:block;background:#ff0090;border-radius:32px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:19px;width:auto;text-align:center;mso-padding-alt:0;mso-border-alt:10px solid #ff0090;padding-left:5px;padding-right:5px">
                                {{ $data['buttonText'] }}
                            </a>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;margin:0;padding-bottom:15px;">
                        <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:19px;color:#0a1b33;font-size:16px">
                            El usuario aceptó los términos y el tratamiento de datos.                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection