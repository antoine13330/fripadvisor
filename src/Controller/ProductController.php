<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Repository\ProductRepository;
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

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ProductController.php',
        ]);
    }

    #[Route('/api/products', name: 'products.getAll', methods: ['GET'])]
    public function getAllProducts(
        ProductRepository $repository,
        Request $request,
        SerializerInterface $serializer,
        TagAwareCacheInterface $cache
    ) :JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $limit = $limit > 20 ? 20 : $limit;

        $idCache = 'getAllProducts';
        $jsonProduct = $cache->get($idCache, function (ItemInterface $item) use ($repository, $serializer) {
            echo "MISE EN CACHE";
            $item->tag('ProductCache');

            $product = $repository->findAll();
            return $serializer->serialize($product, 'json', ['groups' => 'getAllProducts']);

        } ); 
        return new JsonResponse($jsonProduct, 200, [], true);

        // return $this->json($repository->findProducts($page, $limit), 200, [], ['groups' => 'getAllProducts']);
    }

    #[Route('/api/product/{idProduct}', name: 'products.getProduct', methods: ['GET'])]
    #[ParamConverter("product", options: ["id" => "idProduct"], class: 'App\Entity\Product')]
    public function getProduct(
        Product $product,
    ) :JsonResponse
    {
        return $this->json($product, 200, [], ['groups' => 'getProduct']);
    }

    #[Route('/api/product/{idProduct}', name: 'products.deleteProduct', methods: ['DELETE'])]
    #[ParamConverter("Product", options: ["id" => "idProduct"], class: 'App\Entity\Product')]
    public function deleteProduct(
        Product $product,
        EntityManagerInterface $entityManager
    ) :JsonResponse
    {
        $entityManager->remove($product);
        $entityManager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/product', name: '$product.create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'êtes pas admin')]
    public function createProduct(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
    ) :JsonResponse
    {
        $product = $serializer->deserialize($request->getContent(), product::class, 'json');
        $product->setStatus(true);

        //$content = $request->toArray();
        //$idCategorie = $content["idCategorie"];

        $erors = $validator->validate($product);
        if ($erors->count() >0) {
            return new JsonResponse($serializer->serialize($erors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($product);
        $entityManager->flush();

        $location = $urlGenerator->generate("products.getProduct", ['idProduct' => $product->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
        $jsonProduct = $serializer->serialize($product, 'json', ['groups' => 'getProduct']);
        return new JsonResponse($jsonProduct, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    // update route
    #[Route('/api/product/{id}', name: 'Product.update', methods: ['PUT'])]
    public function updateProduct(
        Product $Product,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ProductRepository $productRepository,
        UrlGeneratorInterface $urlGenerator
    ): JsonResponse {
        $Product = $serializer->deserialize(
            $request->getContent(),
            Product::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $Product]
        );
        $Product->setStatus("1");

        $content = $request->toArray();
        $id = $content['idProduct'];

        $entityManager->persist($Product);
        $entityManager->flush();

        $location = $urlGenerator->generate("products.getProduct", ['idProduct' => $Product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $jsonBoutique = $serializer->serialize($Product, 'json', ['groups' => 'getAllProducts']);
        return new JsonResponse($jsonBoutique, JsonResponse::HTTP_CREATED, ['$location' => ''], true);
    }

    // #[Route('/api/product/{id}', name: 'products.updateProduct', methods: ['PATCH'])]
    // #[ParamConverter("Product", options: ["id" => "idProduct"], class: 'App\Entity\Product')]
    // public function updateProduct(
    //     Product $product,
    //     Request $request,
    //     EntityManagerInterface $entityManager,
    //     SerializerInterface $serializer,
    //     UrlGeneratorInterface $urlGenerator,
    //     ValidatorInterface $validator,
    // ) :JsonResponse
    // {
    //     $product = $serializer->deserialize($request->getContent(), product::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $product]);
    //     $product->setStatus(true);

    //     $content = $request->toArray();
    //     //$idBoutique = $content['idBoutique'];
    //     //$product->addBoutiqueCategorie($categorieRepository->find($idBoutique));

    //     $erors = $validator->validate($product);
    //     if ($erors->count() >0) {
    //         return new JsonResponse($serializer->serialize($erors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
    //     }

    //     $entityManager->persist($product);
    //     $entityManager->flush();

    //     $location = $urlGenerator->generate("products.getProduct", ['idProduct' => $product->getId(), UrlGeneratorInterface::ABSOLUTE_URL]);
    //     $jsonProduct = $serializer->serialize($product, "json", ['groups' => 'getProduct']);
    //     return new JsonResponse($jsonProduct, Response::HTTP_CREATED, ["Location" => $location], true);
    // }
}
