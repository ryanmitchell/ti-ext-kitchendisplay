## Kitchen Display

Adds the ability to create multiple order processing views designed to facilitate expediting orders through the restaurant.

This is free and doesn't require a license, but you can [donate to Ryan](https://github.com/sponsors/ryanmitchell), the developer behind it, to show your appreciation.

### Usage

The views will show a breakdown of any incomplete orders or orders that match the criteria set by the view specifications. View specifications are user selectable and include options for location(s), initial status to appear on the view, order type, category (facilitating "station" display panels), assignee (facilitating delivery drivers and pickup stations), number of orders to display and refresh interval.

The orders are listed based on their scheduled pick-up time.

Buttons appear on the view that enables touch capability to move an order to a new status of your selection. If the "Notify Customer" option is selected on the status definition then this extension will send an email to the customer notifying them of their order's new status. The status of button 3 determines whether the order remains on the view or if it is removed from the view when the current view is processing the order. 

Multiple views can be set up to allow different types of order processing, for example you could set up a view for each station limited by the category of order items, or you could set up a view for delivery orders for your delivery drivers.

Security has been added to allow the admin to secure the system by granting access to the Kitchen Display menu under the Sales menu. This facilitates a Kitchen user or Delivery Driver access to the view, but not having access to the rest of the admin pages.

### Installation

1. Add to extensions/thoughtco/kitchendisplay inside Tasty Igniter install.
2. Enable extension in Systems -> Extensions
3. Add any additional statuses you require as an order is processed, i.e. Ready to Pick-up, In Kitchen, Protien Station Complete, Fryer Station Complete, etc.
4. All settings are controlled within the Order Views (Kitchen Display) menu within each View defined by admin users.
5. Open the Order Views (Kitchen Display) menu under Sales to define and View new views
    a. Click New to create a new view
    b. Name your new view and make selections on the 3 tabs (General, Buttons, Cards) that match the desired "view" you want a user (Kitchen, Pickup, Delivery Driver, etc.) to see.

For support with this extension please post any issues on the Github repository.
