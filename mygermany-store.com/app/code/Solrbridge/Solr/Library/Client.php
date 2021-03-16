<?php
namespace Solrbridge\Solr\Library;

use \Magento\Framework\HTTP\ZendClient;
use Solrbridge\Search\Helper\System as System;

class Client
{
    protected $isLiteMode = false;
    
    protected $solrServerUrl = null;
    
    protected $solrCore = null;
    
    protected $index = null;

    protected $client = null;
    
    protected $isAuthentication = null;
    
    protected $password = null;
    
    protected $username = null;

    public function __construct()
    {
        //construct
    }
    
    public function setLiteMode()
    {
        $this->isLiteMode = true;
    }
    
    public function isLiteMode()
    {
        return $this->isLiteMode;
    }
    
    public function getClient()
    {
        if (null == $this->client) {
            $this->client = new ZendClient;
        }
        return $this->client;
    }
    
    public function setSolrServerUrl($serverUrl)
    {
        $this->solrServerUrl = $serverUrl;
    }
    
    public function getSolrServerUrl()
    {
        if (null == $this->solrServerUrl) {
            $this->solrServerUrl = $this->getConfig('solrbridge_general/solr/server_url');
        }
        return $this->solrServerUrl;
    }
    
    public function getConfig($path)
    {
        if ($this->isLiteMode()) {
            return \Solrbridge\Search\lib\Solrbridge::getConfigData($path);
        }
        if ($this->getIndex()) {
            return $this->getIndex()->getStore()->getConfig($path);
        }
        return \Solrbridge\Search\lib\Solrbridge::getConfig($path);
    }
    
    protected function isAuthEnable()
    {
        if (null === $this->isAuthentication) {
            $this->isAuthentication = $this->getConfig('solrbridge_general/solr/server_authentication_enable');
        }
        return $this->isAuthentication;
    }
    
    protected function getAuthUsername()
    {
        if (null === $this->username) {
            $this->username = $this->getConfig('solrbridge_general/solr/server_authentication_username');
        }
        return $this->username;
    }
    
    protected function getAuthPassword()
    {
        if (null === $this->password) {
            $this->password = $this->getConfig('solrbridge_general/solr/server_authentication_password');
            if (!$this->isLiteMode()) {
                $this->password = System::getEncryptor()->decrypt($this->password);
            }
        }
        return $this->password;
    }
    
    public function setSolrCore($solrCore)
    {
        $this->solrCore = $solrCore;
    }
    
    public function getSolrCore()
    {
        return $this->solrCore;
    }
    
    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }
    
    public function getIndex()
    {
        return $this->index;
    }

    public function buildUrl($path, $core = null)
    {
        if(null !== $core)
        {
            return trim($this->getSolrServerUrl(), '/').'/'.trim($core, '/').'/'. trim($path, '/');
        }
        return $this->getSolrServerUrl() . trim($path, '/');
    }
    
    public function pingSolrCore($core)
    {
        $requestUrl = $this->buildUrl('admin/ping', $core);
        $result = $this->doGetRequest($requestUrl);
        //@TODO: some thing here
    }
    
    protected function setupClientAuth(&$client)
    {
        if ($this->isAuthEnable()) {
            $client->setAuth($this->getAuthUsername(), $this->getAuthPassword());
        }
    }
    
    /**
     * Setup Solr authentication user/pass if neccessary
     * @param resource $sh
     */
    public function setupSolrAuthenticate(&$sh)
    {
        if ($this->isAuthEnable()) {
            curl_setopt($sh, CURLOPT_USERPWD, $this->getAuthUsername().':'.$this->getAuthPassword());
            curl_setopt($sh, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        }
    }
    
    public function doPostRequest($requestUrl, $params = array())
    {
        $requestParams = array_merge(array('wt' => 'json'), $params);
        $client = new ZendClient;
        $client->setUri($requestUrl);
        $client->setParameterPost($requestParams);
        $client->setMethod('POST');
        $this->setupClientAuth($client);
        try
        {
            $response = $client->request();
            if ($response->isError()) {
                throw new \Exception(__('Solr server response error: [%1] - Query URL[%2] - file:%2 line:%3', $response->getMessage(), $requestUrl, __FILE__, __LINE__));
            }
        }
        catch (\Exception $e)
        {
            $originErrorMessage = $e->getMessage();
            $finalMessage = __('Request to Solr Server [%1] Failed. Origin request message [%2]', 
                                    $this->getClient()->getUri(), $originErrorMessage);
            throw new \Exception($finalMessage);
        }
    }
    
    public function postJsonData($postParams = array())
    {
        if( $this->index !== null )
        {
            $requestUrl = $this->buildUrl('update', $this->index->getSolrCore());
            \Solrbridge\Search\Helper\Debug::log(print_r($postParams, true).PHP_EOL.'---------------------');
            $this->doRequest($requestUrl, $postParams);
        }
    }
    
    public function commit()
    {
        if( $this->index !== null )
        {
            $params = array(
                'wt'        => 'json',
                'commit'    => 'true',
                'optimize'  => 'false',
                'waitFlush' => 'false',
                'waitSearcher' => 'false'
            );
            
            $requestUrl = $this->buildUrl('update/json', $this->index->getSolrCore());
            $this->doGetRequest($requestUrl, $params);
        }
    }
    
    public function truncate()
    {
        //delete all documents
        $this->deleteDocuments();
    }
    
    public function deleteDocuments($documentIds = array())
    {
        if( $this->index !== null )
        {
            $solrcore = $this->index->getSolrCore();
            $storeId = $this->index->getStoreId();
            $doctype = $this->index->getDoctype();

            $deleteQuery = array(
                'store_id' => $storeId,
                'document_type' => $doctype
            );  
            $query = array();
            foreach ($deleteQuery as $key => $value)
            {
                //$query .= "<query>{$key}:{$value}</query>";
                $query[] = "{$key}:{$value}";
            }
            
            if(is_array($documentIds) && count($documentIds) > 0) {
                foreach($documentIds as $docId) {
                    //$query .= "<query>document_id:{$docId}</query>";
                    $query[] = "document_id:{$docId}";
                }
            }
            
            if(count($query) > 0) {
                $query = '<query>'.@implode(' AND ', $query).'</query>';
                $params = array(
                    'stream.body' => '<delete>'.$query.'</delete>',
                    'commit' => 'true'
                );
            }
            
            \Solrbridge\Search\Helper\Debug::log($params, true);
            
            $requestUrl = $this->buildUrl('update', $this->index->getSolrCore());
            $this->doRequest($requestUrl, $params);
        }
    }
    
    public function doGetRequest($requestUrl, $params = array())
    {
        $requestParams = array_merge(array('wt' => 'json'), $params);
        $client = $this->getClient();
        $client->setParameterGet($requestParams);
        $client->setUri($requestUrl);
        $client->setMethod('GET');
        //$adapter = new \Zend\Http\Client\Adapter\Curl();
        //$this->getClient()->setAdapter($adapter);
        $this->setupClientAuth($client);
        try
        {
            $response = $client->request();
        }
        catch (\Exception $e)
        {
            $originErrorMessage = $e->getMessage();
            $finalMessage = __('Request to Solr Server [%1] Failed. Origin request message [%2]', 
                                    $client->getUri(), $originErrorMessage);
            throw new \Exception($finalMessage);
        }
        
        if ($response->isError()) {
            throw new \Exception(__('Solr server response error: [%1] - Query URL[%2]', $response->getMessage(), $requestUrl));
        }
        
        $responseText = $response->getBody();
        $result = json_decode($responseText, true);
        return $result;
    }
    
    /**
     * Request Solr Server by CURL
     * @param string $url
     * @param mixed $postFields
     * @param string $type
     * @return array
     */
    public function doRequest($url, $postFields = null, $type='array'){

        $sh = curl_init($url);
        curl_setopt($sh, CURLOPT_HEADER, 0);
        if(is_array($postFields)) {
            curl_setopt($sh, CURLOPT_POST, true);
            curl_setopt($sh, CURLOPT_POSTFIELDS, $postFields);
        }
        curl_setopt($sh, CURLOPT_RETURNTRANSFER, 1);

        if ($type == 'json') {
            curl_setopt( $sh, CURLOPT_HEADER, true );
        }

        $this->setupSolrAuthenticate($sh);

        if ($type == 'json') {
            list( $header, $contents ) = @preg_split( '/([\r\n][\r\n])\\1/', curl_exec($sh), 2 );
            $output = preg_split( '/[\r\n]+/', $contents );
        }else{
            $output = curl_exec($sh);
            $output = json_decode($output,true);
        }

        curl_close($sh);
        return $output;
    }
    
    public function putRequest($url, $data = array()) {
        $sh = curl_init($url);
        
        curl_setopt($sh, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($sh, CURLOPT_CUSTOMREQUEST, 'PUT');
        
        curl_setopt($sh, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
        curl_setopt($sh, CURLOPT_CONNECTTIMEOUT, 80);

        if(is_string($data)) {
            curl_setopt($sh, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($sh, CURLOPT_POSTFIELDS, json_encode($data, true));
        }

        $this->setupSolrAuthenticate($sh);
        
        $output = curl_exec($sh);
        $output = json_decode($output,true);
        curl_close($sh);
        return $output;
    }
    
    public function deleteRequest($url, $data = array()) {
        $sh = curl_init($url);
        curl_setopt($sh, CURLOPT_CUSTOMREQUEST, 'DELETE');
        
        curl_setopt($sh, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
        curl_setopt($sh, CURLOPT_CONNECTTIMEOUT, 80);
        
        if(is_string($data)) {
            curl_setopt($sh, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($sh, CURLOPT_POSTFIELDS, json_encode($data, true));
        }

        $this->setupSolrAuthenticate($sh);
        
        $output = curl_exec($sh);
        $output = json_decode($output,true);
        curl_close($sh);
        return $output;
    }

    /**
     * Retrieve available solr cores from Solr server.
     *
     * @return array
     */
    public function getAllAvailableSolrCores()
    {
        $requestUrl = $this->buildUrl('admin/cores');
        $result = $this->doGetRequest($requestUrl);
        return $result['status'];
    }
    
    static public function parseFilterQueryFromArray($filter = array())
    {
        $filterQuery = '';
        foreach( $filter as $key => $value)
        {
            //OR
            if( is_array($value) )
            {
                //$query .= $key.':%22'.urlencode(trim(addslashes($value))).'%22+OR+';
                if( count($value) > 0)
                {
                    $filterQueryOr = $key.':'.@implode(' OR ' . $key . ':', $value);
                    $filterQuery .= ' AND ('.$filterQueryOr.')';
                }
            }
            //AND
            else
            {
                //$query .= $key.':%22'.urlencode(trim(addslashes($value))).'%22+OR+';
                $filterQuery .= ' AND ('.$key.':' . $value . ')';
            }
        }
        return trim(trim(trim($filterQuery),'AND'));
    }
    
    public function getSynonyms($solrcore, $term = null) {
        if (!empty($solrcore)) {
            $path = '/schema/analysis/synonyms/'.$solrcore;
            if($term) {
                $path .= '/'.$term;
            }
            $requestUrl = $this->buildUrl($path, $solrcore);
            $response = $this->doRequest($requestUrl);
        
            $synonyms = array();
            if($term) {
                if(isset($response[$term])) {
                    $synonyms = $response[$term];
                }
                return $synonyms;
            }
        
            if(isset($response['synonymMappings']['managedMap'])) {
                $synonyms = $response['synonymMappings']['managedMap'];
            }
            return $synonyms;
        }
        return array();
    }
    
    public function getDocumentCount($filter = array())
    {
        if( $this->index !== null )
        {
            $solrcore = $this->index->getSolrCore();
            $storeId = $this->index->getStoreId();
            $doctype = $this->index->getDoctype();
            
            $queryString = $this->parseFilterQueryFromArray($filter);
            $params = array(
                'q' => '*:*',
                'rows' => 0
            );
            if(empty($queryString) )
            {
                $params['fq'] = $queryString;
            }
            $requestUrl = $this->buildUrl('select', $this->index->getSolrCore());
            $response = $this->doGetRequest($requestUrl, $params);
            if( is_array($response) && isset($response['response']['numFound']))
            {
                return $response['response']['numFound'];
            }
            return 0;
        }
    }

    public function getAllDocumentByType($type = '???')
    {

    }
}
