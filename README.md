CS-Cart-hosted (v4+)
====================

INTRODUCTION
------------

This module enables CS-Cart (v4+) customers to pay for their items using the Cardstream hosted
form payment gateway.

INSTALLATION
------------

 1. Unzip / untar and merge the httpdocs folder into the root folder of your CS-Cart installation.

 2. Run the SQL found in install.sql on the Database you are using for your CS-Cart installation.
 	NOTE: As a requirement set by CS-Cart, all third party payment gateways must specify a processor_id 
 	of 1000 or over in the cscart_payment_processors table. The SQL query will insert a record with a 
 	processor_id of 1000. It is advisable to check that the processor_id is not already in use with 
 	another gateway otherwise it will get overwritten. If the processor_id is already in use then
 	you should alter the SQL query to use a free id over 1000 before you run it.

 3. Login to your CS-Cart installation and navigate to Administration > Payment Methods from the 
 	top menu
 
 4. Click the '+' symbol at the top of the page to add a new payment method.
 
 5. Fill out the form and select Cardstream as the processor. Click on the 'Configure' tab and 
 	enter the relevant information before clicking 'Create'.
 
 6. The module will now be available as a payment method in the checkout procedure.