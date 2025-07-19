<?php
namespace Psys\OrderInvoiceBundle\Service\InvoiceManager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Psys\OrderInvoiceBundle\Entity\Invoice;
use Symfony\Component\HttpFoundation\Response;

use Psys\OrderInvoiceBundle\Entity\InvoiceProforma;
use Psys\OrderInvoiceBundle\Entity\InvoiceFinal;


class InvoiceManager
{   
    public function __construct 
    (
        private readonly EntityManagerInterface $entityManager,
    )
    {}  
    
    /**
     * Sets a unique variable symbol for the given invoice.
     * The variable symbol is a numeric string of specified length that is not already used in the database.
     */
    public function setUniqueVariableSymbol(Invoice $invoice, int $length = 10): void
    {   
        $dbConn = $this->entityManager->getConnection();
        $this->entityManager->getConnection()->executeStatement('LOCK TABLES oi_invoice WRITE;');
        
        $variableSymbol = $this->generateUniqueVariableSymbol($length);
        
        $dbConn->executeStatement
        (
            "UPDATE oi_invoice SET variable_symbol = :variable_symbol WHERE id = :invoice_id;",
            [
                'variable_symbol' => $variableSymbol,
                'invoice_id' => $invoice->getId()
            ]
        );
        $invoice->setVariableSymbol($variableSymbol);
        
        $dbConn->executeStatement('UNLOCK TABLES;');
    }

    /**
     * Sets the sequential number for the given invoice type (proforma or final) based on the current counter value in the the settings which is increased at the same time.
     */
    public function setSequentialNumber(InvoiceProforma|InvoiceFinal $invoiceSpecific): void
    {        
        $dbConn = $this->entityManager->getConnection();
        $this->entityManager->getConnection()->executeStatement('LOCK TABLES oi_settings WRITE;');
        
        if      ($invoiceSpecific instanceof InvoiceProforma) {$type = 'proforma';}
        else if ($invoiceSpecific instanceof InvoiceFinal)    {$type = 'final';}

        $resultSet = $dbConn->executeQuery
        (
            "SELECT value FROM oi_settings WHERE option = :option;",
            [
                'option' => "invoice_{$type}_sequential_number"
            ]
        );
        $invoiceSpecific->setSequentialNumber($resultSet->fetchOne());
        
        $dbConn->executeStatement
        (
            "UPDATE oi_settings SET value = value+1 WHERE option = :option;",
            [
                'option' => "invoice_{$type}_sequential_number"
            ]
            );
        
        $dbConn->executeStatement('UNLOCK TABLES;');
    }  
    
    /**
     * Generates numeric string that is not already used in the database
     */
    private function generateUniqueVariableSymbol($length): string
    {
        $variableSymbol = $this->random_digits($length);

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('variable_symbol', 'variable_symbol');

        $query = $this->entityManager->createNativeQuery
        ('
            SELECT variable_symbol FROM oi_invoice 
            WHERE variable_symbol = ?'
        , $rsm);
        $query->setParameter(1, $variableSymbol);

        $kodVarDB = $query->getResult();

        if (!empty($kodVarDB))
        {
            $variableSymbol = $this->generateUniqueVariableSymbol($length);
        }

        return $variableSymbol;
    }

    private function random_digits(int $length): string 
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) 
        {
            $result .= random_int(0, 9);
        }
        return $result;
    }
    

    /**
     * Resets the sequential numbers for invoices every year. It's meant to be used inside a cron which needs to be run 1 to 10 minutes before a new year.
     *
     * This method checks if the current year is different from the next year (10 minutes in the future).
     * If the years are different, it waits until the next year and then calls the `resetSequentialNumbers()`
     *
     * @return string - Verbose debug information
     */
    public function resetSequentialNumbersEveryYear(): string
    {
        $debug = '';

        $currYear = date("Y");
        $debug .= '<br> Current year: ' . $currYear;

        $now = time() + (60 * 10);
        $nextYear = date("Y", $now);
        $debug .= '<br> Next year (10 minutes in the future): ' . $nextYear;

        if ($currYear !== $nextYear) {
            // Wait for the next year
            sleep(60);
            while (date("Y") === $currYear) {
                sleep(10);
                $debug .= '<br> Waiting for the next year: ' . $nextYear;
            }

            $this->resetSequentialNumbers();
            $debug .= '<br><br>Sequential numbers have been reset for the new year.';
        }

        return $debug;
    }

    /**
     * Resets the sequential numbers for both proforma and final invoices.
     */
    public function resetSequentialNumbers(): void
    {            
        $dbConn = $this->entityManager->getConnection();
        $this->entityManager->getConnection()->executeStatement('LOCK TABLES oi_settings WRITE;');

        $dbConn->executeStatement
        (
            "UPDATE oi_settings SET value = 1 WHERE option = 'invoice_proforma_sequential_number' OR option = 'invoice_final_sequential_number';"
        );

        $dbConn->executeStatement('UNLOCK TABLES;');

        $this->entityManager->flush();
    }
}
