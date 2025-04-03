@extends("emails.layouts.layout")

@section("content")
<table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
    <tr>
        <td align="center" valign="top" style="padding:0;margin:0;width:520px">
            <table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-width:10px;border-style:solid;border-color:transparent;background-color:#ffffff;border-radius:32px">
                <tr>
                    <td align="center" style="padding:0;margin:0;padding:15px">
                        <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px;text-align: left;">
                            <strong>Compraste</strong>
                        </p>
                    </td>
                </tr>
                @foreach($data['products'] as $product)
                <tr>
                    <td>
                        <table width="100%" style="margin-bottom: 5px; padding-left: 15px; padding-right: 15px;">
                            <tr>
                                <td class="products">
                                    <a href="{{$product['slug']}}" class="es-button" target="_blank" style="width: 25%; max-width: 120px; border-radius: 16px; border: 1px solid #E2F8FC; text-align: center; align-items: center; justify-content: center; display: flex !important;">
                                        <img src="{{ $product['product_image'] }}" width="90%">
                                    </a>
                                    <div style="width: 75%; justify-content: start; align-items: center; display: flex;">
                                        <div style="display:block;">
                                            <span style="display: block; font-size: 22px; color: #0a1b33; margin-left: 40px;">
                                                {{ $product['product_name'] }}
                                            </span>
                                            <span style="display: block; font-size: 15px; color: #999999; margin-left: 40px;">
                                                Color: {{ $product['color'] }}
                                            </span>
                                            <span style="display: block; font-size: 15px; color: #999999; margin-left: 40px;">
                                                {{ $product['quantity'] }} {{ $product['text_quantity']}}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>     
                </tr>
                @endforeach
                @foreach($data['services'] as $service)
                <tr>
                    <td>
                        <table width="100%" style="margin-bottom: 5px; padding-left: 15px; padding-right: 15px;">
                            <tr>
                                <td class="products">
                                    <a href="{{$service['slug']}}" class="es-button" target="_blank" style="width: 25%; max-width: 120px; border-radius: 16px; border: 1px solid #E2F8FC; text-align: center; align-items: center; justify-content: center; display: flex !important;">
                                        <img src="{{ $service['service_image'] }}" width="90%">
                                    </a>
                                    <div style="width: 75%; justify-content: start; align-items: center; display: flex;">
                                        <div style="display:block;">
                                            <span style="display: block; font-size: 22px; color: #0a1b33; margin-left: 40px;">
                                                {{ $service['service_name'] }}
                                            </span>
                                            @if($service['flavor'])
                                            <span style="display: block; font-size: 15px; color: #999999; margin-left: 40px;">
                                                Sabor: {{ $service['flavor'] }}
                                            </span>
                                            @endif
                                            @if($service['filling'])
                                            <span style="display: block; font-size: 15px; color: #999999; margin-left: 40px;">
                                                Relleno: {{ $service['filling'] }}
                                            </span>
                                            @endif
                                            @if($service['cake_size'])
                                            <span style="display: block; font-size: 15px; color: #999999; margin-left: 40px;">
                                                Tamaño: {{ $service['cake_size'] }}
                                            </span>
                                            @endif
                                            <span style="display: block; font-size: 15px; color: #999999; margin-left: 40px;">
                                                {{ $service['quantity'] }} {{ $service['text_quantity']}}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>     
                </tr>
                @endforeach
            </table>
            <table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" style="margin-top:16px;mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-width:10px;border-style:solid;border-color:transparent;background-color:#ffffff;border-radius:32px">
                <tr>
                    <td align="center" style="padding:0;margin:0;padding-top:15px;padding-bottom:30px; padding-left: 15px; padding-right: 15px;">
                        <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0a1b33;font-size:24px;text-align: left;">
                            <strong>Resumen de compra</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding-left: 15px; padding-right: 15px;">
                        <table width="100%" class="address">
                            <td width="10%" style="vertical-align: top;">
                                <img src="{{ asset('/images/truck.png') }}" alt="truck" title="truck" width="24" height="24" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic">
                            </td>
                            <td width="90%">
                                <p style="font-size: 16px; color: #0a1b33; font-weight: 700; margin: 0; padding-bottom: 12px;">Envío a domicilio</p>
                                <span style="color: #999; font-size: 16px;">
                                    {{ $data['address'] }}<br> 
                                    {{ $data['user'] }} - {{ $data['phone'] }}
                                </span>
                            </td>
                        </table>
                    </td>  
                </tr>
                <tr>
                    <td style="padding-left: 15px; padding-right: 15px;">
                        <table width="100%" style="margin-top: 16px;" class="address">
                            <td width="10%" style="vertical-align: top;">
                                <img src="{{ asset('/images/dollar-sign.png') }}" alt="dollar" title="dollar" width="24" height="24" style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic">
                            </td>
                            <td width="90%" style="padding-bottom: 12px;">
                                <p style="font-size: 16px; color: #0a1b33; font-weight: 700; margin: 0; padding-bottom: 12px;">Pagaste ${{ $data['total'] }}</p>
                                <span style="color: #999; font-size: 16px;">con {{ $data['payment_method'] }}</span>
                            </td>
                        </table>
                    </td>     
                </tr>
                @if($data['showButton'])
                <tr>
                    <td align="center" style="padding:0;margin:0;padding-bottom:20px;padding-left:15px;padding-right:15px">
                        <span  style="border-style:solid;border-color:#2CB543;background:#ff0090;border-width:0px 0px 2px 0px;display:block;border-radius:32px;width:auto;border-bottom-width:0px">
                            <a href="{{ $data['link_send'] }}" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;color:#FFFFFF;font-size:16px;padding:10px 20px 10px 20px;display:block;background:#ff0090;border-radius:32px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:19px;width:auto;text-align:center;mso-padding-alt:0;mso-border-alt:10px solid #ff0090;padding-left:5px;padding-right:5px">
                                Sigue tu envio
                            </a>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;margin:0;padding-bottom:20px;padding-left:15px;padding-right:15px">
                        <span  style="border-style:solid;border-color:#2CB543;background:#ff0090;border-width:0px 0px 2px 0px;display:block;border-radius:32px;width:auto;border-bottom-width:0px">
                            <a href="{{ $data['link_purchases'] }}" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;color:#FFFFFF;font-size:16px;padding:10px 20px 10px 20px;display:block;background:#ff0090;border-radius:32px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:19px;width:auto;text-align:center;mso-padding-alt:0;mso-border-alt:10px solid #ff0090;padding-left:5px;padding-right:5px">
                                Ver mis compras
                            </a>
                        </span>
                    </td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>
@endsection