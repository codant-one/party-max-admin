@extends("emails.layouts.layout")

@section("content")
<tr>
    <td align="left" bgcolor="#E2F8FC" style="Margin:0;padding-top:10px;padding-left:40px;padding-right:40px;background-color:#e2f8fc">
        <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
            <tr>
                <td align="center" valign="top" style="padding:0;Margin:0;width:520px">
                    <table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-width:10px;border-style:solid;border-color:transparent;background-color:#ffffff;border-radius:32px">
                        <tr>
                            <td align="center" style="padding:0;Margin:0;padding-top:30px">
                                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:54px;color:#ff0090;font-size:36px">
                                    <strong>¡Bienvenid@!</strong>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="padding:0;Margin:0;padding-top:15px;padding-bottom:20px;border-bottom: 1px solid #D9EEF2;">
                                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px">
                                    Cuenta creada satisfactoriamente
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="padding:0;Margin:0;padding-top:10px;padding-bottom:15px">
                                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px">
                                    <strong>{!! $data['user'] !!}</strong>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="padding:0;Margin:0;padding-bottom:15px;padding-left:15px;padding-right:15px">
                                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:19px;color:#0a1b33;font-size:16px">
                                    PartyMax te da la bienvenida, hemos creado satisfactoriamente tu cuenta de usuario para que puedas disfrutar de todos nuestros productos y servicios.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="padding:0;Margin:0;padding-top:15px;padding-bottom:15px;border-bottom: 1px solid #D9EEF2;">
                                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px">
                                    Usuario:&nbsp;<br>
                                    <strong>{!! $data['user_name'] !!}</strong>
                                </p>
                                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px">
                                    <br>
                                </p>
                                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px">
                                    Contraseña de acceso:&nbsp;<br>
                                    <strong>{!! $data['password'] !!}</strong>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="padding:0;Margin:0;padding-bottom:15px;padding-top:30px">
                                <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:27px;color:#0a1b33;font-size:18px">
                                    Explora nuestro marketplace
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="padding:0;Margin:0;padding-bottom:40px;padding-left:40px;padding-right:40px">
                                <span class="es-button-border" style="border-style:solid;border-color:#2CB543;background:#ff0090;border-width:0px 0px 2px 0px;display:block;border-radius:32px;width:auto;border-bottom-width:0px">
                                    <a href="{{ env('APP_DOMAIN') }}" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;color:#FFFFFF;font-size:16px;padding:10px 20px 10px 20px;display:block;background:#ff0090;border-radius:32px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:19px;width:auto;text-align:center;mso-padding-alt:0;mso-border-alt:10px solid #ff0090;padding-left:5px;padding-right:5px">
                                        www.partymax.co
                                    </a>
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>
@endsection

<!-- <tr>
    <td colspan="2">
        <table style="font-family: Poppins,Helvetica,sans-serif; clear: both; margin-top: 6px!important; margin-bottom: 6px!important;  max-width: none!important; border-collapse: separate!important;  border-spacing: 0; background-color: white; width: 100%; padding: 0 5px;" align="center">
            <tr>
                <td style="padding-bottom: 1.5rem!important;" align="center">
                    <p style="font-size: 18px; font-weight: 600;">{{$title}}</p>
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 1.5rem!important; font-size: 14px;" align="left">
                    <p>Hola: {{$user}}</p>

                    <p>{!! $text !!}</p>
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 1.5rem!important; font-size: 12px;" align="center">
                    <a href="{{$buttonLink}}" class="btn btn-primary" style="color: #FFFFFF; background-color: #FF0090; border-color: #FF0090; line-height: 1.5; cursor: pointer; border-radius: 0.475rem; padding-top: 0.75rem; padding-bottom: 0.75rem; padding-left: 1.5rem; padding-right: 1.5rem; text-decoration: none;">
                        {{$buttonText}}
                    </a>
                </td>
            </tr>
        </table>
    </td>
</tr> -->

        