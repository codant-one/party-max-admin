@extends("emails.layouts.layout")

@section("content")
<table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-width:10px;border-style:solid;border-color:transparent;background-color:#ffffff;border-radius:32px">
    <tr>
        <td align="center" style="padding:0;margin:0;padding-top:20px;font-size:0px">
            <table width="100%">
                <td width="100%" style="vertical-align: top; text-align: center;" class="out_of_stock">
                    <img src="{{ asset('/images/out_of_stock.png') }}">
                </td>
            </table>
            <table width="100%" style="padding-left: 20px; padding-right: 20px;">
                <td width="100%">
                    <p style="font-size: 36px; color: #ff0090; font-weight: bold; margin-top: 20px; text-align: center;">Ya no tienes existencias del siguiente producto</p>
                </td>
            </table>
        </td>
    </tr>
    <tr>
        <td align="center" style="margin:0;padding-bottom:20px;padding-left:20px;padding-right:20px; border-bottom: 1px solid #D9EEF2;">
            <table width="100%">
                <td class="products">
                    <a href="{{$data['product']['slug']}}" class="es-button" target="_blank" style="width: 25%; max-width: 120px; border-radius: 16px; border: 1px solid #E2F8FC; text-align: center; align-items: center; justify-content: center; display: flex !important;">
                        <img src="{{ $data['product']['product_image'] }}" width="90%">
                    </a>
                    <div style="width: 75%; justify-content: start; align-items: center; display: flex;" class="text">
                        <div style="display:block;">
                            <span style="display: block;font-size: 24px; color: #0a1b33; margin-left: 40px;">
                                {{ $data['product']['product_name'] }}
                            </span>
                            <span style="display: block; font-size: 15px; color: #999999; margin-left: 40px;">
                                Color: {{ $data['product']['product_color'] }}
                            </span>
                        </div>
                    </div>
                </td>
            </table>
        </td>
    </tr>
    <tr>
        <td align="center" style="padding:0;margin:0;padding-bottom:30px; padding-top: 30px; padding-left: 15px; padding-right: 15px;">
            <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:24px;color:#0a1b33;font-size:24px">
                Abastece tu tienda lo antes posible para seguir disfrutando de vender en PartyMax.
            </p>
        </td>
    </tr>
    <tr>
        <td align="center" style="padding:0;margin:0;padding-bottom:20px;padding-left:15px;padding-right:15px">
            <span  style="border-style:solid;border-color:#2CB543;background:#ff0090;border-width:0px 0px 2px 0px;display:block;border-radius:32px;width:auto;border-bottom-width:0px">
                <a href="{{ $data['link'] }}" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;color:#FFFFFF;font-size:16px;padding:10px 20px 10px 20px;display:block;background:#ff0090;border-radius:32px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:19px;width:auto;text-align:center;mso-padding-alt:0;mso-border-alt:10px solid #ff0090;padding-left:5px;padding-right:5px">
                    Ver mis productos
                </a>
            </span>
        </td>
    </tr>
</table>
@endsection