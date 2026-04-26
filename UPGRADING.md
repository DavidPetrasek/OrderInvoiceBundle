
## 1.4.5 to 1.4.6
- Run: `symfony console make:migration` --> `symfony console doctrine:migrations:migrate`


## 1.4.0 to 1.4.2
- Run: `symfony console make:migration` --> `symfony console doctrine:migrations:migrate`
- Optional: Update your mPDF twig template by: `symfony console make:oib:invoice:mpdf_twig_template`


## 1.3.3 to 1.4.0
- Run: `symfony console oib:upgrade:13_to_14`
- Rename `State::NEW` to `State::UNPAID`
- Rename `orderItem` to `item`
- Rename `OrderItem` to `Item`
- Rename `variableSymbol` to `paymentReference`
- Rename `VariableSymbol` to `PaymentReference`
- Rename `processAndSaveNewOrder` to `save`
- If a custom binary provider is being used, add third parameter `?InvoiceAdvance $ent_InvoiceAdvance = null` to the `getBinary` method. 


## 1.3.2 to 1.3.3
- Run: `symfony console make:migration` --> `symfony console doctrine:migrations:migrate`


## 1.2 to 1.3
- Run: `symfony console oib:upgrade:12_to_13`

During this process, you can either continue using your current (custom) file entity or switch to the new default file entity. If you decide to keep using your current (custom) file entity, you don't have to change anything. If you choose to switch to the new file entity (table `oi_file`), your current file records will not be automatically transferred to the new table `oi_file`.