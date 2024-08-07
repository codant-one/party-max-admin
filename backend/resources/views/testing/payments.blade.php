@extends("emails.layouts.layout")

@section("content")
    <form method="post" action="https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/">
        <input name="merchantId"      type="hidden"  value="508029"   >
        <input name="accountId"       type="hidden"  value="512321" >
        <input name="description"     type="hidden"  value="APPROVED"  >
        <input name="referenceCode"   type="hidden"  value="00200002" >
        <input name="amount"          type="hidden"  value="36000"   >
        <input name="tax"             type="hidden"  value="0"  >
        <input name="taxReturnBase"   type="hidden"  value="0" >
        <input name="currency"        type="hidden"  value="COP" >
        <input name="signature"       type="hidden"  value="c831d3ad112225ddb04260bf3557961a"  >
        <input name="test"            type="hidden"  value="1" >
        <input name="buyerEmail"      type="hidden"  value="test@test.com" >
        <input name="responseUrl"     type="hidden"  value="{{env('APP_URL')}}/api/payments/response" >
        <input name="confirmationUrl" type="hidden"  value="{{env('APP_URL')}}/api/payments/confirmation" >
        <input name="Submit"          type="submit"  value="Enviar" >
    </form>
@endsection