REPLACE INTO cscart_payment_processors (processor_id, processor, processor_script,
processor_template, admin_template, callback, type) values ('1000', 'Cardstream',
'cardstream.php', 'views/orders/components/payments/cc_outside.tpl',
'cardstream.tpl', 'N', 'P');