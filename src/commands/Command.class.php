<?php
/**
 * LLR Information Systems Development
 * part of LLR Services Group - www.llrweb.com/isd
 *
 * Mercury Application Platform
 * InfoScape
 *
 * User: lromero
 * Date: 12/23/2019
 * Time: 9:54 AM
 */


namespace commands;

use exceptions\MercuryException;

/**
 * MERCURY 3.0.0
 * This class will be implemented by extensions utilizing commands to perform business operations
 *
 * Interface Command
 * @package commands
 */
interface Command
{
    /**
     * Executes the instructions of the command
     * @return bool Was the command successful?
     */
    public function execute():bool;

    /**
     * @return mixed The output of a successful command, defined by the command
     */
    public function getResult();

    /**
     * @return MercuryException|null The exception thrown by execution
     */
    public function getError(): ?MercuryException;
}
