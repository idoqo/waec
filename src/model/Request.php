<?php
namespace model;

class Request extends Model
{
	public static $PARAM_EXAM_YEAR = "ExamYear";
	public static $PARAM_EXAM_NUMBER = "ExamNumber";
	public static $PARAM_EXAM_TYPE = "ExamType";
	public static $PARAM_CARD_SERIAL = "serial";
	public static $PARAM_CARD_PIN = "pin";

	const MAIN_URL = "https://www.waecdirect.org/DisplayResult.aspx";

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

    /**
     * Request constructor.
     * @param String $number candidate's examination number
     * @param String $pin Pin on the card
     * @param String $serial Card serial number
     * @param String $type @see $EXAM_TYPE_* static variables
     * @param int $year Examination year
     */
	public function __construct($number=null,$pin=null,$serial=null,$type=null,$year=null) {
	    $this->examNumber = $number;
	    $this->cardPin = $pin;
	    $this->cardSerial = $serial;
        $this->examType = $type;
        $this->examYear = $year;
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
        return $this->buildResponse();
    }

    /*
     * Returns the HTML response given by curl after using
     * passing $this->curlUrl to curl as host
     */
    private function getCurlOutput() {
        if (is_null($this->requestParams)) {
            $this->makeResultUrl();
        }
        $this->curlUrl = self::MAIN_URL."?".$this->requestParams;
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

    private function parseFailedRequest($curlOutput) {
        //the redirect url, this contains the error msg if any and can deduce if the
        //request was successful
        $redirectUrl = $curlOutput->redirect_url;
        //extract the error message and title from the redirect url
        $parts = parse_url($redirectUrl);
        parse_str($parts['query'], $getParams);
        $content[Response::RESPONSE_KEY_ERROR_MESSAGE] = (isset($getParams['errMsg'])) ?
            $getParams['errMsg'] : null;
        $content[Response::RESPONSE_KEY_ERROR_TITLE] = (isset($getParams['errTitle'])) ?
            $getParams['errTitle'] : null;
        $pq = \phpQuery::newDocument($curlOutput->output);
        //extract the value of the html <title> tag
        $content[Response::RESPONSE_KEY_TITLE] = $pq->find('title')->html();

        return $content;
    }

    /**
     * using the value of the "output" key sent by @see getCurlOutput(), makes sense out of curl's response
     * @return array representation of content handpicked out of the curl output
     */
    public function parseCurlOutput() {
        $content = array();
        $curlResponse = $this->getCurlOutput();
        $decoded = json_decode($curlResponse);
        //http status code as returned by curl
        $responseCode = $decoded->http_code;
        $content[Response::RESPONSE_KEY_HTTP_CODE] = $responseCode;
        if ($responseCode == 200) {
            $parsed = $this->parseResult($decoded);
            $content[Response::RESPONSE_KEY_SUCCESS] = true;
        } else {
            $parsed = $this->parseFailedRequest($decoded);
            $content[Response::RESPONSE_KEY_SUCCESS] = false;
        }

        $content[Response::RESPONSE_KEY_CONTENT] = $parsed;
        return $content;
    }

    private function parseResult($curlOutput) {
        $content = array();
        $htmlBody = $curlOutput->output;

        $pq = \phpQuery::newDocument($htmlBody); //todo use actual site
        $content[Response::RESPONSE_KEY_TITLE] = $pq->find('title')->html();
        //because the monster who coded the main site didn't see the need for class or ids,
        //let's target the 5th tr element in the table tag and get it's html
        $resultContent = $pq->find("body > form > table tr:nth-child(5)")->html();

         //first table in $resultContent's parent supposedly contains both candidate info and grades
         //the first four <tr> tags has number, name, type and center respectively
        $relevant = pq($resultContent)->find("td > table:nth-child(1)")->parent()->html();
        $relevant = pq($relevant); //convert it to a phpQuery object

        $nameRow = $relevant->find("tr:eq(0)"); //zero based indexing
        $numberRow = $relevant->find("tr:eq(1)");
        $examTypeRow = $relevant->find("tr:eq(2)");
        $centerRow = $relevant->find("tr:eq(3)");

        $content[Response::RESPONSE_KEY_CANDIDATE_NUMBER] = pq($numberRow)->find("td:last")->html();
        $content[Response::RESPONSE_KEY_CANDIDATE_NAME] = pq($nameRow)->find("td:last")->html();
        $content[Response::RESPONSE_KEY_EXAM_TYPE] = pq($examTypeRow)->find("td:last")->html();
        $content[Response::RESPONSE_KEY_EXAM_CENTER] = pq($centerRow)->find("td:last")->html();

        $subjects = array();
        foreach ($relevant->find("tr:gt(3)") as $subjectRow) {
            $subjectRow = pq($subjectRow);
            $subject = $subjectRow->find("td:first")->html();
            $grade = $subjectRow->find("td:last")->html();

            $subjectItem = [$subject=>$grade];
            $subjects[] = $subjectItem;
        }
        $content[Response::RESPONSE_KEY_GRADES] = $subjects;

        return $content;
    }

    private function buildResponse() {
        $output = $this->parseCurlOutput();
        $this->response->bindMultiple($output);

        return $this->response->getResponse();
    }
}