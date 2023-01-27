<?php

namespace App\Serializer;

use App\Entity\Basket;
use App\Entity\Product;
use App\Repository\BasketRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class CreatedByDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED_DENORMALIZER = 'CreatedByDenormalizerCalled';
    /**
     * @var Security
     */
    private $security;
    /**
     * @var BasketRepository
     */
    private $basketRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(Security $security, BasketRepository $basketRepository, UserRepository $userRepository)
    {
        $this->security = $security;
        $this->basketRepository = $basketRepository;
        $this->userRepository = $userRepository;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        $alreadyCalled = $context[self::ALREADY_CALLED_DENORMALIZER] ?? false;
        return $type === Product::class && !$alreadyCalled;
    }

    public function denormalize($data, string $type, string $format = null, array $context = []): Product
    {
        $context[self::ALREADY_CALLED_DENORMALIZER] = true;
        /** @var Product $product */
        $product = $this->denormalizer->denormalize($data, $type, $format, $context);

        $user = $this->security->getUser();
        $basket = $this->basketRepository->findByUser($user);
        if (!$basket) {
            $dbUser = $this->userRepository->find($user->getId());
            $basket = new Basket();
            $basket->setCreatedAt(new \DateTimeImmutable());
            if ($dbUser) {
                $basket->setUser($dbUser);
            }
            $this->basketRepository->add($basket, true);
        }

        $product->setBasket($basket);

        return $product;
    }
}