<?php
/**
 * Handles all HTTP requests using cURL and manages the responses.
 *
 * @version 2011.06.07
 * @copyright 2006-2011 Ryan Parman
 * @copyright 2006-2010 Foleeo Inc.
 * @copyright 2010-2011 Amazon.com, Inc. or its affiliates.
 * @copyright 2008-2011 Contributors
 * @license http://opensource.org/licenses/bsd-license.php Simplified BSD License
 */
namespace SLS;
/**
 * Container for all response-related methods.
 */
class ResponseCore
{
	/**
	 * Stores the HTTP header information.
	 */
	public $header;

	/**
	 * Stores the SimpleXML response.
	 */
	public $body;

	/**
	 * Stores the HTTP response code.
	 */
	public $status;

	/**
	 * Constructs a new instance of this class.
	 *
	 * @param array $header (Required) Associative array of HTTP headers (typically returned by <RequestCore::get_response_header()>).
	 * @param string $body (Required) XML-formatted response from AWS.
	 * @param integer $status (Optional) HTTP response status code from the request.
	 * @return object Contains an <php:array> `header` property (HTTP headers as an associative array), a <php:SimpleXMLElement> or <php:string> `body` property, and an <php:integer> `status` code.
	 */
	public function __construct($header, $body, $status = null)
	{
		$this->header = $header;
		$this->body = $body;
		$this->status = $status;

		return $this;
	}

	/**
	 * Did we receive the status code we expected?
	 *
	 * @param integer|array $codes (Optional) The status code(s) to expect. Pass an <php:integer> for a single acceptable value, or an <php:array> of integers for multiple acceptable values.
	 * @return boolean Whether we received the expected status code or not.
	 */
	public function isOK($codes = array(200, 201, 204, 206))
	{
		if (is_array($codes))
		{
			return in_array($this->status, $codes);
		}

		return $this->status === $codes;
	}
}

