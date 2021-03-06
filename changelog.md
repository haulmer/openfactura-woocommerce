# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## V1.0.24 2021-05-27
### Fix
- Removed DescuentoMonto with value 0
- Changed Idempotency-Key by order_key

## V1.0.23 2021-05-12
### Fix
- Fixed montoItems calculations

## V1.0.22 2021-05-12
### Fix
- Fixed calculations of amounts

## V1.0.21 2021-05-05
### Fix
- Fixed total amount to display the one provided by woocomerce.

## V1.0.20 2021-04-05
### Fix
- Fixed emission fail if customer info is empty.

## V1.0.19 2021-02-25
### Fix
- Fixed encoding in item name and description.

## V1.0.18 2021-01-27
### Fix
- Fixed negative values as discount.

## V1.0.17 2021-01-18
### Fix
- Fixed Description max lenght.
- Fixed Direccion Sucursal displayed on ticket.

## V1.0.16 2021-01-12
### Fix
- Fixed Monto Total calculation with decimals.

## V1.0.15 2020-12-24
### Fix
- Stripped html tags from item description.

## V1.0.14 2020-12-22
### Fix
- Added non valuable items as note.
- Prevented issuing for a total of less than 10 clp.

## V1.0.13 2020-09-29
### Added
- origin in document.

## V1.0.12 2020-08-11
### fixed
- If organization does not have Sucursales,the parent company is incorporated into the list in order to be selected.

## Unreleased
## V1.0.11 2020-04-24
### Fix
- Round "MontoItem" and "PrcItem" in item.
- Added document code in order notes and order details.

## Unreleased
## V1.0.10 2020-04-14
### Fix
- Round "Mnttotal" in item.

## Unreleased
## V1.0.9 2020-04-06
### Fix
- Round "DescuentoMonto" in item.

## Unreleased
## V1.0.8 2020-03-27
### Added
- Added serial number and document type in order notes and order details.

## Unreleased
## V1.0.7 2020-03-17
### Fix
- Round total amount in fee item on total.

## Unreleased
## V1.0.6 2020-02-04
### Fix
- Round total amount in fee item.

## Unreleased
## V1.0.5 2020-01-29
### Added
- Option to insert the documents link in the "Order Completed" email 

## Unreleased
## V1.0.4 2020-01-28
### Added
- Write response of OpenFactua API in order notes.
- self-service link sending in payment email completed.

## Unreleased
## V1.0.3 2020-01-21
### Changed
- The way it is verified if a product is afecta or exenta.

## V1.0.2 2020-01-21
### Changed
- Mandatory product name condition
- Non-mandatory product description condition

## V1.0.1 2020-01-20
### Changed
- Acteco is saved correctly in the database.
### Added
- Addec unisntall.php to delete registries in database.
