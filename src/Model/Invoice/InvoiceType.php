<?php
namespace Psys\OrderInvoiceBundle\Model\Invoice;


enum InvoiceType :int
{
    case PROFORMA = 1;
    case FINAL = 2;

    public static function fromName(string $name): self
    {
         $name = strtoupper($name);

        foreach (self::cases() as $status) {
            if( $name === $status->name ){
                return $status;
            }
        }
        throw new \ValueError("$name is not a valid case name for enum " . self::class );
    }
}
?>