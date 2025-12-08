[Back to index](../README.md)

File management
===============

### Save invoice
Persist the invoice file binary to disk and save a reference to it in the database. File is overwritten if already exists:

``` php
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Service\FilePersister\FilePersister;

public function saveProformaInvoice (FilePersister $filePersister, Order $ent_Order, string $binary) : void
{
    $fileInfo = $filePersister->persistProforma($binary, $ent_Order);
}
```
If using the default File entity, no more work is needed.

If a custom File entity is used you need to persist it in the database on your own based the returned array `$fileInfo` which has this structure: 
``` php
[
    'mimeType' => string,
    'nameFileSystem' => string,
    'nameDisplay' => string,
];
```


### Delete invoice
Delete the invoice file from the disk and remove its reference from the database:

``` php
use Psys\OrderInvoiceBundle\Entity\Order;
use Psys\OrderInvoiceBundle\Service\FileDeleter\FileDeleter;

public function deleteProformaInvoice (FileDeleter $fileDeleter, Order $ent_Order) : void
{
    $fileDeleter->deleteProforma($ent_Order);
}
```
If using the default File entity, no more work is needed.

If a custom File entity is used you need to also provide the file name you stored earlier: 
``` php
$fileDeleter->deleteProforma($ent_Order, 'invoice123.pdf');
```