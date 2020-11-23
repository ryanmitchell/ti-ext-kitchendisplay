## Order Views (formerly Kitchen Display)

Adds the ability to create multiple order processing views designed to facilitate expediting orders through the restaurant.

The views will show a breakdown of any incomplete orders or orders that match the criteria set by the view specfications. View specifications are user selectable and include options for Location(s), initial status to appear on the view, Order type, category (facilitate "station" display panels), assignee (facilitate delivery drivers and Pickup station), number of orders to display and refresh interval.

The orders are listed oldest to newest based on their scheduled pick-up time.

Buttons appear on the view that enables touch capability to move an order to a new status. Designed to allow Kitchen to see Received orders with buttons for Prep and Ready such that the Kitchen staff can move an order to the Preperation status with the touch of one button. If the "Notify Customer" option is selected on the status definition then this extension will send an email to the customer notifying them of their order's new status. Same when the order is Ready; simple one touch by Kitchen will remove the order from the Kitchen View and notify the customer that their order is ready.

The key to understand about the button option selections is that the status of button 3 determines whether the order remains on the view or if it is removed from the view when the current view is processing the order. 

To continue processing the order, another view is required at the "Pickup/Waiter Station". This view will show any order with an initial status of Ready. The buttons to process the order are only a Complete button as that is the last process step. Once Complete (meaning the order has been picked up or the wait staff has delivered the order) the order will drop off the "Pickup/Waiter Station" view.

Security has been added to allow the admin to secure the system by granting access to the Order Views (Kitchen Display) menu under the Sales menu. This facilitates a Kitchen user or Delivery Driver access to the view, but not having access to the rest of the admin pages.

### Installation

1. Add to extensions/thoughtco/kitchendisplay inside Tasty Igniter install.
2. Enable extension in Systems -> Extensions
3. Add any additional statuses you require as an order is processed, i.e. Ready to Pick-up, In Kitchen, Protien Station Complete, Fryer Station Complete, etc.
4. All settings are controlled within the Order Views (Kitchen Display) menu within each View defined by admin users.
5. Open the Order Views (Kitchen Display) menu under Sales to define and View new views
    a. Click New to create a new view
    b. Name your new view and make selections on the 3 tabs (General, Buttons, Cards) that match the desired "view" you want a user (Kitchen, Pickup, Delivery Driver, etc.) to see.

For support with this extension please post any issues on the github repository.
