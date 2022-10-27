<?php

namespace Ulib\Grabber\Entity;

class User extends BaseEntity
{
    private $firstname;

    private $lastname;

    private $department;

    private $room;

    /**
     * @ignore
     */
    private $phone;

    private $mail;

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getDepartment()
    {
        return $this->department;
    }

    public function setDepartment(string $department): self
    {
        $this->department = $department;
        return $this;
    }

    public function getRoom()
    {
        return $this->room;
    }

    public function setRoom(string $room): self
    {
        $this->room = $room;
        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;
        return $this;
    }

    public function getLastnameWithoutTitles()
    {
        return explode(',', $this->getLastname())[0];
    }

    public function getTitles()
    {
        $explode = explode(',', $this->getLastname());
        if (count($explode) > 1) {
            $titles = [];
            $count = 1;
            foreach ($explode as $value) {
                if ($count > 1) {
                    $titles[] = trim($value);
                }
                $count++;
            }
            return $titles;
        }
        return null;
    }

    public function getPhoneNumbers(): array
    {
        $phones = [];
        $explode = explode(',', $this->getPhone());
        if (count($explode) > 1) {
            foreach ($explode as $value) {
                $phones[] = (int)$value;
            }
            return $phones;
        }
        $phones[] = (int)$this->getPhone();
        return $phones;
    }

    public function getCleanName(): string
    {
        return $this->getFirstname() . ' ' . $this->getLastnameWithoutTitles();
    }
}
