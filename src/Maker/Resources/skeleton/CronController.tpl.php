<?= "<?php\n" ?>

namespace <?= $namespace ?>;

<?= $use_statements ?>


#[Route('/cron/oib')]
class <?= $class_name ?> extends AbstractController
{
    public function __construct
    (
        private readonly InvoiceManager $invoiceManager,
    )
    {}

    /**
    *   This cron needs to be run 1 to 10 minutes before a new year.
    */
    #[Route('/reset-sequential-numbers-every-year')]
    public function reset_sequential_numbers_every_year(): Response
    {
        $debug = $this->invoiceManager->resetSequentialNumbersEveryYear();

        return new Response(
            $debug,
            Response::HTTP_OK
        );
    }
}
