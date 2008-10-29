<?xml version="1.0" encoding="UTF-8"?>
<xrds:XRDS
    xmlns:xrds="xri://$xrds"
    xmlns="xri://$xrd*($v*2.0)"
    xmlns:openid="http://openid.net/xmlns/1.0">
  <XRD>
    <Service priority="10">
      <Type>{$type2}</Type>
      <URI>{$provider}</URI>
      <LocalID>{$local_id}</LocalID>
    </Service>
    <Service priority="20">
      <Type>{$type1}</Type>
      <URI>{$provider}</URI>
      <openid:Delegate>{$local_id}</openid:Delegate>
    </Service>
  </XRD>
</xrds:XRDS>
