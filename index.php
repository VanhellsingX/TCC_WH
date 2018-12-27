<?php 
/***************************************/
/* ESPERO PARAMETROS POR EL DIALOGFLOW */
/***************************************/
    $method = $_SERVER['REQUEST_METHOD'];

/*************************************/
/* RECIBO PARAMETROS POR METODO POST */
/*************************************/
	$requestBody = file_get_contents('php://input');
	$json = json_decode($requestBody);

/************************************/
/* OTENGO LA INTENCION SELECCIONADA */
/***********************************/
	$intent = $json->queryResult->intent->displayName;	
	//$intent = 'ESTADO_REMESA';

if(!isset($intent)){$intent='Fallo';}

/**********************************************/
/* SEGUN LA INTENCION, DETERMINO QUE REALIZAR */
/**********************************************/
switch ($intent) {
/* INICIO DE INTENCION DE CONSULTAR REMESA */
    case 'ESTADO_REMESA':
        $REMESA = $json->queryResult->parameters->number;
        //$REMESA ='1';
        //$REMESA ='757104213';

    //CONSUMO UN SERVICIO WEB DE INFORMACION DE PAISES 
        $soap_client = new SoapClient('http://clientes.tcc.com.co/servicios/informacionremesas.asmx?WSDL');
    //PREPARO LOS DATOS A SER ENVIADOS
        $PASS='MEDMATTELSA';
        $vec=array('Clave'=>$PASS,'remesas'=> array('RemesaUEN'=> array('numeroremesa'=>$REMESA, 'unidadnegocio'=> '1')),'Respuesta'=>'');
        $quote =$soap_client->ConsultarInformacionRemesasEstadosUEN($vec);
    //OBTENGO LOS PARAMETROS DE LA RESPUESTA DEL SERVICIO 
    $OK = $quote->Respuesta;

    if(!isset($OK)){
        $OK='-1';
        $speech ='No logre encontrar la remesa '.$REMESA.', valida por favor el número'; 
    }

    IF ($OK ==0) {
        $Nremesa=$quote->remesasrespuesta->RemesaEstados->numeroremesa;
        $CodigoNov=$quote->remesasrespuesta->RemesaEstados->codigonovedad;
        $fechanovedad = $quote->remesasrespuesta->RemesaEstados->fechanovedad;
        $Novedad = $quote->remesasrespuesta->RemesaEstados->Novedad;
        $Estado_nov = $quote->remesasrespuesta->RemesaEstados->estadonovedad;        
        }

 if ($OK == 0)
      { 
        $speech = 'La remesa '.$REMESA.', presenta la novedad '.mb_convert_encoding($Novedad,'Windows-1252','HTML-ENTITIES').', reportada el '.$fechanovedad.', la cual se encuentra '.$Estado_nov;
      }
 else {
        $speech ='No logre encontrar la remesa '.$REMESA.' valida por favor el número'; 
      }
        break;
// FIN DE CONSULTA ESTADO REMESA //        
    default:
       $speech = "SE PRESENTO UN FALLO INESPERADO";
    }
   $fulfillment = array("fulfillmentText" => utf8_encode($speech));
   //$fulfillment = array("fulfillmentText" => $speech);
   echo(json_encode($fulfillment));
   
?>
