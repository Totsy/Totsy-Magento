DELETE FROM customer_form_attribute WHERE form_code = 'adminhtml_customer' AND attribute_id = 218 LIMIT 1;
INSERT INTO customer_form_attribute (form_code, attribute_id) VALUES ('adminhtml_customer', 219);
