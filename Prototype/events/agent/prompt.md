Update this Laravel EventServiceTest to stop creating new events and categories using factories or create() methods. Instead, fetch existing data from the database (which has been seeded via CSV). Ensure that:

test_it_can_filter_events_by_search picks an event title that actually exists in the DB.

test_it_can_filter_events_by_category fetches an existing category and an event associated with it.

test_it_can_update_an_event fetches the first available event and updates its title.

Keep using DatabaseTransactions to ensure no changes persist after tests.
