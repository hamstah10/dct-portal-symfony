<?php

namespace App\Entity;

interface ManualUidEntityInterface
{
    public function getUid(): int;

    public function setUid(int $uid): static;

    public function getCrdate(): int;

    public function setCrdate(int $crdate): static;

    public function setTstamp(int $tstamp): static;
}
