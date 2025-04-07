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
                    <td align="center" style="padding:0;margin:0;padding-bottom:15px;">
                        <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:19px;color:#0a1b33;font-size:16px">
                            {!! $data['text'] !!}
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
                                                {{ ucwords(strtolower($product['product_name'])) }}
                                            </span>
                                            <span style="display: block; font-size: 15px; color: #999999; margin-left: 40px;">
                                                <svg width="41" height="38" viewBox="0 0 41 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.8506 29.2146L20.3651 28.9059L20.8795 29.2146L31.0222 35.3001L28.3445 23.7779L28.2086 23.193L28.661 22.7982L37.5845 15.0103L25.8298 14.0375L25.2284 13.9878L24.9921 13.4324L20.3651 2.55473L15.738 13.4324L15.5017 13.9878L14.9003 14.0375L3.14557 15.0103L12.0691 22.7982L12.5215 23.193L12.3856 23.7779L9.70791 35.3001L19.8506 29.2146Z" stroke="#999999" stroke-width="2"/>
                                                </svg>
                                                <svg width="41" height="38" viewBox="0 0 41 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.8506 29.2146L20.3651 28.9059L20.8795 29.2146L31.0222 35.3001L28.3445 23.7779L28.2086 23.193L28.661 22.7982L37.5845 15.0103L25.8298 14.0375L25.2284 13.9878L24.9921 13.4324L20.3651 2.55473L15.738 13.4324L15.5017 13.9878L14.9003 14.0375L3.14557 15.0103L12.0691 22.7982L12.5215 23.193L12.3856 23.7779L9.70791 35.3001L19.8506 29.2146Z" stroke="#999999" stroke-width="2"/>
                                                </svg>
                                                <svg width="41" height="38" viewBox="0 0 41 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.8506 29.2146L20.3651 28.9059L20.8795 29.2146L31.0222 35.3001L28.3445 23.7779L28.2086 23.193L28.661 22.7982L37.5845 15.0103L25.8298 14.0375L25.2284 13.9878L24.9921 13.4324L20.3651 2.55473L15.738 13.4324L15.5017 13.9878L14.9003 14.0375L3.14557 15.0103L12.0691 22.7982L12.5215 23.193L12.3856 23.7779L9.70791 35.3001L19.8506 29.2146Z" stroke="#999999" stroke-width="2"/>
                                                </svg>
                                                <svg width="41" height="38" viewBox="0 0 41 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.8506 29.2146L20.3651 28.9059L20.8795 29.2146L31.0222 35.3001L28.3445 23.7779L28.2086 23.193L28.661 22.7982L37.5845 15.0103L25.8298 14.0375L25.2284 13.9878L24.9921 13.4324L20.3651 2.55473L15.738 13.4324L15.5017 13.9878L14.9003 14.0375L3.14557 15.0103L12.0691 22.7982L12.5215 23.193L12.3856 23.7779L9.70791 35.3001L19.8506 29.2146Z" stroke="#999999" stroke-width="2"/>
                                                </svg>
                                                <svg width="41" height="38" viewBox="0 0 41 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.8506 29.2146L20.3651 28.9059L20.8795 29.2146L31.0222 35.3001L28.3445 23.7779L28.2086 23.193L28.661 22.7982L37.5845 15.0103L25.8298 14.0375L25.2284 13.9878L24.9921 13.4324L20.3651 2.55473L15.738 13.4324L15.5017 13.9878L14.9003 14.0375L3.14557 15.0103L12.0691 22.7982L12.5215 23.193L12.3856 23.7779L9.70791 35.3001L19.8506 29.2146Z" stroke="#999999" stroke-width="2"/>
                                                </svg>
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
                                                {{ ucwords(strtolower($service['service_name'])) }}
                                            </span>
                                            <span style="display: block; font-size: 15px; color: #999999; margin-left: 40px;">
                                                <svg width="41" height="38" viewBox="0 0 41 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.8506 29.2146L20.3651 28.9059L20.8795 29.2146L31.0222 35.3001L28.3445 23.7779L28.2086 23.193L28.661 22.7982L37.5845 15.0103L25.8298 14.0375L25.2284 13.9878L24.9921 13.4324L20.3651 2.55473L15.738 13.4324L15.5017 13.9878L14.9003 14.0375L3.14557 15.0103L12.0691 22.7982L12.5215 23.193L12.3856 23.7779L9.70791 35.3001L19.8506 29.2146Z" stroke="#999999" stroke-width="2"/>
                                                </svg>
                                                <svg width="41" height="38" viewBox="0 0 41 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.8506 29.2146L20.3651 28.9059L20.8795 29.2146L31.0222 35.3001L28.3445 23.7779L28.2086 23.193L28.661 22.7982L37.5845 15.0103L25.8298 14.0375L25.2284 13.9878L24.9921 13.4324L20.3651 2.55473L15.738 13.4324L15.5017 13.9878L14.9003 14.0375L3.14557 15.0103L12.0691 22.7982L12.5215 23.193L12.3856 23.7779L9.70791 35.3001L19.8506 29.2146Z" stroke="#999999" stroke-width="2"/>
                                                </svg>
                                                <svg width="41" height="38" viewBox="0 0 41 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.8506 29.2146L20.3651 28.9059L20.8795 29.2146L31.0222 35.3001L28.3445 23.7779L28.2086 23.193L28.661 22.7982L37.5845 15.0103L25.8298 14.0375L25.2284 13.9878L24.9921 13.4324L20.3651 2.55473L15.738 13.4324L15.5017 13.9878L14.9003 14.0375L3.14557 15.0103L12.0691 22.7982L12.5215 23.193L12.3856 23.7779L9.70791 35.3001L19.8506 29.2146Z" stroke="#999999" stroke-width="2"/>
                                                </svg>
                                                <svg width="41" height="38" viewBox="0 0 41 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.8506 29.2146L20.3651 28.9059L20.8795 29.2146L31.0222 35.3001L28.3445 23.7779L28.2086 23.193L28.661 22.7982L37.5845 15.0103L25.8298 14.0375L25.2284 13.9878L24.9921 13.4324L20.3651 2.55473L15.738 13.4324L15.5017 13.9878L14.9003 14.0375L3.14557 15.0103L12.0691 22.7982L12.5215 23.193L12.3856 23.7779L9.70791 35.3001L19.8506 29.2146Z" stroke="#999999" stroke-width="2"/>
                                                </svg>
                                                <svg width="41" height="38" viewBox="0 0 41 38" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M19.8506 29.2146L20.3651 28.9059L20.8795 29.2146L31.0222 35.3001L28.3445 23.7779L28.2086 23.193L28.661 22.7982L37.5845 15.0103L25.8298 14.0375L25.2284 13.9878L24.9921 13.4324L20.3651 2.55473L15.738 13.4324L15.5017 13.9878L14.9003 14.0375L3.14557 15.0103L12.0691 22.7982L12.5215 23.193L12.3856 23.7779L9.70791 35.3001L19.8506 29.2146Z" stroke="#999999" stroke-width="2"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>     
                </tr>
                @endforeach
                <tr>
                    <td align="center" style="padding:0;margin:0;padding-bottom:20px;padding-left:15px;padding-right:15px">
                        <span  style="border-style:solid;border-color:#2CB543;background:#ff0090;border-width:0px 0px 2px 0px;display:block;border-radius:32px;width:auto;border-bottom-width:0px">
                            <a href="{{ $data['link'] }}" class="es-button" target="_blank" style="mso-style-priority:100 !important;text-decoration:none;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;color:#FFFFFF;font-size:16px;padding:10px 20px 10px 20px;display:block;background:#ff0090;border-radius:32px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:19px;width:auto;text-align:center;mso-padding-alt:0;mso-border-alt:10px solid #ff0090;padding-left:5px;padding-right:5px">
                                {{ $data['buttonText'] }}
                            </a>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:0;margin:0;padding-bottom:15px;">
                        <p style="margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:19px;color:#0a1b33;font-size:16px">
                            {!! $data['text2'] !!}
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endsection