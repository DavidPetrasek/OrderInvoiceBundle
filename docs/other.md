[Back to index](../README.md)

Other
=====

### Categories
`symfony console make:oib:category` - Creates enum to specify custom categories for orders or order items


### Reseting sequential numbers
Either create a ready-to-use cron controller: `symfony console make:oib:cron_controller` 

or reset them by:
``` php
use Psys\OrderInvoiceBundle\Service\InvoiceManager\InvoiceManager;
...
$invoiceManager->resetSequentialNumbers();
```