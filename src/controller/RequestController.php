<?php
namespace controller;

use model\Request;

class RequestController extends Controller
{
	public function actionIndex() {

		if (isset($_POST) && !empty($_POST)) {
            $cardPin = (isset($_POST[Request::$PARAM_CARD_PIN])) ?
                $_POST[Request::$PARAM_CARD_PIN] : "";
            $cardSerial = (isset($_POST[Request::$PARAM_CARD_SERIAL])) ?
                $_POST[Request::$PARAM_CARD_SERIAL] : "";
		    $examNumber = (isset($_POST[Request::$PARAM_EXAM_NUMBER])) ?
                $_POST[Request::$PARAM_EXAM_NUMBER] : "";
		    $examType = (isset($_POST[Request::$PARAM_EXAM_TYPE])) ?
                $_POST[Request::$PARAM_EXAM_TYPE] : "";
		    $examYear = (isset($_POST[Request::$PARAM_EXAM_YEAR])) ?
                $_POST[Request::$PARAM_EXAM_YEAR] : "";

            /*
            check that all required values are present. 
            Card validation itself is handled by the main site.
            */
		    if (trim($cardPin) == "") {
		        $this->actionError("Card PIN value cannot be blank");
            }
            else if (trim($cardSerial) == "") {
		        $this->actionError("Card Serial Number cannot be blank");
            }
            else if (trim($examNumber) == "") {
		        $this->actionError("Candidate Examination number cannot be blank");
            }
            else if (trim($examType) == "") {
		        $this->actionError("Examination type cannot be blank");
            }
            else if (trim($examYear) == "") {
		        $this->actionError("Examination year cannot be blank");
            }
            else {
                $request = new Request($examNumber,$cardPin,$cardSerial,$examType,$examYear);
                echo $request->execute();
            }
        } else {
            $this->actionError("Failed to understand request. Are you sure it was a POST?");
        }
	}
}