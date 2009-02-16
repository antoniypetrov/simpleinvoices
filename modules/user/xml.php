<?php

header("Content-type: text/xml");

$start = (isset($_POST['start'])) ? $_POST['start'] : "0" ;
$dir = (isset($_POST['sortorder'])) ? $_POST['sortorder'] : "ASC" ;
$sort = (isset($_POST['sortname'])) ? $_POST['sortname'] : "email" ;
$rp = (isset($_POST['rp'])) ? $_POST['rp'] : "25" ;
$page = (isset($_POST['page'])) ? $_POST['page'] : "1" ;


function sql($type='', $dir, $sort, $rp, $page )
{
	global $config;
	global $auth_session;
	
	//SC: Safety checking values that will be directly subbed in
	if (intval($start) != $start) {
		$start = 0;
	}
	if (intval($rp) != $rp) {
		$rp = 25;
	}
	
	/*SQL Limit - start*/
	$start = (($page-1) * $rp);
	$limit = "LIMIT $start, $rp";

	if($type =="count")
	{
		unset($limit);
	}
	/*SQL Limit - end*/	
	
	if (!preg_match('/^(asc|desc)$/iD', $dir)) {
		$dir = 'ASC';
	}
	
	$query = $_POST['query'];
	$qtype = $_POST['qtype'];
	
	$where = " WHERE u.domain_id = :domain_id AND u.role_id = ur.id";
	if ($query) $where = " WHERE u.domain_id = :domain_id AND AND u.role_id = ur.id $qtype LIKE '%$query%' ";
	
	
	
	/*Check that the sort field is OK*/
	$validFields = array('id', 'role', 'email');
	
	if (in_array($sort, $validFields)) {
		$sort = $sort;
	} else {
		$sort = "email";
	}
	
		//$sql = "SELECT * FROM ".TB_PREFIX."customers ORDER BY $sort $dir LIMIT $start, $limit";
		$sql = "SELECT 
					u.id, 
					u.email, 
					ur.name
	
				FROM 
					".TB_PREFIX."user u,
					".TB_PREFIX."user_role ur
				$where
				ORDER BY 
					$sort $dir 
				$limit";
	
		$result = dbQuery($sql,':domain_id', $auth_session->domain_id) or die(htmlspecialchars(end($dbh->errorInfo())));
		return $result;
}

$sth = sql('', $dir, $sort, $rp, $page);
$sth_count_rows = sql('count',$dir, $sort, $rp, $page);

$user = $sth->fetchAll(PDO::FETCH_ASSOC);

$count = $sth_count_rows->rowCount();

//echo sql2xml($customers, $count);
$xml .= "<rows>";
$xml .= "<page>$page</page>";
$xml .= "<total>$count</total>";

foreach ($user as $row) {
	$xml .= "<row id='".$row['iso']."'>";
	$xml .= "<cell><![CDATA[
	<a class='index_table' title='$LANG[view] ".utf8_encode($row['name'])."' href='index.php?module=user&view=details&id=$row[id]&action=view'><img src='images/common/view.png' height='16' border='-5px' padding='-4px' valign='bottom' /></a>
	<a class='index_table' title='$LANG[edit] ".utf8_encode($row['name'])."' href='index.php?module=user&view=details&id=$row[id]&action=edit'><img src='images/common/edit.png' height='16' border='-5px' padding='-4px' valign='bottom' /></a>
	]]></cell>";
	$xml .= "<cell><![CDATA[".utf8_encode($row['email'])."]]></cell>";
	$xml .= "<cell><![CDATA[".utf8_encode($row['name'])."]]></cell>";
	$xml .= "</row>";		
}

$xml .= "</rows>";
echo $xml;

?> 