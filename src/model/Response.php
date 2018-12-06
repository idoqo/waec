<?php
namespace model;

class Response extends Model
{
    //an array of the response body.
    private $body = array();

    //The success value. Value is either true or false
    const RESPONSE_KEY_SUCCESS = "success";
    //The request Url. Value is the url as sent to the main site
    const RESPONSE_KEY_URL = "curl_url";
    //String representation of the HTML title of curl's output
    const RESPONSE_KEY_TITLE = "title";
    //JSON object containing the stuff you're probably here for.
    const RESPONSE_KEY_CONTENT = "content";

    const RESPONSE_KEY_HTTP_CODE = "http_code";
    const RESPONSE_KEY_ERROR_MESSAGE = "error_message";
    const RESPONSE_KEY_ERROR_TITLE = "error_title";

    const RESPONSE_KEY_CANDIDATE_NAME = "candidate_name";
    const RESPONSE_KEY_CANDIDATE_NUMBER = "candidate_number";
    const RESPONSE_KEY_EXAM_TYPE = "exam_type";
    const RESPONSE_KEY_EXAM_CENTER = "center";
    const RESPONSE_KEY_GRADES = "grades";

    /*
     * appends a new key-value pair to the response' body
     * It is recommended for $key to be one of @see RESPONSE_KEY*
     */
    public function bind($key, $value) {
        $this->body[$key] = $value;
    }

    /*
     * Like $this->bind() but for adding multiple values at same time.
     * @param array $data: Key-Value pair of data to be appended.
     */
    public function bindMultiple(array $data) {
        array_push($this->body, $data);
    }

    public function getResponse(){
        return json_encode($this->body);
    }
}