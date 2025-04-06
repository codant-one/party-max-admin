<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="telephone=no" name="format-detection">
    <title>Cotización PARTYMAX</title>
    <style>
      @page {
        margin: 170px 0 210px 0;
      }

      body {
        margin: 0;
        padding: 0;
        font-family: 'switzer', Arial, sans-serif !important;
        font-size: 14px;
      }

      table {
        border-spacing: unset;
        font-weight: 400;
        letter-spacing: normal;
        text-transform: none;
      }

      table tr, table td {
        height: 20px;
      }
    
      header {
        position: fixed;
        top: -170px;
        left: 0;
        right: 0;
        height: 170px;
        background-image: url('{{ asset('/images/letterhead_header.jpg') }}');
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
        text-align: center;
        line-height: 80px;
        font-size: 18px;
      }
    
      footer {
        position: fixed;
        bottom: -210px; 
        left: 0;
        right: 0;
        height: 210px;
        background-image: url('{{ asset('/images/letterhead_footer.jpg') }}');
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
        text-align: center;
        line-height: 80px;
        font-size: 16px;
      }

      .products {
        margin: 5px 0;
      } 
      
      .products_img {
        width: 5%;
        padding: 5px 0;
      } 
      
      .products_cant {
        width: 12%;
        text-align: right;
        margin: 5px 0;
      }  

      .content {
        padding: 0 50px;
      }

      .m-0 {
        margin: 0 !important;
      }

      .border-title {
        border-bottom: 1px solid #D9EEF2;
      }
  </style>
  </head>
  <body style="width:100%;font-family:arial, 'helvetica neue', helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;margin:0">
      <header></header>
      <footer></footer>
      <div class="content">
        <table cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top">
          <tr>
            <td>CLIENTE</td>
            <td style="text-align: right">COTIZACIÓN # {{$quote->id}}</td>
          </tr>
          <tr class="border-title">
            <td>
                <p class="m-0">Nombre: {{ $quote->name }}</p>
                @if($quote->company)
                <p class="m-0">Empresa: {{ $quote->company }}</p>
                @endif
                <p class="m-0">Documento: ({{ $quote->document_type->code }}) - {{ $quote->document }}</p>
                <p class="m-0">Teléfono: {{substr($quote->phone, 0, 3) === '+57' ? '' : '(+57)'}} {{ $quote->phone }}</p>
                <p class="m-0">Correo electrónico: {{ $quote->email }}</p>
            </td>
            <td style="text-align: right; vertical-align: top;">
                <p class="m-0">Fecha de solicitud: {{$quote->start_date}}</p>
                <p class="m-0">Fecha de vencimiento: {{$quote->due_date}}</p>
            </td>
          </tr>
        </table>
        <table cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;table-layout:fixed !important;width:100%;background-color:transparent;background-repeat:repeat;background-position:center top">
          <tr class="border-title">
            <td class="products_img">Productos</td>
            <td class="products"></td>
            <td class="products_cant">Cant.</td>
            <td class="products_cant">Unitario</td>
            <td class="products_cant">Total</td>
          </tr>
          @foreach($products as $product)
          <tr class="border-title">
              <td class="products_img">
                <a href="{{$product['slug']}}" class="es-button" target="_blank" style="width: 60px; min-height: 50px; border-radius: 8px; border: 1px solid #E2F8FC; text-align: center; align-items: center; justify-content: center; display: flex !important;">
                      <img src="{{ $product['product_image'] }}" width="90%">
                  </a>
              </td>
              <td class="products">
                <div style="justify-content: start; align-items: center; display: flex;">
                    <div style="display:block;">
                        <span style="display: block; font-size: 16px; color: #0a1b33; margin-left: 40px;">
                            {{ $product['product_name'] }}
                        </span>
                        <span style="display: block; font-size: 15px; color: #999999; margin-left: 40px;">
                            Color: {{ $product['color'] }}
                        </span>
                    </div>
                </div>
              </td>
              <td class="products_cant">
                {{ $product['quantity'] }}
              </td>
              <td class="products_cant">
                ${{ formatCurrency($product['product_price']) }}
              </td>
              <td class="products_cant">
                ${{ formatCurrency($product['product_total']) }}
              </td>
          </tr>
          @endforeach
          @foreach($services as $service)
          <tr class="border-title">
            <td class="products_img">
              <a href="{{$service['slug']}}" class="es-button" target="_blank" style="width: 60px; min-height: 50px; border-radius: 8px; border: 1px solid #E2F8FC; text-align: center; align-items: center; justify-content: center; display: flex !important;">
                  <img src="{{ $service['service_image'] }}" width="90%">
              </a>
            </td>
            <td class="products">         
                <div style="justify-content: start; align-items: center; display: flex;">
                    <div style="display:block;">
                        <span style="display: block; font-size: 16px; color: #0a1b33; margin-left: 40px;">
                            {{ $service['service_name'] }}
                        </span>
                        @if($service['cake_size'])
                        <span style="display: block; font-size: 15px; color: #999999; margin-left: 40px;">
                            Tamaño: {{ $service['cake_size'] }}
                        </span>
                        @endif
                        @if($service['flavor'])
                        <span style="display: block; font-size: 15px; color: #999999; margin-left: 40px;">
                            Sabor: {{ $service['flavor'] }}
                     
                        
                          @if($service['filling'])
                          <span >
                              / Relleno: {{ $service['filling'] }}
                          </span>
                          @endif
                        </span>
                        @endif
                    </div>
                </div>
            </td>     
            <td class="products_cant">
                {{ $service['quantity'] }}
              </td>
              <td class="products_cant">
                ${{ formatCurrency($service['service_price']) }}
              </td>
              <td class="products_cant">
                ${{ formatCurrency($service['service_total']) }}
              </td>  
          </tr>
          @endforeach
          <tr class="border-title">
            <td class="products_img"></td>
            <td class="products"></td>
            <td class="products_cant"></td>
            <td class="products_cant">
              <p class="text m-0">Subtotal:</p>
              <p class="text m-0">Tax:</p>
              <p class="text m-0">Total:</p>
            </td>
            <td class="products_cant" style="padding: 10px 0;">
              <p class="numbers m-0"><span>${{ formatCurrency($quote->sub_total) }}</span></p>
              <p class="numbers m-0"><span>${{ formatCurrency($quote->tax) }}</span></p>
              <p class="numbers m-0"><span>${{ formatCurrency($quote->total) }}</span></p>
            </td>
          </tr>
          <tr>
            <td colspan="5" style="padding: 10px 0;">
              <span class="ms-2">
                Los precios mostrados en esta cotización corresponden únicamente a los productos seleccionados. <br>
                El costo de envío no está incluido y se calculará por separado, según la dirección de entrega. <br>
                Todos nuestros productos en el marketplace incluyen el IVA. <br>
                Esta cotización tiene una validez de 8 días a partir de la fecha de emisión.
              </span>
            </td>
          </tr>
        </table>
      </div>
  </body>
</html>
