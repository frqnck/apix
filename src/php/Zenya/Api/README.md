Server.php: frontend class for exposing a REST API server.

Exception.php

Resource/
Resource.php: fontend to the class/methods.

Response/
Response.php: encode/format data


Request.php: deal with the request

HttpRequest: facade to HTTP_Request2



-----------
XML_RPC2_Server: frontend class for exposing PHP functions via XML-RPC.
XML_RPC2_Server_CallHandler: responsible for actually calling the server-exported methods from the exported class.

XML_RPC_Backend: responsible for the actual execution of a request, as well as payload encoding and decoding.
XML_RPC2_Backend_Php_Request: represents an XML_RPC request, exposing the methods needed to encode/decode a request.
XML_RPC2_Backend_Php_Response:
XML_RPC2_Backend_Php_Server: XML_RPC server class PHP-only backend

XML_RPC2_Method: representing an XML-RPC exported method.
