<?php

namespace Bonsi\GetResponse\Newsletter;

//use GetResponse\GetResponse;
//use DrewM\MailChimp\MailChimp;

class Newsletter
{
    /** @var GetResponse */
    protected $getResponse;

    /** * @var \Bonsi\GetResponse\Newsletter\NewsletterListCollection */
    protected $lists;

    /**
     * @param \GetResponse                 $getResponse
     * @param \Bonsi\GetResponse\Newsletter\NewsletterListCollection $lists
     */
    public function __construct(\GetResponse $getResponse, NewsletterListCollection $lists)
    {
        $this->getResponse = $getResponse;

        $this->lists = $lists;
    }

    /**
     * @param string $email
     * @param array  $mergeFields
     * @param string $listName
     * @param array  $options
     *
     * @return array|bool
     *
     * @throws \Bonsi\GetResponse\Newsletter\Exceptions\InvalidNewsletterList
     */
    public function subscribe($email, $mergeFields = [], $listName = '', $options = [])
    {
        $list = $this->lists->findByName($listName);

        $defaultOptions = [
            'email' => $email,
            'campaign' => ['campaignId' => $list->getId()]
        ];

        $allOptions = array_merge($defaultOptions, $mergeFields);
//dd($allOptions);
        $curlResponse = $this->getResponse->addContact($allOptions);

        $httpStatus = $this->getResponse->http_status;

//        if( 202 != $httpStatus )
//        {
//            $this->lastError = $curlResponse;
//            throw new \Exception("GetResponseAPI3 returned code {$httpStatus}, error: ".print_r($curlResponse,true));
//        }

// dd(['$httpStatus' => $httpStatus]);
        // empty stdClass is OK
        return $curlResponse;
//        return true;

    }

    /**
     * @param string $email
     * @param string $listName
     *
     * @return array|bool
     *
     * @throws \Bonsi\GetResponse\Newsletter\Exceptions\InvalidNewsletterList
     */
    public function getMember($email, $listName = '')
    {
        $list = $this->lists->findByName($listName);

//        if (!$this->lastActionSucceeded()) {
//            return false;
//        }
        $options = [
            'query' => [
                'email' => $email,
                'campaign' => ['campaignId' => $list->getId()]
            ],
            'fields' => 'name',
        ];
        dd([
            'response' => $this->getResponse->searchContacts($options)
            ]);

//        return $this->getResponse->get("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}");
    }

    /**
     * @param string $email
     * @param string $listName
     *
     * @return bool
     */
    public function hasMember($email, $listName = '')
    {
        $response = $this->getMember($email, $listName);
        
        if (! isset($response['email_address'])) {
            return false;
        }

        if (strtolower($response['email_address']) != strtolower($email)) {
            return false;
        }

        return true;
    }

    /**
     * @param $email
     * @param string $listName
     *
     * @return array|false
     *
     * @throws \Bonsi\GetResponse\Newsletter\Exceptions\InvalidNewsletterList
     */
    public function unsubscribe($email, $listName = '')
    {
        $list = $this->lists->findByName($listName);

        $response = $this->getResponse->delete("lists/{$list->getId()}/members/{$this->getSubscriberHash($email)}");

        return $response;
    }

    /**
     * @param string $fromName
     * @param string $replyTo
     * @param string $subject
     * @param string $html
     * @param string $listName
     * @param array  $options
     * @param array  $contentOptions
     *
     * @return array|bool
     *
     * @throws \Bonsi\GetResponse\Newsletter\Exceptions\InvalidNewsletterList
     */
    public function createCampaign($fromName, $replyTo, $subject, $html = '', $listName = '', $options = [], $contentOptions = [])
    {
        $list = $this->lists->findByName($listName);

        $defaultOptions = [
            'type' => 'regular',
            'recipients' => [
                'list_id' => $list->getId(),
            ],
            'settings' => [
                'subject_line' => $subject,
                'from_name' => $fromName,
                'reply_to' => $replyTo,
            ],
        ];

        $options = array_merge($defaultOptions, $options);

        $response = $this->getResponse->post('campaigns', $options);

        if (!$this->lastActionSucceeded()) {
            return false;
        }

        if ($html === '') {
            return $response;
        }

        if (!$this->updateContent($response['id'], $html, $contentOptions)) {
            return false;
        }

        return $response;
    }

    public function updateContent($campaignId, $html, $options = [])
    {
        $defaultOptions = compact('html');

        $options = array_merge($defaultOptions, $options);

        $response = $this->getResponse->put("campaigns/{$campaignId}/content", $options);

        if (!$this->lastActionSucceeded()) {
            return false;
        }

        return $response;
    }

    /**
     * @return \GetResponse
     */
    public function getApi()
    {
        return $this->getResponse;
    }

    /**
     * @return array|false
     */
    public function getLastError()
    {
        return $this->getResponse->getLastError();
    }

    /**
     * @return bool
     */
    public function lastActionSucceeded()
    {
        return !$this->getResponse->getLastError();
    }

    /**
     * @param string $email
     *
     * @return string
     */
    protected function getSubscriberHash($email)
    {
        return $this->getResponse->subscriberHash($email);
    }
}
