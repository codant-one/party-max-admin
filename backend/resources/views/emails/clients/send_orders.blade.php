@extends("emails.layouts.layout")

@section("content")
<table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
    <tr>
        <td align="center" valign="top" style="padding:0;Margin:0;width:520px">
            <table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-width:10px;border-style:solid;border-color:transparent;background-color:#ffffff;border-radius:32px">
                <tr>  
                    <td class="festin" align="center" style="padding:0;Margin:0;padding-top:20px;font-size:0px">
                        @if($data['shipping_state_id'] === '2')
                            <img class="adapt-img" src="{{ asset('/images/festin_3.png') }}" width="auto">
                        @else
                            <img class="adapt-img" src="{{ asset('/images/festin_2.png') }}" width="auto">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;Margin:0;padding-top:15px;padding-bottom:10px">
                        <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#FF0090;font-size:24px">
                            <strong>{{ $data['title'] }}</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;Margin:0;padding-bottom:15px;">
                        <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:19px;color:#0a1b33;font-size:16px">
                            @if($data['shipping_state_id'] === '2')
                                Lo sentimos!!!
                            @else
                                ¡Que lo disfrutes!
                            @endif
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
            </table>
                   
            <table cellpadding="0" cellspacing="0" width="100%" bgcolor="#FFFFFF" style="margin-top:16px; mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:separate;border-spacing:0px;border-width:10px;border-style:solid;border-color:transparent;background-color:#ffffff;border-radius:32px">
                <tr>
                    <td align="center" style="padding:0;Margin:0;padding-bottom:15px;padding-top:15px;padding-left:30px;padding-right:30px;font-size:0px;border-bottom: 1px solid #E2F8FC;">
                        @if($data['shipping_state_id'] === '2')
                        <p style="color: #0A1B33; font-size: 16px;">
                            {{ $data['text'] }}<br>
                            Razón: {{ $data['reason'] }}.
                        </p><br><br>
                        @else
                        <p style="color: #0A1B33; font-size: 16px;">
                            {{ $data['text'] }} {{ $data['address'] }}
                        </p><br><br>
                        @endif
                        <p style="color: #0A1B33; font-size: 16px;">
                            Si tienes algún problema, estamos para ayudarte.<br>
                            <a href="{{ $data['link_contact'] }}" target="_blank" style="mso-style-priority:100!important;text-decoration:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;color:#ff0090;font-size:16px;padding:10px 20px 0 20px;display:block;font-weight:bold;font-style:normal;line-height:19px;mso-padding-alt:0;mso-border-alt: 10px solid #ff0090;">
                                Contáctanos
                            </a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;Margin:0;padding-top:15px;padding-bottom:10px">
                        <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:36px;color:#0A1B33;font-size:18px">
                            Explora nuestro marketplace
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;Margin:0;padding-bottom:20px;padding-left:15px;padding-right:15px">
                        <span  style="border-style:solid;border-color:#2CB543;background:#ff0090;border-width:0px 0px 2px 0px;display:block;border-radius:32px;width:auto;border-bottom-width:0px">
                            <a href="{{ $data['link'] }}" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;color:#FFFFFF;font-size:16px;padding:10px 20px 10px 20px;display:block;background:#ff0090;border-radius:32px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:19px;width:auto;text-align:center;mso-padding-alt:0;mso-border-alt:10px solid #ff0090;padding-left:5px;padding-right:5px">
                                www.partymax.co
                            </a>
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection