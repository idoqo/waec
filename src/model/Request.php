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

    /*
     * Returns the HTML response given by curl after using
     * passing $this->curlUrl to curl as host
     */
    private function getCurlOutput() {
        //why the fuck does curl feels so cryptic?
        $curlHandle = curl_init();
        curl_setopt_array(
            $curlHandle, array(
                //set the url
                CURLOPT_URL => $this->curlUrl,
                CURLOPT_RETURNTRANSFER => true,
                //enable headers, used to know where we're being redirected by main waec site
                CURLINFO_HEADER_OUT => true,
                //enable verbose mode to show output separately
                CURLOPT_VERBOSE => true
            )
        );

        $output = curl_exec($curlHandle);
        $info = curl_getinfo($curlHandle);
        $info['output'] = $output;
        return utf8_encode(json_encode($info));
    }

    /**
     * @param String $dataString preferably the output from curl, parsed using the
     * html tags present
     * @return string A JSON formatted string with appropriate keys.
     */
    private function encodeResponse($dataString) {
        //todo make this saner...
        $response = array();
        /*$response['success'] = true;
        $response['message'] = "Result check successful";
        $response['content'] = $this->getCurlOutput();*/
        //parse the output from curl and make it into something saner
        return json_encode(array("hello"=>$this->parseCurlOutput()));
    }

    /**
     * using the value of the "output" key sent by @see getCurlOutput(), this parses the html string
     * present in the output and figures whether the request failed or not
     * @return String a json encoded string with relevant data
     */
    private function parseCurlOutput() {
        $curlResponse = $this->getCurlOutput();
        return json_decode($curlResponse)->output;
    }
}