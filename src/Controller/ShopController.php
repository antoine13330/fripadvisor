<?php

namespace App\Controller;

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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

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
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $limit = $limit > 20 ? 20 : $limit;

        $idCache = 'getAllShops';
        $jsonShop = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer) {
            echo "MISE EN CACHE";
            $item->tag('ShopCache');

            $shop = $repository->findAll();
            return $serializer->serialize($shop, 'json', ['groups' => 'getAllShops']);

        } ); 
        return new JsonResponse($jsonShop, 200, [], true);

        // return $this->json($repository->findShops($page, $limit), 200, [], ['groups' => 'getAllShops']);
    }

    #[Route('/api/shop/{idShop}', name: 'shops.getShop', methods: ['GET'])]
    #[ParamConverter("shop", options: ["id" => "idShop"], class: 'App\Entity\Shop')]
    public function getShop(
        Shop $shop,
        Request $request,
        SerializerInterface $serializer
    ) :JsonResponse
    {
        return $this->json($shop, 200, [], ['groups' => 'getShop']);
    }

    #[Route('/api/shop/{idShop}', name: 'shops.deleteShop', methods: ['DELETE'])]
    #[ParamConverter("shop", options: ["id" => "idShop"], class: 'App\Entity\Shop')]
    public function deleteShop(
        Shop $shop,
        EntityManagerInterface $entityManager
    ) :JsonResponse
    {
        $entityManager->remove($shop);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/shop', name: '$shop.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas admin')]
    public function createShop(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
    ) :JsonResponse
    {
        $shop = $serializer->deserialize($request->getContent(), Shop::class, 'json');
        $shop->setStatus(true);

        //$content = $request->toArray();
        //$idCategorie = $content["idCategorie"];

        $erors = $validator->validate($shop);
        if ($erors->count() >0) {
            return new JsonResponse($serializer->serialize($erors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($shop);
        $entityManager->flush();

        $location = $urlGenerator->generate("shops.getShop", ['idShop' => $shop->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        $jsonShop = $serializer->serialize($shop, 'json', ['groups' => 'getShop']);
        return new JsonResponse($jsonShop, Response::HTTP_CREATED, ["Location" => $location], true);
    }

     // update route
     #[Route('/api/shop/{id}', name: 'Shop.update', methods: ['PUT'])]
     public function updateShop(
         Shop $Shop,
         Request $request,
         EntityManagerInterface $entityManager,
         SerializerInterface $serializer,
         ShopRepository $shopRepository,
         UrlGeneratorInterface $urlGenerator
     ): JsonResponse {
         $Shop = $serializer->deserialize(
             $request->getContent(),
             Shop::class,
             'json',
             [AbstractNormalizer::OBJECT_TO_POPULATE => $Shop]
         );
         $Shop->setSatus("1");
 
         $content = $request->toArray();
         $id = $content['idShop'];
 
         $entityManager->persist($Shop);
         $entityManager->flush();
 
         $location = $urlGenerator->generate("shops.getShop", ['idShop' => $Shop->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
 
         $jsonBoutique = $serializer->serialize($Shop, 'json', ['groups' => 'getAllShops']);
         return new JsonResponse($jsonBoutique, JsonResponse::HTTP_CREATED, ['$location' => ''], true);
     }

    // #[Route('/api/shop/{id}', name: 'shops.updateShop', methods: ['PATCH'])]
    // #[ParamConverter("shop", options: ["id" => "idShop"], class: 'App\Entity\Shop')]
    // public function updateShop(
    //     Shop $shop,
    //     Request $request,
    //     EntityManagerInterface $entityManager,
    //     SerializerInterface $serializer,
    //     UrlGeneratorInterface $urlGenerator,
    //     ValidatorInterface $validator,
    // ) :JsonResponse
    // {
    //     $shop = $serializer->deserialize($request->getContent(), Shop::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $shop]);
    //     $shop->setStatus(true);

    //     $content = $request->toArray();
    //     //$idBoutique = $content['idBoutique'];
    //     //$shop->addBoutiqueCategorie($categorieRepository->find($idBoutique));

    //     $erors = $validator->validate($shop);
    //     if ($erors->count() >0) {
    //         return new JsonResponse($serializer->serialize($erors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
    //     }

    //     $entityManager->persist($shop);
    //     $entityManager->flush();

    //     $location = $urlGenerator->generate("shops.getBoutique", ['idShop' => $shop->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
    //     $jsonShop = $serializer->serialize($shop, "json", ['groups' => 'getShop']);
    //     return new JsonResponse($jsonShop, Response::HTTP_CREATED, ["Location" => $location], true);
    // }
}
