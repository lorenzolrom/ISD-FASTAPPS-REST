<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/07/2019
 * Time: 1:57 PM
 */


namespace exceptions;


class LDAPException extends MercuryException
{
    const FAILED_SET_LDAP_VERSION = 1200;
    const FAILED_DISABLE_REFERRALS = 1201;
    const FAILED_START_TLS = 1202;

    const MESSAGES = array(
        self::FAILED_SET_LDAP_VERSION => "Failed to set LDAP protocol version",
        self::FAILED_DISABLE_REFERRALS => "Failed to disable LDAP referrals",
        self::FAILED_START_TLS => "Failed to start TLS LDAP connection"
    );
}