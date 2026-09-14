<?php

namespace App\Services\State\Processor\PageView;

use Symfony\Component\Validator\Constraints as Assert;

class PageViewLogDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 500)]
    public string $path;
}