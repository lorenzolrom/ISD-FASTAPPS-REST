<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 9/12/2019
 * Time: 5:40 PM
 */


namespace extensions\tickets\models;


use models\Model;
use utilities\Validator;

class Search extends Model
{
    public const NAME_RULES = array(
        'name' => 'Name',
        'lower' => 1,
        'alnumds' => TRUE
    );

    private $id;
    private $workspace;
    private $user;
    private $name;
    private $number;
    private $title;
    private $contact;
    private $assignees;
    private $severity;
    private $type;
    private $category;
    private $status;
    private $closureCode;
    private $desiredDateStart;
    private $desiredDateEnd;
    private $scheduledDateStart;
    private $scheduledDateEnd;
    private $description;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getWorkspace(): int
    {
        return $this->workspace;
    }

    /**
     * @return int
     */
    public function getUser(): int
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function getContact(): ?string
    {
        return $this->contact;
    }

    /**
     * @return string|null
     */
    public function getAssignees(): ?string
    {
        return $this->assignees;
    }

    /**
     * @return string|null
     */
    public function getSeverity(): ?string
    {
        return $this->severity;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getClosureCode(): ?string
    {
        return $this->closureCode;
    }

    /**
     * @return string|null
     */
    public function getDesiredDateStart(): ?string
    {
        return $this->desiredDateStart;
    }

    /**
     * @return string|null
     */
    public function getDesiredDateEnd(): ?string
    {
        return $this->desiredDateEnd;
    }

    /**
     * @return string|null
     */
    public function getScheduledDateStart(): ?string
    {
        return $this->scheduledDateStart;
    }

    /**
     * @return string|null
     */
    public function getScheduledDateEnd(): ?string
    {
        return $this->scheduledDateEnd;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $name
     * @return bool
     * @throws \exceptions\DatabaseException
     * @throws \exceptions\ValidationException
     */
    public static function validateName(?string $name): bool
    {
        return Validator::validate(self::NAME_RULES, $name);
    }
}
