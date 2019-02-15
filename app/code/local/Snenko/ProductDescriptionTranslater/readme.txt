добавити:
- massAction ProductPage


SELECT entity_id FROM catalog_product_entity_text WHERE entity_id NOT IN (SELECT entity_id FROM catalog_product_entity_text WHERE attribute_id IN( SELECT attribute_id FROM eav_attribute WHERE attribute_code IN (name,description,short_description)) AND store_id = 4) AND entity_id in (SELECT entity_id                  FROM catalog_product_entity_int		          WHERE value in (4)                  AND attribute_id = 102) GROUP BY entity_id