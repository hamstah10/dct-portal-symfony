<?php

namespace App\Twig\Components;

use App\Entity\MediaAsset;
use App\Entity\MediaFolder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('app_media_slider', template: 'components/app_media_slider.html.twig')]
final class AppMediaSlider
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $folder = 'APP';

    #[LiveProp]
    public int $limit = 12;

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return array<int, MediaAsset>
     */
    public function getAssets(): array
    {
        $folderName = trim($this->folder);
        if ('' === $folderName) {
            return [];
        }

        $folder = $this->entityManager->getRepository(MediaFolder::class)
            ->createQueryBuilder('f')
            ->andWhere('LOWER(f.name) = :name')
            ->setParameter('name', mb_strtolower($folderName))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$folder instanceof MediaFolder) {
            return [];
        }

        return $this->entityManager->getRepository(MediaAsset::class)
            ->createQueryBuilder('a')
            ->andWhere('a.folder = :folder')
            ->andWhere('a.active = :active')
            ->andWhere('a.mediaType = :mediaType')
            ->setParameter('folder', $folder)
            ->setParameter('active', true)
            ->setParameter('mediaType', MediaAsset::TYPE_IMAGE)
            ->addOrderBy('a.sorting', 'ASC')
            ->addOrderBy('a.createdAt', 'DESC')
            ->setMaxResults(max(1, $this->limit))
            ->getQuery()
            ->getResult();
    }
}
