<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="product_import_template.csv"');
echo "name,description,price,sale_price,stock,sizes,colors,tags,status,category_name\n";
echo '"Floral Dress","Beautiful summer dress","1999","1499","50","XS,S,M,L,XL","Red,White,Blue","new,featured","active","Women\'s Fashion"';
