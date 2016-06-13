<?php
  require __DIR__ . '/vendor/autoload.php';
/**
 *
 * Copyright (c) 2005-2015, Braulio José Solano Rojas
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 * 	Redistributions of source code must retain the above copyright notice, this list of
 * 	conditions and the following disclaimer.
 * 	Redistributions in binary form must reproduce the above copyright notice, this list of
 * 	conditions and the following disclaimer in the documentation and/or other materials
 * 	provided with the distribution.
 * 	Neither the name of the Solsoft de Costa Rica S.A. nor the names of its contributors may
 * 	be used to endorse or promote products derived from this software without specific
 * 	prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND
 * CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
 * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *
 * @version $Id$
 * @copyright 2005-2015
 */

require_once 'Ahorcado.class.php';

function autoinclude($className) {
	$className = str_replace('\\', '/', $className) . '.php';
	require_once $className;
}

spl_autoload_register('autoinclude');
if ( $_SERVER['REQUEST_URI'] === '/ServidorAhorcado/?wsdl/$metadata' OR $_SERVER['REQUEST_URI'] ==='/ServidorAhorcado/?wsdl/_vti_bin/ListData.svc/$metadata' OR $_SERVER['REQUEST_URI'] === '?wsdl/$metadata' OR isset($_GET['wsdl'])) 
{
	header('Content-Type: application/soap+xml; charset=utf-8');
	echo file_get_contents('Juegos.wsdl');
}
else 
{
	session_start();

	if (!isset($_SESSION['service'])) 
	{
		$_SESSION['service'] = new Ahorcado();
	}
	$servidorSoap = new SoapServer('http://testlocal.dev:80/ServidorAhorcado/?wsdl');

	//Para evitar la excepción por defecto de SOAP PHP cuando no existe HTTP_RAW_POST_DATA,
	//se regresa explícitamente el siguiente fallo cuando no hay solicitud (v.b. desde un navegador)
	if(!@$HTTP_RAW_POST_DATA){
		$servidorSoap->fault('SOAP-ENV:Client', 'Invalid Request');
		exit;
	}

	$servidorSoap->setObject(new Zend\Soap\Server\DocumentLiteralWrapper($_SESSION['service']));
	$servidorSoap->setPersistence(SOAP_PERSISTENCE_SESSION);
	$servidorSoap->handle();
}

?>
