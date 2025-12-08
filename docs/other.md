[Back to index](../README.md)

Other
=====

### Reseting sequential numbers
Either create a ready-to-use cron controller: `symfony console make:oib:cron_controller` 

or reset them by:
``` php
use Psys\OrderInvoiceBundle\Service\InvoiceManager\InvoiceManager;
...
$invoiceManager->resetSequentialNumbers();
```