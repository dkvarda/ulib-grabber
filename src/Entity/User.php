<?php

declare(strict_types=1);

namespace Ulib\Grabber\Entity;

class User extends BaseEntity implements IEntity
{
    private ?string $firstname = null;

    private ?string $lastname = null;

    private ?string $department = null;

    private ?string $room = null;

    /**
     * @ignore
     */
    private ?string $phone = null;

    private ?string $mail = null;

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(string $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getRoom(): ?string
    {
        return $this->room;
    }

    public function setRoom(string $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getLastnameWithoutTitles(): ?string
    {
        if ($this->lastname === null || $this->lastname === '') {
            return null;
        }

        return $this->splitCsvValue($this->lastname)[0] ?? null;
    }

    public function getTitles(): ?array
    {
        if ($this->lastname === null || $this->lastname === '') {
            return null;
        }

        $explode = $this->splitCsvValue($this->lastname);
        if (count($explode) <= 1) {
            return null;
        }

        $titles = array_values(array_filter(array_slice($explode, 1), static fn (string $value): bool => $value !== ''));

        return $titles !== [] ? $titles : null;
    }

    public function getPhoneNumbers(): array
    {
        if ($this->phone === null || trim($this->phone) === '') {
            return [];
        }

        $phones = [];
        foreach ($this->splitCsvValue($this->phone) as $value) {
            $normalized = preg_replace('/\D+/', '', $value) ?? '';
            if ($normalized !== '') {
                $phones[] = (int) $normalized;
            }
        }

        return $phones;
    }

    public function getCleanName(): string
    {
        return trim(sprintf('%s %s', $this->firstname ?? '', $this->getLastnameWithoutTitles() ?? ''));
    }

    /**
     * @return string[]
     */
    private function splitCsvValue(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value)), static fn (string $item): bool => $item !== ''));
    }
}
