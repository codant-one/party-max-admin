@extends("emails.layouts.layout")

@section("content")
    <form method="post" action="https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/">
        <input name="merchantId"      type="hidden"  value="508029"   >
        <input name="accountId"       type="hidden"  value="512321" >
        <input name="description"     type="hidden"  value="APPROVED"  >
        <input name="referenceCode"   type="hidden"  value="partymax8" >
        <input name="amount"          type="hidden"  value="100"   >
        <input name="tax"             type="hidden"  value="0"  >
        <input name="taxReturnBase"   type="hidden"  value="0" >
        <input name="currency"        type="hidden"  value="COP" >
        <input name="signature"       type="hidden"  value="f2f2f40d38faa17c19f051c443484520"  >
        <input name="test"            type="hidden"  value="1" >
        <input name="buyerEmail"      type="hidden"  value="test@test.com" >
        <input name="responseUrl"     type="hidden"  value="{{env('APP_URL')}}/api/payment/response" >
        <input name="confirmationUrl" type="hidden"  value="{{env('APP_URL')}}/api/payment/confirmation" >
        <input name="Submit"          type="submit"  value="Enviar" >
    </form>
@endsection