### Introduction
This provides an unofficial API for querying the main WAEC site for results of a candidate.
It is possible for it to suddenly break as site's markup is susceptible to random changes though 
you can always send a PR if I am late to fixing the code to work with such change(s).
### Installation
Ensure you have composer installed already, else you could look that up [here](https://getcomposer.org/doc/00-intro.md).
In the project's root directory, run `composer update` to install dependencies and `php -S 127.0.0.1:3000` to start the php server listening on port 3000 on the localhost.
### Fetch Results
Fetches the result of the candidate using the parameters given. Sending HTTP POSTs to `/` and `result` both yields the same response.
#### Url
`/`, `result`
#### Method
`POST`
#### Data Parameters
The data parameters correspond to the form fields found on the main website. They include:
##### Required:
* `ExamNumber=[String]`: String representation of the candidate's examination number.
* `ExamYear=[Integer]`: Integer representation of the examination year.
* `ExamType=[String]`: Either `MAY/JUN` OR `NOV/DEV`, representing the examination type.
* `serial=[String]`: String representation of the Scratch card's serial number.
* `pin=[String]`: String representation of the Scratch card's PIN.

##### Optional:
None.
#### Response
All API response are JSON formatted and three consistent keys viz: `http_code`, `success` and `content`. These keys can be used to deduce
the status of the request even before the other keys are analyzed.
* `http_code=[Integer]`: Valid HTTP code representing the request status.
* `success=[bool]`: A `true` or `false` response indicating whether we were able to get the results or not.
* `content`: An embedded json object whose key-value pairs depend on the first two keys.

The keys in the `content` object depend on whether `http_code` is `200` or not (success or failure) and are explained below:
##### Success
* `title=[String]`: Content of the HTML `<title>` sent to browsers from the main site. Usually "WAECDIRECT ONLINE - RESULTS" for successful checks.
* `candidate_number=[String]`: String value of the candidate's examination number.
* `candidate_name=[String]`: String value of the candidate's full name.
* `exam_type`: String value of the exam type and year combined.
* `center`: String value of the candidate's examination center.
* `grades`: An embedded json object whose key-value pairs correspond to the subjects taken by the student and their grade in each of them.
##### Failure
* `title=[String]`: Content of the HTML `<title>` sent to the browsers from the main site. May or may not be present for failed requests.
* `error_title=[String]`: Title of the encountered error.
* `error_message=[String]`: Tad more detailed error message.
#### Sample Call
Using jQuery's ajax method;
```javascript
    var candidateInfo = { "ExamNumber": "5078802000", "ExamType": "MAY/JUN",
        "ExamYear": "2015", "serial": "QWA123456789", "pin": "9081803423"
    };
    $.ajax({
        url: "/result",
        dataType: "json",
        data: candidateInfo,
        type: "POST",
        success: function(response){
            console.log(response);
        }
    });
```
On success, the value of `response` from the above will be such:
```json
[
  {
    "http_code": 200,
    "success": true,
    "content": {
      "title": "\r\n\tWAECDIRECT ONLINE - RESULTS\r\n",
      "candidate_number": "AGADA JULIET AMUYINI",
      "candidate_name": "5081802023",
      "exam_type": "NOV/DEC WASSCE2015",
      "center": "Federal Government College, Otobi",
      "grades": {
        "ECONOMICS": "B2",
        "GEOGRAPHY": "A1",
        "ENGLISH LANGUAGE": "A1",
        "FURTHER MATHEMATICS": "B3",
        "MATHEMATICS": "B2",
        "AGRICULTURAL SCIENCE": "A1",
        "BIOLOGY": "A1",
        "CHEMISTRY": "B3",
        "PHYSICS": "A1"
      }
    }
  }
]
```

On failure, the response will be such (assuming invalid scratch card as the reason for failure):
```json
[
  {
    "http_code": 302,
    "success": false,
    "content": {
      "error_message": "INVALID SCRATCH CARD DETAIL",
      "error_title": "INVALID SCRATCH CARD DETAIL",
      "title": "Object moved"
    }
  }
]
``` 

#### Disclaimer
I am in no way affiliated to WAEC, Fleet Technologies or Vatebra Technologies!!!
