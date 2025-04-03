@extends("emails.layouts.layout")

@section("content")
<table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
    <tr>
        <td align="center" valign="top" style="padding:0;margin:0;width:520px">
            <table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" style="margin-top:16px;mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-width:10px;border-style:solid;border-color:transparent;background-color:#ffffff;border-radius:32px">
                <tr>  
                    <td class="festin" align="center" style="padding:0;margin:0;padding-top:20px;font-size:0px">
                            <img class="adapt-img" src="{{ asset('/images/warning_festin.png') }}" width="auto">
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;margin:0;padding-top:15px;padding-bottom:10px">
                        <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#FF0090;font-size:24px">
                            <strong>Estado de la transacción: {{$data['payment_state_id']}}</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;margin:0;padding-bottom:15px">
                        <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px">
                            <strong>Hola, {{$data['user']}}</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;margin:0;padding-bottom:15px;padding-left:15px;padding-right:15px">
                        <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:19px;color:#0a1b33;font-size:16px">
                            Su orden #{{$data['orderId']}} no ha sido completado exitosamente; por favor regrese a la tienda para realizar el pago correspondiente al pedido.
                        </p>
                    </td>
                </tr>     
                <tr>
                    <td align="center" style="padding:0;margin:0;padding-bottom:15px">
                       <p style="font-size: 16px; color: #0a1b33; font-weight: 700; margin: 0; padding-bottom: 12px;">Mensaje de respuesta:</p>
                        <span style="color: #999; font-size: 16px;">
                            {{ $data['message'] }}
                        </span>
                    </td> 
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection