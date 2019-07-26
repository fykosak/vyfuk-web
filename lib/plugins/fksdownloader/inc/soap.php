<?php

/**
 * Utility class for crafting and sending SOAP requests.
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michal Koutný <michal@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC'))
    die();

class fksdownloader_soap {

    /**
     * @var SoapClient
     */
    private $client;

    public function __construct($wsdl, $username, $password) {
        try {
            $this->client = new SoapClient($wsdl, array(
                'trace' => true,
                'exceptions' => true,
                'stream_context' => stream_context_create(
                    array(
                        'ssl' => array(
                            'verify_peer'       => false,
                            'verify_peer_name'  => false,
                        )
                    )),
            ));
        } catch (SoapFault $e) {
            msg('fksdbexport: ' . $e->getMessage(), -1);
            return;
        }

        $credentials = new stdClass();
        $credentials->username = $username;
        $credentials->password = $password;

        $header = new SoapHeader('http://fykos.cz/xml/ws/service', 'AuthenticationCredentials', $credentials);
        $headers = array($header);
        $this->client->__setSoapHeaders($headers);
    }

    public function createExportRequest($qid, $parameters, $formatVersion = null) {
        $parametersXML = array();
        foreach ($parameters as $name => $value) {
            $parametersXML[] = array(
                'name' => $name,
                '_' => $value,
            );
        }
        $request = array(
            'qid' => $qid,
            'parameter' => $parametersXML,
        );
        if ($formatVersion !== null) {
            $request['format-version'] = $formatVersion;
        }
        return $request;
    }

    public function createResultsDetailRequest($contest, $year, $series) {
        return array(
            'contest' => $contest,
            'year' => $year,
            'detail' => $series,
        );
    }

    public function createResultsCummulativeRequest($contest, $year, $series) {
        return array(
            'contest' => $contest,
            'year' => $year,
            'cumulatives' => array(// supports bundling multiple cummulative specifications in on request
                // Circumvent PHP ambiguity by serializing list manually.
                'cumulative' => implode(' ', $series), // list of series
            ),
        );
    }

    public function createResultsSchoolCummulativeRequest($contest, $year, $series) {
        return array(
            'contest' => $contest,
            'year' => $year,
            'school-cumulatives' => array(// supports bundling multiple cummulative specifications in on request
                // Circumvent PHP ambiguity by serializing list manually.
                'school-cumulative' => implode(' ', $series), // list of series
            ),
        );
    }

    /**
     * @param string
     * @param mixed $request
     * @return string response XML
     */
    public function callMethod($methodName, $request) {
        try {
            call_user_func(array($this->client, $methodName), $request);
            return $this->client->__getLastResponse();
        } catch (SoapFault $e) {
            msg('fksdownloader: server error: ' . $e->getMessage(), -1);
        }
    }

}
