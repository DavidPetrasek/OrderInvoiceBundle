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
    *   Run this cron between 1 minute and the specified number of minutes before the new year.
    */
    #[Route('/reset_sequential_numbers_every_year', methods: ['GET'])]
    public function reset_sequential_numbers_every_year(): Response
    {
        $debug = $this->invoiceManager->resetSequentialNumbersEveryYear(checkMinutesInAdvance: 10);

        return new Response(
            $debug,
            Response::HTTP_OK
        );
    }
}
