Openfactura - WooCommerce
=========
- SitioWeb: https://www.openfactura.cl
- Comunidad: [Chat Slack]([https://communityinviter.com/apps/haulmer/haulmer](https://communityinviter.com/apps/haulmer/haulmer))
- Documentación: [API Rest]([http://docsapi-openfactura.haulmer.com/](http://docsapi-openfactura.haulmer.com/))
- Tutorial de instalación: [Haulmer Blog - Tutorial](https://www.haulmer.com/docs/como-instalar-el-plugin-de-openfactura-en-woocommerce/)
<img alt="OpenFactura-WooCommerce" src="https://www.haulmer.com/docs/content/images/2020/01/3WocommerceOF.png" width="600px">
OpenFactura es la solución de facturación electrónica N°1 de Chile, a través de este plugin/integración podrás disfrutar de todos sus beneficios.
Los beneficios del plugin OpenFactura son:
- **Emisión ilimitada**: Al igual que nuestra [plataforma de facturación web](http://learn-openfactura.haulmer.com), no colocamos límites a la cantidad de documentos tributarios electrónicos que podrás emitir al [integrar OpenFactura](https://www.openfactura.cl/) a tu ecommerce, así que no importa si emites diez o miles de boletas y facturas, el costo será siempre el mismo.
- **Envío automático de documentos**: Una vez finalizada la compra y segun el modo de emisión que hayas establecido en el plugin (modo automático o modo autoservicio), al emitirse la boleta o factura la enviaremos al instante a tu cliente para que este elija si imprimirla o compartirla.
- **Comparte, descarga e imprime**: Cuando pensamos en crear esta función buscábamos además de facilitar la facturación de tu negocio, conceder tanto a ti como a tus clientes algunos beneficios extras. La capacidad de compartir con facilidad, descargar, imprimir el documento o visualizarlo en formato pdf o xml es uno de ellos.
- **Personaliza la página de emisión para clientes**: Para más confianza, te concedemos también la posibilidad de subir tu logo a la página de emisión en la que tus clientes generarán sus documentos (modo autoservicio), de manera que estando en ella sepan que aún permanecen en tu tienda y que los datos que deberán completar -en el caso de la facturación- están seguros.
- **Compatible 100% con el S.I.I.**: Todos los documentos que se emitan en tu ecommerce ya sea a través de la página de emisión -por tus clientes- o aquellos que se generen automáticamente, serán enviados al SII al instante y cuentan con todas las exigencias de la ley.
Para más información, puedes visitar nuestro sitio web: [https://www.openfactura.cl/factura-electronica/integraciones/](https://www.openfactura.cl/factura-electronica/integraciones/).
Funcionamiento del plugin
-------------------------------
Todo el funcionamiento del plugin radica en el hook de WooCommerce `woocommerce_order_status_completed`, cuando este hook es gatillado comienzan a realizarse las siguientes rutinas:
 1. Genera y realiza el envío de la información a OpenFactura para la generación del DTE.
 2. Cuando llega la respuesta desde OpenFactura, se almacena en las notas de la orden; el tipo de documento, folio y el link de autoservicio.
 3. Se completa información de los meta `_invoice_serial`, `_document_code`, `_document_type`. De utilidad para otras integraciones dentro de WooCommerce.
Instalación plugin
-------------------------------
La documentación está disponible en nuestro sitio de tutoriales [Haulmer  Docs](https://www.haulmer.com/docs/):
  - [Guía de Instalación del plugin woocommerce](https://www.haulmer.com/docs/como-instalar-el-plugin-de-openfactura-en-woocommerce/)