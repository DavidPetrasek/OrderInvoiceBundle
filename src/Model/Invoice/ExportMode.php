<?php
namespace Psys\OrderInvoiceBundle\Model\Invoice;

enum ExportMode: int
{
    case BINARY = 1;
    case INLINE = 2;
    case DOWNLOAD = 3;
    case FILE = 4;
}