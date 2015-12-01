<?php

//include required class for build nnusoap web service server
  require_once('nusoap.php');
  
  // Create server object
   $server = new soap_server();

   // configure  WSDL
   $server->configureWSDL('hello world', 'urn:hellowsdl');

   // Register the method to expose
    $server->register('hello_world',                    // method
        array('name' => 'xsd:string'),                 // input parameters
        array('return' => 'xsd:string'),              // output parameters
        'urn:hellowsdl',                             // namespace
        'urn:hellowsdl#hello',                // soapaction
        'rpc',                                    // style
        'encoded',                              // use
        'Says hello world to client'            // documentation
    );

    // Define the method as a PHP function

    function hello_world($pName) {
        
      return 'Hello world from, ' . $pName;

    }
 
  // Use the request to (try to) invoke the service
    $HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
    $server->service($HTTP_RAW_POST_DATA); 

?>