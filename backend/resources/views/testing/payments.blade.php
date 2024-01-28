@extends("emails.layouts.layout")

@section("content")
    <form method="post" action="https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/">
        <input name="merchantId"      type="hidden"  value="508029"   >
        <input name="accountId"       type="hidden"  value="512321" >
        <input name="description"     type="hidden"  value="APPROVED"  >
        <input name="referenceCode"   type="hidden"  value="partymax5" >
        <input name="amount"          type="hidden"  value="2000"   >
        <input name="tax"             type="hidden"  value="3193"  >
        <input name="taxReturnBase"   type="hidden"  value="16806" >
        <input name="currency"        type="hidden"  value="COP" >
        <input name="signature"       type="hidden"  value="0017e293bae8ffc4bb163c65768f1f00"  >
        <input name="test"            type="hidden"  value="true" >
        <input name="buyerEmail"      type="hidden"  value="test@test.com" >
        <input name="responseUrl"     type="hidden"  value="http://backend.partymax/api/payment/response" >
        <input name="confirmationUrl" type="hidden"  value="http://backend.partymax/api/payment/confirmation" >
        <input name="shippingAddress"    type="hidden"  value="calle 93 n 47 - 65"   >
        <input name="shippingCity"       type="hidden"  value="Bogotá" >
        <input name="shippingCountry"    type="hidden"  value="CO"  >
        <input name="Submit"          type="submit"  value="Enviar" >
    </form>
@endsection