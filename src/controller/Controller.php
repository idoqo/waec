<?php
namespace controller;

use model\Response;

class Controller
{
	public function actionError($errorMessage, $errorCode=400) {
	    $errorResponse = new Response();
	    $errorResponse->bind(Response::RESPONSE_KEY_SUCCESS, false);
        $errorResponse->bind(Response::RESPONSE_KEY_HTTP_CODE, $errorCode);
        $errorResponse->bind(Response::RESPONSE_KEY_ERROR_TITLE, "Bad request");
	    $errorResponse->bind(Response::RESPONSE_KEY_ERROR_MESSAGE, $errorMessage);

	    echo $errorResponse->getResponse();
	    exit();
	}
}