# TODO List
## Validations
- [ ] Ensure all GET links are validated to ensure they redirect user if the ID in the URL is not permitted to view by them.
- [ ] Combine order details and info queries together, keep cart query separate.
- [ ] Add more security to queries by including extra WHERE clauses. e.g. for order cancellation, add exta where clauses to check customer_id, order_status, etc.