<?php namespace komenco\util;
/*
 * Copyright (C) 2015, BMW Car IT GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

use chobie\Jira\Api;
use chobie\Jira\Api\Authentication\Basic;
use chobie\Jira\Api\Client\ClientInterface;
use chobie\Jira\Api\Authentication\AuthenticationInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Post\PostFile;
use GuzzleHttp\Exception\ClientException;

class JiraGuzzleClient implements ClientInterface {
	protected $client;

	public function __construct(Client $client) {
		$this->client = $client;
		$defaultHeaders = array(
								'Content-Type' => 'application/json',
								'Accept' => 'application/json'
							);

		$this->client->setDefaultOption('headers', $defaultHeaders);
		$this->client->setDefaultOption('exceptions', true);
	}

	public function sendRequest($method,
								$url,
								$data = array(),
								$endpoint,
								AuthenticationInterface $credential,
								$isFile = false,
								$debug = false) {

		if (!$this->isSupportedMethod($method)) {
			throw new Exception('Unsupported HTTP method: ' . $method);
		}

		if ($credential instanceof Basic) {
			$this->client->
					setDefaultOption('auth',
										array($credential->getId(),
												$credential->getPassword()));
		}

		$request = $this->createRequest($method, $url, $data, $isFile);

		try {
			$response = $this->client->send($request);

			$content = $response->getBody()->getContents();
			if (strlen($content) <= 0) {
				return $this->statusToJSON($response);
			}

			return $content;
		} catch (ClientException $e) {
			return $this->statusToJSON($e->getResponse());
		}
	}

	public function createRequest($method, $url, $data, $isFile) {
		if ($this->requiresQueryRequest($method)) {
			return $this->createQueryRequest($method, $url, $data);
		}

		if ($isFile) {
			return $this->createFileRequest($method, $url, $data);
		}

		return $this->createJSONRequest($method, $url, $data);
	}

	public function createQueryRequest($method, $url, $data) {
		$request = $this->client->createRequest($method, $url);
		$request->getQuery()->merge($data);

		return $request;
	}

	public function createJSONRequest($method, $url, $data) {
		$request = $this->client->createRequest($method, $url);
		$request->setBody(Stream::factory(json_encode($data)));

		return $request;
	}

	public function createFileRequest($method, $url, $data) {
		$request = $this->client->createRequest($method, $url);
		$request->addHeader('X-Atlassian-Token', 'nocheck');
		$request->addHeader('Content-Type', 'multipart/form-data');

		$filename = substr($data['file'],1);
		$resource = fopen($filename, 'r');
		unset($data['file']);

		$body = $request->getBody();
		$body->replaceFields($data);
		$body->addFile(new PostFile('file', $resource));

		return $request;
	}

	public function isSupportedMethod($method) {
		switch ($method) {
			case Api::REQUEST_GET:
			case Api::REQUEST_POST:
			case Api::REQUEST_PUT:
			case Api::REQUEST_DELETE:
				return true;
			default:
				return false;
		}
	}

	public function requiresQueryRequest($method) {
		return ($method == Api::REQUEST_GET || $method == Api::REQUEST_DELETE);
	}

	public function statusToJSON($response) {#
		$result = array(
			'status' => $response->getStatusCode(),
			'message' => $response->getReasonPhrase()
		);

		return json_encode($result);
	}
}
