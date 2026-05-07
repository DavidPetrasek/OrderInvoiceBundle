[Back to index](index.md)

Logic
=====
- A new order can be saved without an invoice. The invoice can be added later.

- If proforma and/or advance invoice/s exist, items added to the order represent the total price which allows to show the total remaining due at any given time.

- If proforma is marked as payable:
    1. payment details are shown.
    2. and if no advance invoice exists, it's used for deduction on the final invoice.

### Status update
- If at least a single advance invoice was marked as paid, order's state is automatically set to `PARTIALLY_PAID`
- If final or regular invoice was marked as paid, order's state is automatically set to `PAID`
- If the total amount due is equal to zero, after deducting advance invoice payments, the final invoice is automatically marked as paid.