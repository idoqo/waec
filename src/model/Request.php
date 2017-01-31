<?php
namespace model;

class Request extends Model
{
	public static $PARAM_EXAM_YEAR = "ExamYear";
	public static $PARAM_EXAM_NUMBER = "ExamNumber";
	public static $PARAM_EXAM_TYPE = "ExamType";
	public static $PARAM_CARD_SERIAL = "serial";
	public static $PARAM_CARD_PIN = "pin";

	//May/June
	public static $EXAM_TYPE_RAIN = "MAY/JUN";
	//November/December
	public static $EXAM_TYPE_HARMATTAN = "NOV/DEC";

	private $examYear;
	private $examNumber;
	private $cardSerial;
	private $cardPin;
	private $examType;

	private $requestParams = null;
	private $curlUrl;

    /**
     * String value of the year the examination was taken
     * @param String $examYear
     */
    public function setExamYear($examYear)
    {
        $this->examYear = $examYear;
    }

    /**
     * The String value of the candidate's exam number
     * @param String $examNumber
     */
    public function setExamNumber($examNumber)
    {
        $this->examNumber = $examNumber;
    }

    /**
     * The String value of the serial number of the scratch card to be used in result checking
     * @param String $cardSerial
     */
    public function setCardSerial($cardSerial)
    {
        $this->cardSerial = $cardSerial;
    }

    /**
     * The String pin on the scratch card to be used in result checking
     * @param String $cardPin
     */
    public function setCardPin($cardPin)
    {
        $this->cardPin = $cardPin;
    }

    /**
     * the exam type corresponding to one of $EXAM_TYPE_RAIN for May/June
     * or $EXAM_TYPE_HARMATTAN for November/December
     * @param String $examType
     */
    public function setExamType($examType)
    {
        $this->examType = $examType;
    }



    private function makeResultUrl() {
		$data = array(
		    self::$PARAM_EXAM_YEAR => $this->examYear,
            self::$PARAM_EXAM_NUMBER => $this->examNumber,
            self::$PARAM_EXAM_TYPE => $this->examType,
            self::$PARAM_CARD_SERIAL => $this->cardSerial,
            self::$PARAM_CARD_PIN => $this->cardPin
            );
		$this->requestParams = http_build_query($data);
	}

    public function letTheDinosaursFall() {
        $this->execute();
    }

    public function execute() {
        $mainUrl = "https://www.waecdirect.org/DisplayResult.aspx";
        if (is_null($this->requestParams)) {
            $this->makeResultUrl();
        }
        $this->curlUrl = $mainUrl."?".$this->requestParams;
        $response = $this->getCurlOutput();
        $jsonResponse = $this->encodeResponse($response);
        header("Content-type: application/json");
        return $jsonResponse;
    }

    private function getCurlOutput() {
        //make a request using curl and $this->curlUrl as the host.
        return "";
    }

    /**
     * @param String $dataString preferably the output from curl, parsed using the
     * html tags present
     * @return string A JSON formatted string with appropriate keys.
     */
    private function encodeResponse($dataString) {
        $response = array();
        $response['success'] = true;
        $response['message'] = "Result check successful";
        $response['url'] = $this->curlUrl;
        //parse the output from curl and make it into something saner
        return json_encode($response);
    }
}