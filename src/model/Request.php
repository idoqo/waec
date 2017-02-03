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

	private $response;

	public function __construct() {
	    $this->response = new Response();
    }

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
        $jsonResponse = $this->buildResponse();
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
        //better to encode and decode later than deal with what curl output actually is...
        return utf8_encode(json_encode($info));
    }

    /**
     * using the value of the "output" key sent by @see getCurlOutput(), parses the html string
     * present in the output
     * @return array representation of content handpicked out of the curl output
     */
    private function parseCurlOutput() {
        $content = array();
        $curlResponse = $this->getCurlOutput();
        $decoded = json_decode($curlResponse);
        //the actual displayable html sent to curl
        $output = $decoded->output;
        //http status code as returned by curl
        $content[Response::RESPONSE_KEY_HTTP_CODE] = $decoded->http_code;
        //the redirect url, this contains the error msg if any and can deduce if the
        //request was successful
        $redirectUrl = $decoded->redirect_url;
        //extract the error message and title from the redirect url
        $parts = parse_url($redirectUrl);
        parse_str($parts['query'], $getParams);
        $content[Response::RESPONSE_KEY_ERROR_MESSAGE] = (isset($getParams['errMsg'])) ?
            $getParams['errMsg'] : null;
        $content[Response::RESPONSE_KEY_ERROR_TITLE] = (isset($getParams['errTitle'])) ?
            $getParams['errTitle'] : null;

        $pq = \phpQuery::newDocument($output);
        //extract the value of the html <title> tag
        $content[Response::RESPONSE_KEY_TITLE] = $pq->find('title')->html();
        return $content;
    }

    private function buildResponse() {
        $output = $this->parseCurlOutput();
        if ($output[Response::RESPONSE_KEY_ERROR_TITLE] != null) {
            $this->response->bind(Response::RESPONSE_KEY_SUCCESS, false);
            $this->response->bind(Response::RESPONSE_KEY_ERROR_TITLE,
                $output[Response::RESPONSE_KEY_ERROR_TITLE]);
            $this->response->bind(Response::RESPONSE_KEY_ERROR_MESSAGE,
                $output[Response::RESPONSE_KEY_ERROR_MESSAGE]);
        }
        $this->response->bind(Response::RESPONSE_KEY_HTTP_CODE, $output[Response::RESPONSE_KEY_HTTP_CODE]);
        $this->response->bind(Response::RESPONSE_KEY_TITLE, $output[Response::RESPONSE_KEY_TITLE]);

        return $this->response->getResponse();
    }
}