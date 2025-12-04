<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Core\Image\Application\Port\ImageRepositoryInterface;
use App\Core\Image\Domain\Image;
use App\Core\Image\Domain\ImageId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Image>
 */
final class ImageRepository extends ServiceEntityRepository implements ImageRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Image::class);
    }

    public function save(Image $image): void
    {
        $em = $this->getEntityManager();
        $em->persist($image);
        $em->flush();
    }

    public function findById(ImageId $id): ?Image
    {
        return $this->find($id);
    }
}
