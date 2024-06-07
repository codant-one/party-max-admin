@extends("emails.layouts.layout")

@section("content")
<table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
    <tr>
        <td align="center" valign="top" style="padding:0;Margin:0;width:520px">
            <table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-width:10px;border-style:solid;border-color:transparent;background-color:#ffffff;border-radius:32px">
                <tr>  
                    <td class="festin" align="center" style="padding:0;Margin:0;padding-top:20px;font-size:0px">
                        <img class="adapt-img" src="{{ asset('/images/festin_2.png') }}" width="auto">
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;Margin:0;padding-top:15px;padding-bottom:10px">
                        <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#FF0090;font-size:24px">
                            <strong>Nuevo Contacto</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;Margin:0;padding-bottom:15px;">
                        <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:19px;color:#0a1b33;font-size:16px">
                            Un proveedor quiere contactarte
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;Margin:0;padding-top:15px;padding-bottom:15px;border-top: 1px solid #D9EEF2;">
                        <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px">
                            <strong>Nombre:</strong>&nbsp;{{ $name }}
                        </p>
                        <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px">
                            <strong>Nit:&nbsp;</strong>{{ $nit }}
                        </p>
                        <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px">
                            <strong>E-mail:&nbsp;</strong>{{ $email }}
                        </p>
                        <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px">
                            <strong>Teléfono:&nbsp;</strong>{{ $phone }}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection