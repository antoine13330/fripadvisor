<?php

namespace App\Controller;

use phpDocumentor\Reflection\Types\Context;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Shop;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Serializer;

class ShopController extends AbstractController
{
    #[Route('/shop', name: 'app_shop')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ShopController.php',
        ]);
    }

    #[Route('/api/shops', name: 'shops.getAll', methods: ['GET'])]
    public function getAllShops(
        ShopRepository $repository,
        Request $request,
        TagAwareCacheInterface $cache,
        SerializerInterface $serializer
    ) :JsonResponse
    {
        $idCache = 'getAllShops';
        $jsonShop = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $request) {
            $item->tag('ShopCache');
            $context = SerializationContext::create()->setGroups(["getAllShops"]);

            $page = $request->get('page', 1);
            $limit = $request->get('limit', 5);
            $limit = min($limit, 20);

            $shop = $repository->findShops($page, $limit);
            return $serializer->serialize($shop, 'json', $context);
        } );
        return new JsonResponse($jsonShop, 200, [], true);
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    #[Route('/api/shop/{idShop}', name: 'shops.getShop', methods: ['GET'])]
    #[ParamConverter("shop", options: ["id" => "idShop"], class: 'App\Entity\Shop')]
    public function getShop(
        Shop $shop,
        ShopRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $idCache = 'getShop';
        $jsonShop = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer, $request, $shop) {
            $item->tag("getShop");
            $context = SerializationContext::create()->setGroups('getShop');

            $shops = $repository->find($shop);
            return $serializer->serialize($shops, 'json', $context);
        });

        return new JsonResponse($jsonShop, Response::HTTP_OK, [], true);
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     */
    #[Route('/api/shop/{idShop}', name: 'shops.deleteShop', methods: ['DELETE'])]
    #[ParamConverter("shop", options: ["id" => "idShop"], class: 'App\Entity\Shop')]
    public function deleteShop(
        Shop $shop,
        EntityManagerInterface $entityManager,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $cache->invalidateTags(["getShop"]);
        $shop->setSatus("0");
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_OK);
    }

    #[Route('/api/shop', name: 'shop.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas admin')]
    public function createShop(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
    ) :JsonResponse
    {
       $shop = $serializer->deserialize(
            $request->getContent(),
            Shop::class,
            'json');
        $shop->setName($shop->getName());
        $shop->setPoastalCode("1");
        $shop->setSatus("1");

        $erors = $validator->validate($shop);
        if ($erors->count() >0) {
            return new JsonResponse($serializer->serialize($erors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($shop);
        $entityManager->flush();

        $context = SerializationContext::create()->setGroups(["getShop"]);

        $location = $urlGenerator->generate("shops.getShop", ['idShop' => $shop->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        $jsonShop = $serializer->serialize($shop, 'json', $context);
        return new JsonResponse($jsonShop, Response::HTTP_CREATED, ["Location" => $location], true);
    }

     // update route
     #[Route('/api/shop/{idShop}', name: 'Shop.update', methods: ['PUT'])]
     #[ParamConverter("shop", class: 'App\Entity\Shop', options: ["id" => "idShop"])]
     public function updateShop(
         Shop $shop,
         Request $request,
         EntityManagerInterface $entityManager,
         SerializerInterface $serializer,
         UrlGeneratorInterface $urlGenerator
     ): JsonResponse {
         $updateShop = $serializer->deserialize(
             $request->getContent(),
             Shop::class,
             'json');
         $shop->setName($updateShop->getName() ? $updateShop->getName() : $shop->getName());
         $shop->setPoastalCode($updateShop->getPoastalCode() ? $updateShop->getPoastalCode() : $shop->getPoastalCode());
         $shop->setSatus("1");

         $entityManager->persist($shop);
         $entityManager->flush();

         $location = $urlGenerator->generate("shops.getShop", ['idShop' => $shop->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
         $context = SerializationContext::create()->setGroups(["getAllShops"]);

         $jsonBoutique = $serializer->serialize($shop, 'json', $context);
         return new JsonResponse($jsonBoutique, Response::HTTP_CREATED, [$location => ''], true);
     }
}
