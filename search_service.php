<?php

//ini_set('display_errors','On');
 //error_reporting(E_ALL);
require ( "sphinxapi.php" );
//phpinfo();
//exit();

function run_query() {
	
	if ($_GET["search_str"] <> "")
	{
		$tmp_var = do_query($_GET["search_str"]);
	
		echo json_encode($tmp_var);

	}	
	else 
	{
		echo json_encode(array("-1"=>array("")));
		
	}
	
}
	
	run_query();
	

function do_query($search_str) {
	
	//$tmp_var = array(array('itemName' => "test1"), array('itemName' => "test2"), array('itemName' => "test3"));
	//echo implode(",",tmp_var);
	//echo json_encode($tmp_var);
	//return tmp_var;
	
	$q = "";	
	$sql = "";
	$mode = SPH_MATCH_ALL;
	$host = "localhost";
	$port = 9312;
	$index = "*";
	$groupby = "";
	$groupsort = "@group desc";
	$filter = "group_id";
	$filtervals = array();
	$distinct = "";
	$sortby = "";
	$sortexpr = "";
	$limit = 20;
	$ranker = SPH_RANK_PROXIMITY_BM25;
	$select = "*";

	$cl = new SphinxClient ();
	$cl->SetServer ( $host, $port );
	$cl->SetConnectTimeout ( 1 );
	$cl->SetArrayResult ( true );
	$cl->SetWeights ( array ( 100, 1 ) );
	$cl->SetMatchMode ( $mode );
	if ( count($filtervals) )	$cl->SetFilter ( $filter, $filtervals );
	if ( $groupby )				$cl->SetGroupBy ( $groupby, SPH_GROUPBY_ATTR, $groupsort );
	if ( $sortby )				$cl->SetSortMode ( SPH_SORT_EXTENDED, $sortby );
	if ( $sortexpr )			$cl->SetSortMode ( SPH_SORT_EXPR, $sortexpr );
	if ( $distinct )			$cl->SetGroupDistinct ( $distinct );
	if ( $select )				$cl->SetSelect ( $select );
	if ( $limit )				$cl->SetLimits ( 0, $limit, ( $limit>1000 ) ? $limit : 1000 );
	$cl->SetRankingMode ( $ranker );
	$res = $cl->Query ( $search_str, $index );
	
	//return $res;
		
	if ( is_array($res["matches"]) )
	{
		$results = array();
		$n = 1;
		//print "Matches:\n";
		foreach ( $res["matches"] as $docinfo )
		{
			//print "$n. doc_id=$docinfo[id], weight=$docinfo[weight]";
			$attr_array  = array();
			$results[$docinfo[id]];
			foreach ( $res["attrs"] as $attrname => $attrtype )
			{ 
				$value = $docinfo["attrs"][$attrname];
				if ( $attrtype==SPH_ATTR_MULTI || $attrtype==SPH_ATTR_MULTI64 )
				{
					$value = "(" . join ( ",", $value ) .")";
				} else
				{
					if ( $attrtype==SPH_ATTR_TIMESTAMP )
						$value = date ( "Y-m-d H:i:s", $value );
				}
				$attr_array[$attrname] = $value;
				//print $value;
					
			}
			$results[$docinfo[id]] = $attr_array;
			
			$n++;
			//print implode("",$results)."\n"; 
		}
			return $results;
	}

}

?>