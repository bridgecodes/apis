<?php

require 'Slim/Slim.php';

$app = new Slim();


$app->get('/parent-cat','getParentCat');
$app->get('/subcat/:id',	'getSubCat');
$app->get('/cat-item-list/:id',	'getItemCat');
$app->get('/get-item-details/:id',	'getItemDetails');
$app->get('/test/:id',	'test');

$app->run();
function getParentCat() {
	$sql = "SELECT os_t_category_description.s_name, os_t_category_description.s_slug, os_t_category.pk_i_id
FROM os_t_category
INNER JOIN os_t_category_description ON os_t_category.pk_i_id = os_t_category_description.fk_i_category_id
WHERE os_t_category.fk_i_parent_id IS NULL
AND os_t_category_description.fk_c_locale_code =  'en_US'"
;
	

	try {
		$db = getConnection();
		$stmt = $db->query($sql);  
		$items = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"items": ' . json_encode($items) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function getSubCat($id) {
	$sql = " SELECT os_t_category_description.s_name, os_t_category_description.s_slug, os_t_category.pk_i_id
FROM os_t_category
INNER JOIN os_t_category_description ON os_t_category.pk_i_id = os_t_category_description.fk_i_category_id
WHERE os_t_category.fk_i_parent_id =:id
AND os_t_category_description.fk_c_locale_code =  'en_US'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		$items = $stmt->fetchAll(PDO::FETCH_OBJ);		
		$db = null;
	
		echo '{"subcat": ' . json_encode($items) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function getItemCat($id)  {
	$sql = "SELECT DISTINCT (os_t_item.pk_i_id) , os_t_item_description.s_title, os_t_item.dt_pub_date, os_t_item.i_price, os_t_item.fk_c_currency_code, os_t_category_description.s_name,os_t_item_resource.pk_i_id AS image_name,os_t_item_resource.s_extension as image_format,os_t_item_resource.s_path as img_url, os_t_item_location.s_region as state,os_t_item_location.s_city as city
FROM os_t_item 
LEFT OUTER JOIN os_t_category_description ON os_t_item.fk_i_category_id=os_t_category_description.fk_i_category_id

inner JOIN os_t_item_description ON os_t_item_description.fk_i_item_id = os_t_item.pk_i_id
left JOIN os_t_item_resource ON os_t_item.pk_i_id = os_t_item_resource.fk_i_item_id
left join os_t_item_location on os_t_item_location.fk_i_item_id=os_t_item.pk_i_id

where os_t_category_description.fk_c_locale_code='en_US' AND 
os_t_item.fk_i_category_id=:id group by os_t_item.pk_i_id order by os_t_item.dt_pub_date  ";
$sql2="SET character_set_results=utf8";

	try {
		$db = getConnection();
		$stmt2 = $db->prepare($sql2); 
		$stmt = $db->prepare($sql);  
		
		$stmt->bindParam("id", $id);
		$stmt2->execute();
		$stmt->execute();
	
		$items = $stmt->fetchAll(PDO::FETCH_OBJ);	
		$db = null;
			
		echo '{"items": ' . json_encode($items) .' }';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function getItemDetails($id) {
	$sql = "SELECT  os_t_item_description.s_title as item_name, os_t_item.dt_pub_date as publish_date, os_t_item.i_price as item_price, os_t_item.fk_c_currency_code as currency_code, os_t_category_description.s_name as category_name,os_t_item_description.s_description as item_description,os_t_item.s_contact_name as contact_name,os_t_item.s_contact_email as contact_email,min(os_t_item_meta.s_value) as contact_number,os_t_item_resource.s_extension as image_format,os_t_item_resource.s_path as img_url, os_t_item_location.s_region as state,os_t_item_location.s_city as city,os_t_item_location.s_city_area as area
FROM os_t_item
INNER JOIN os_t_category_description ON os_t_item.fk_i_category_id=os_t_category_description.fk_i_category_id

INNER JOIN os_t_item_description ON os_t_item_description.fk_i_item_id = os_t_item.pk_i_id
INNER JOIN os_t_item_meta ON os_t_item_meta.fk_i_item_id=os_t_item.pk_i_id
LEFT JOIN os_t_item_resource ON os_t_item.pk_i_id = os_t_item_resource.fk_i_item_id
left join os_t_item_location on os_t_item_location.fk_i_item_id=os_t_item.pk_i_id
where os_t_category_description.fk_c_locale_code='en_US' AND 
os_t_item.pk_i_id=:id";

$sql2="select pk_i_id as image_name from os_t_item_resource where fk_i_item_id=:id ";
$sql3="SET character_set_results=utf8";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql); 
		$stmt2 = $db->prepare($sql2); 
		$stmt3 = $db->prepare($sql3); 
		$stmt->bindParam("id", $id);
		$stmt2->bindParam("id", $id);
		$stmt3->execute();
		$stmt->execute();
		$stmt2->execute();
		$Item = $stmt->fetchAll(PDO::FETCH_OBJ);	
		$items = $stmt2->fetchAll(PDO::FETCH_OBJ);		
		$db = null;
		 $arr[] = array('body'=>$Item,'image'=>$items);
		echo '{"item": ' . json_encode($arr) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function test($id) {
	$sql = "SELECT DISTINCT (os_t_item.pk_i_id) , os_t_item_description.s_title, os_t_item.dt_pub_date, os_t_item.i_price, os_t_item.fk_c_currency_code, os_t_category_description.s_name,os_t_item_resource.pk_i_id AS image_name,os_t_item_resource.s_extension as image_format,os_t_item_resource.s_path as img_url, os_t_item_location.s_region as state,os_t_item_location.s_city as city
FROM os_t_item 
LEFT OUTER JOIN os_t_category_description ON os_t_item.fk_i_category_id=os_t_category_description.fk_i_category_id

inner JOIN os_t_item_description ON os_t_item_description.fk_i_item_id = os_t_item.pk_i_id
left JOIN os_t_item_resource ON os_t_item.pk_i_id = os_t_item_resource.fk_i_item_id
left join os_t_item_location on os_t_item_location.fk_i_item_id=os_t_item.pk_i_id

where os_t_category_description.fk_c_locale_code='en_US' AND 
os_t_item.fk_i_category_id=:id group by os_t_item.pk_i_id order by os_t_item.dt_pub_date  ";
$sql2="SET character_set_results=utf8";

	try {
		$db = getConnection();
		$stmt2 = $db->prepare($sql2); 
		$stmt = $db->prepare($sql);  
		
		$stmt->bindParam("id", $id);
		$stmt2->execute();
		$stmt->execute();
	
		$items = $stmt->fetchAll(PDO::FETCH_OBJ);	
		$db = null;
			
		echo '{"items": ' . json_encode($items) .' }';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}



function getConnection() {
	$dbhost="localhost";
	$dbuser="youtuh25_oscl577";
	$dbpass="S)15k4.FP2";
	$dbname="youtuh25_oscl577";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

?>