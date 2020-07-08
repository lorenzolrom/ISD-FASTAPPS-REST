<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 5/05/2019
 * Time: 3:12 PM
 */


namespace controllers;


use business\TokenOperator;
use business\UserOperator;
use models\HTTPRequest;
use models\HTTPResponse;

class TokenController extends Controller
{
    private const FIELDS = array('username', 'ipAddress', 'startDate', 'endDate');

    /**
     * @return HTTPResponse|null
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\SecurityException
     */
    public function getResponse(): ?HTTPResponse
    {
        CurrentUserController::validatePermission('settings');

        if($this->request->method() === HTTPRequest::POST AND $this->request->next() == 'search')
            return $this->search();

        return NULL;
    }

    /**
     * @return HTTPResponse
     * @throws \exceptions\DatabaseException
     */
    private function search(): HTTPResponse
    {
        $args = self::getFormattedBody(self::FIELDS);

        $tokens = TokenOperator::search("%{$args['username']}%", "%{$args['ipAddress']}%", $args['startDate'], $args['endDate']);

        $data = array();

        foreach($tokens as $token)
        {
            $data[] = array(
                'user' => UserOperator::usernameFromId($token->getUser()),
                'ipAddress' => $token->getIpAddress(),
                'issueTime' => $token->getIssueTime(),
                'expireTime' => $token->getExpireTime(),
                'expired' => $token->getExpired()
            );
        }

        return new HTTPResponse(HTTPResponse::OK, $data);
    }
}
