
## 1.6 to 1.7
- The following methods now return string instead of float: `getPriceVatIncluded`, `getPriceVatExcluded`, `getVatRate`, `getVat`
- The following methods now require string as an argument instead of float: `setPriceVatIncluded`, `setPriceVatExcluded`, `setVatRate`, `setVat`
- Optional: update your mPDF twig template or stylesheet: `symfony console make:oib:invoice:mpdf_twig_template`


## 1.5 to 1.6
- Change version in composer.json to `"psys/order-invoice-bundle": "^1.6",` and run: `composer update`
- Backup your database and run: `symfony console oib:upgrade:15_to_16`
- Make sure `setUniquePaymentReference` is executed after order was saved.
- Remove `->setPaidAt` from order.
- Optional: remove `->setState(State::UNPAID)` when creating new order
- Optional: remove `->setCreatedAt`  when creating new order/invoice/file
- Optional: update your mPDF twig template or stylesheet: `symfony console make:oib:invoice:mpdf_twig_template`


## 1.4 to 1.5
- Change version in composer.json to `"psys/order-invoice-bundle": "^1.5",` and run: `composer update`
- Backup your database and run: `symfony console oib:upgrade:14_to_15`
- Remove: `->getInvoice()`, `new Invoice()`
- If proforma or regular invoice is being issued in your app, items need to be added directly to them.
- Rename `invoiceSeller` to `seller`
- Rename `InvoiceSeller` to `Seller`
- Rename `invoiceBuyer` to `buyer`
- Rename `InvoiceBuyer` to `Buyer`
- Optional: update your mPDF twig template: `symfony console make:oib:invoice:mpdf_twig_template`


## 1.4.5 to 1.4.6
- Run: `composer update`
- Run: `symfony console make:migration` --> `symfony console doctrine:migrations:migrate`


## 1.4.0 to 1.4.2
- Run: `composer update`
- Run: `symfony console make:migration` --> `symfony console doctrine:migrations:migrate`
- Optional: Update your mPDF twig template: `symfony console make:oib:invoice:mpdf_twig_template`


## 1.3 to 1.4
- Change version in composer.json to `"psys/order-invoice-bundle": "^1.4",` and run: `composer update`
- Run: `symfony console oib:upgrade:13_to_14`
- Rename `State::NEW` to `State::UNPAID`
- Rename `orderItem` to `item`
- Rename `OrderItem` to `Item`
- Rename `variableSymbol` to `paymentReference`
- Rename `VariableSymbol` to `PaymentReference`
- Rename `processAndSaveNewOrder` to `save`
- If a custom binary provider is being used, add third parameter `?InvoiceAdvance $invoiceAdvance = null` to the `getBinary` method. 


## 1.3.2 to 1.3.3
- Run: `composer update`
- Run: `symfony console make:migration` --> `symfony console doctrine:migrations:migrate`


## 1.2 to 1.3
- Change version in composer.json to `"psys/order-invoice-bundle": "^1.3",` and run: `composer update`
- Run: `symfony console oib:upgrade:12_to_13`

During this process, you can either continue using your current (custom) file entity or switch to the new default file entity. If you decide to keep using your current (custom) file entity, you don't have to change anything. If you choose to switch to the new file entity (table `oi_file`), your current file records will not be automatically transferred to the new table `oi_file`.