<?php
//ini_set('display_errors','On');
 //error_reporting(E_ALL);
require ( "sphinxapi.php" );
//phpinfo();
//exit();


 /**
 * ProcessSimpleType method
 * @param string $who name of the person we'll say hello to
 * @return string $helloText the hello  string
 */
function ProcessSimpleType($who) {
	return "Hello, how are you ? $who";
}

function test(	) {
	return array('val1'=>"test1", 'val2'=>"test2", 'val3'=>"test3");
}

function do_query($search_str) {
	
	$tmp_var = array(array('itemName' => "test1"), array('itemName' => "test2"), array('itemName' => "test3"));
	//echo implode(",",tmp_var);
	echo json_encode($tmp_var);
	return tmp_var;
	
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
			$results[$docinfo[id]] = implode(",",$attr_array);	
			
			$n++;
			//print implode("",$results)."\n"; 
		}
			return $results;
	}

}


require_once("nusoap/nusoap.php");
$namespace = "http://172.16.16.199/";
// create a new soap server
$server = new soap_server();
// configure our WSDL
$server->soap_defencoding = 'UTF-8'; 

$server->configureWSDL("SimpleService");
$server->configureWSDL("do_query");
// set our namespace
$server->wsdl->addComplexType(
  'ArrayOfString',
  'complexType',
  'array',
  'sequence',
  '',
  array(
    'itemName' => array(
      'name' => 'itemName', 
      'type' => 'xsd:string',
      'minOccurs' => '0', 
      'maxOccurs' => 'unbounded'
    )
  )
);
do_query();
//echo test();
//run_search("nathaniel");
$server->wsdl->schemaTargetNamespace = $namespace;
// register our WebMethod
$server->register(
                // method name:
                'ProcessSimpleType', 		 
                // parameter list:
                array('name'=>'xsd:string'), 
                // return value(s):
                array('return'=>'xsd:string'),
                // namespace:
                $namespace,
                // soapaction: (use default)
                false,
                // style: rpc or document
                'rpc',
                // use: encoded or literal
                'encoded',
                // description: documentation for the method
                'A simple Hello World web method');
$server->register(
                // method name:
                'do_query', 		 
                // parameter list:
                array('name'=>'xsd:string'), 
                // return value(s):
                array('return'=>'tns:ArrayOfString'),
                // namespace:
                $namespace,
                // soapaction: (use default)
                false,
                // style: rpc or document
                'rpc',
                // use: encoded or literal
                'encoded',
                // description: documentation for the method
                'Sphinx Search');
				
				
$server->register( 'test',

				array('name'=>'xsd:string'), 
                // return value(s):
                array('return'=>'tns:ArrayOfString'),
                // namespace:
                $namespace,
                // soapaction: (use default)
                false,
                // style: rpc or document
                'rpc',
                // use: encoded or literal
                'encoded',
                // description: documentation for the method
                'Sphinx test');
// Get our posted data if the service is being consumed
// otherwise leave this data blank.                
$POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) 
                ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';

// pass our posted data (or nothing) to the soap service                    
$server->service($POST_DATA);                
exit();
?>