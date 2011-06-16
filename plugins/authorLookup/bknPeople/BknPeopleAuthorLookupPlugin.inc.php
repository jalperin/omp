<?php

/**
 * @defgroup plugins_authorLookup_bnkPeople
 */

/**
 * @file plugins/authorLookup/bnkPeople/BknPeopleAuthorLookupPlugin.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPBknPeopleLookupPlugin
 * @ingroup plugins_authorLookup_bnkPeople
 *
 * @brief Plugin that provides a Web service to fetch people strings and ids from BKN People http://people.bibkn.org
 */


import('classes.plugins.Plugin');
import('lib.pkp.classes.webservice.WebService');

define('BKNPEOPLE_WEBSERVICE_URL', 'http://people.bibkn.org/wsf/ws/');

class BknPeopleAuthorLookupPlugin extends Plugin {
	/**
	 * Constructor
	 */
	function PKPBknPeopleLookupPlugin() {
		parent::Plugin();
	}


	//
	// Override protected template methods from PKPPlugin
	//
	/**
	 * @see PKPPlugin::register()
	 */
	function register($category, $path) {
		if (!parent::register($category, $path)) return false;
		$this->addLocaleData();
		return true;
	}

	/**
	 * @see PKPPlugin::getName()
	 */
	function getName() {
		return 'BknPeopleAuthorLookupPlugin';
	}

    /**
     * Shorter version of the plugin name
     * @return string
     */
    function getShortName() {
        return String::substr($this->getName(), 0, -18);
    }

	/**
	 * @see PKPPlugin::getDisplayName()
	 */
	function getDisplayName() {
		return Locale::translate('plugins.authorLookup.bknPeople.displayName');
	}

	/**
	 * @see PKPPlugin::getDescription()
	 */
	function getDescription() {
		return Locale::translate('plugins.authorLookup.bnkPeople.description');
	}

	/**
	 * Do the lookup. This process uses the StructWSF API set up at BKN People
     * This API requires us to do three separate requests to the server.
     * 1.   Perform the actual search. This request is sent POST'ed and includes the search term.
     * 2.   Convert the search results into JSON using the StructWSF API. This request is POST'ed with an accept header.
     * 3.   Cycle through all the search results and fetch the actual record. The search only provides descriptors.
     *      So we need to go back to the server once for each search result to get the author strings.
	 */
	function getAuthors($searchTerm) {
        // prepare a results array.
        $results = array();

        // The parameters for the first POST to BKN People
		$params = array(
				'query' => $searchTerm,
				'datasets' => 'all',
				'items' => 25,
				'types' => 'http://xmlns.com/foaf/0.1/Person',
				'include_aggregates' => 'false');

        // This first call does the actual searching. The result is an XML with descriptors of the matching records.
		$xmlSearchResults = $this->_callWebService(BKNPEOPLE_WEBSERVICE_URL . 'search/', $params);


        // The XML is a little complex, so we rely on StructWSF to convert it to more manageable JSON.
		$params2 = array('docmime' => 'text/xml',
						'document' => trim($xmlSearchResults));
        // Call to the conversion endpoint.
		$jsonSearchResults = $this->_callWebService(BKNPEOPLE_WEBSERVICE_URL . 'converter/irjson/', $params2, 'application/iron+json');

		// Decode the returning JSON.
		import('lib.pkp.classes.core.JSONManager');
		$jsonManager = new JSONManager();
		$jsonSearchResults = $jsonManager->decode($jsonSearchResults);

		// Grab the head node of the result set.
		$resultList = $jsonSearchResults->{'recordList'};

        // We max out at the top 20 result matches, since we have to query once for each one.
        // This is an arbitrary number, but it ensures a snappy interface.
		for ($i = 0; ($i < 20 && $i < count($resultList)); $i++ ) {
			$resultNode =& $resultList[$i];
			// Extract the dataset from the object
			$datasetNode =& $resultNode->{'isPartOf'};
			$datasetName = trim($datasetNode->{'ref'}, '@');
			// extract the id
			$recordId =& $resultNode->{'id'};

			$params3 = array(
					'uri' => $recordId,
					'dataset' => $datasetName);

            // have to call with GET for some reason. BKN people gives a different response if you POST.
            // the Accept: header _must_ be sent or the result comes back in a different format.
            // The trailing slash at crud/read/ is also necessary (or the result is a 301 redirect).
			$personRecord = $this->_callWebService(BKNPEOPLE_WEBSERVICE_URL . 'crud/read/', $params3, 'application/rdf+xml', 'GET');

            // The resulting string is a sufficiently simple XML we can parse.
            $xmlParser = new XMLParser();
            $personRecordXml =& $xmlParser->parseText($personRecord);

            // Make sure we received valid XML.
            if ( $personRecordXml && is_a($personRecordXml, 'XMLNode') ) {
                $personNode =& $personRecordXml->getChildByName('ns0:Person');
            }

            // Make sure the XML had a person node and that we found it.
            if ( isset($personNode) && is_a($personNode, 'XMLNode')) {
                unset($id);
                unset($name);

                // Find both the name and the id in one of the XML Node's children.
                foreach ( $personNode->getChildren() as $node ) {
                    if ( stripos($node->getName(), 'name') !== false ) {
                        $name = $node->getValue();
                    } elseif ( stripos($node->getName(), '_id') !== false ) {
                        // FIXME? We are putting the id type and the id value in the $id.
                        // This may be considered a kludge. The calling code needs to know how to parse this.
                        $id = str_replace('-', '_', substr($node->getName(), 4)) . '-' . $node->getValue();
                    }
                }

                // Check that we found both a name and an id. If so, add it to the result set.
                if ( isset($name) && isset($id) ) {
                    $results[$id] = $name;
                }
                unset($personNode);
            }
            unset($personRecordXml);
		}
        return $results;
	}

	/**
	 * Call web service with the given parameters
	 * @param $params array GET or POST parameters
	 * @return String or null in case of error
	 */
	function &_callWebService($url, &$params, $accept = false, $method = 'POST') {
		// Create a request
		$webServiceRequest = new WebServiceRequest($url, $params, $method);

		if ($accept)
			$webServiceRequest->setAccept($accept);

		// Configure and call the web service
        $webService = new WebService();
		$result =& $webService->call($webServiceRequest);

        // FIXME: provide some warning if the call is not successful?

		return $result;
	}
}

?>
